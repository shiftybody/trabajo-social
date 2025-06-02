<?php

/**
 * Clase Router
 * 
 * Maneja el registro de rutas y su despacho
 * a los controladores correspondientes
 */

namespace App\Core;

class Router
{
  /**
   * Rutas registradas agrupadas por método HTTP
   * @var array
   */
  private $routes = array(
    'GET' => array(),
    'POST' => array(),
    'PUT' => array(),
    'DELETE' => array()
  );

  /**
   * Prefijo actual para las rutas
   * @var string
   */
  private $prefix = '';

  /**
   * Middlewares actuales para las rutas
   * @var array
   */
  private $middlewares = array();

  /**
   * Nombre del controlador por defecto
   * @var string
   */
  private $defaultController = 'HomeController';

  /**
   * Método del controlador por defecto
   * @var string
   */
  private $defaultMethod = 'index';


  /**
   * Registra una ruta GET
   * 
   * @param string $uri URI de la ruta
   * @param mixed $controller Controlador a invocar
   * @return Router Instancia actual para encadenamiento
   */
  public function get($uri, $controller)
  {
    $this->addRoute('GET', $uri, $controller);
    return $this;
  }

  /**
   * Registra una ruta POST
   * 
   * @param string $uri URI de la ruta
   * @param mixed $controller Controlador a invocar
   * @return Router Instancia actual para encadenamiento
   */
  public function post($uri, $controller)
  {
    $this->addRoute('POST', $uri, $controller);
    return $this;
  }

  /**
   * Registra una ruta PUT
   * 
   * @param string $uri URI de la ruta
   * @param mixed $controller Controlador a invocar
   * @return Router Instancia actual para encadenamiento
   */
  public function put($uri, $controller)
  {
    $this->addRoute('PUT', $uri, $controller);
    return $this;
  }

  /**
   * Registra una ruta DELETE
   * 
   * @param string $uri URI de la ruta
   * @param mixed $controller Controlador a invocar
   * @return Router Instancia actual para encadenamiento
   */
  public function delete($uri, $controller)
  {
    $this->addRoute('DELETE', $uri, $controller);
    return $this;
  }

  /**
   * Registra rutas para los métodos HTTP especificados
   * 
   * @param array $methods Métodos HTTP
   * @param string $uri URI de la ruta
   * @param mixed $controller Controlador a invocar
   * @return Router Instancia actual para encadenamiento
   */
  public function match(array $methods, $uri, $controller)
  {
    foreach ($methods as $method) {
      $method = strtoupper($method);

      if (isset($this->routes[$method])) {
        $this->addRoute($method, $uri, $controller);
      }
    }

    return $this;
  }

  /**
   * Agrega una ruta a la colección
   * 
   * @param string $method Método HTTP
   * @param string $uri URI de la ruta
   * @param mixed $controller Controlador a invocar
   * @return void
   */
  private function addRoute($method, $uri, $controller)
  {
    // Aplicar prefijo si existe
    $uri = $this->prefix . '/' . trim($uri, '/');
    $uri = '/' . trim($uri, '/');

    // Preservar la ruta original para generar URLs
    $originalUri = $uri;

    // Convertir parámetros nominales a patrón de expresión regular
    // Ejemplo: /users/:id/posts/:postId -> /users/([^/]+)/posts/([^/]+)
    $pattern = $uri;

    // Lista para asociar nombres de parámetros con su posición
    $paramNames = array();

    if (strpos($uri, ':') !== false) {
      $pattern = preg_replace_callback('/\/:([^\/]+)/', function ($matches) use (&$paramNames) {
        $paramNames[] = $matches[1];
        return '/([^/]+)';
      }, $uri);
    }

    $this->routes[$method][$pattern] = array(
      'uri' => $originalUri,
      'controller' => $controller,
      'middlewares' => $this->middlewares,
      'paramNames' => $paramNames
    );
  }

  /**
   * Define un grupo de rutas con prefijo y/o middlewares
   * 
   * @param array $attributes Atributos del grupo (prefix, middleware)
   * @param callable $callback Función que define las rutas del grupo
   * @return Router Instancia actual para encadenamiento
   */
  public function group(array $attributes, $callback)
  {
    // Guardar configuración actual
    $previousPrefix = $this->prefix;
    $previousMiddlewares = $this->middlewares;

    // Aplicar nuevo prefijo
    if (isset($attributes['prefix'])) {
      $this->prefix = $previousPrefix . '/' . trim($attributes['prefix'], '/');
    }

    // Aplicar nuevos middlewares
    if (isset($attributes['middleware'])) {
      $middleware = $attributes['middleware'];
      if (!is_array($middleware)) {
        $middleware = array($middleware);
      }
      $this->middlewares = array_merge($this->middlewares, $middleware);
    }

    // Ejecutar callback que registrará las rutas
    call_user_func($callback, $this);

    // Restaurar configuración anterior
    $this->prefix = $previousPrefix;
    $this->middlewares = $previousMiddlewares;

    return $this;
  }


  /**
   * Despacha la ruta según la URI y el método HTTP
   * 
   * @param Request $request Petición a despachar
   * @return mixed Respuesta del controlador
   * @throws \Exception Si no se encuentra la ruta
   */
  public function dispatch($request)
  {
    $method = $request->getMethod();
    $uri = $request->getUri();

    // Verificar si el método es soportado
    if (!isset($this->routes[$method])) {
      throw new \Exception("Método HTTP no soportado: $method", 405);
    }

    // Buscar coincidencia exacta
    if (isset($this->routes[$method][$uri])) {
      return $this->executeRoute($this->routes[$method][$uri], $request);
    }

    // Buscar rutas con parámetros
    foreach ($this->routes[$method] as $pattern => $route) {
      if (strpos($pattern, '(') !== false) {
        $regex = '#^' . $pattern . '$#';

        if (preg_match($regex, $uri, $matches)) {
          // Eliminar la coincidencia completa
          array_shift($matches);

          // Asociar nombres de parámetros si están definidos
          if (!empty($route['paramNames'])) {
            $params = array();
            foreach ($matches as $index => $value) {
              if (isset($route['paramNames'][$index])) {
                $params[$route['paramNames'][$index]] = $value;
              } else {
                $params[] = $value;
              }
            }
            $request->setParams($params);
          } else {
            $request->setParams($matches);
          }

          return $this->executeRoute($route, $request);
        }
      }
    }

    // No se encontró la ruta
    throw new \Exception("Ruta no encontrada: $method $uri", 404);
  }

  /**
   * Ejecuta una ruta aplicando middlewares
   * 
   * @param array $route Información de la ruta
   * @param Request $request Petición a procesar
   * @return mixed Respuesta del controlador
   */
  private function executeRoute($route, $request)
  {
    // Aplicar middlewares
    $middlewares = $route['middlewares'];
    $next = function ($request) use ($route) {
      return $this->callAction($route['controller'], $request);
    };

    // Construir cadena de middlewares
    $middlewareStack = array_reduce(
      array_reverse($middlewares),
      function ($next, $middleware) {
        return function ($request) use ($next, $middleware) {
          // Obtener parámetros del middleware si los hay
          $parts = explode(':', $middleware, 2);
          $middlewareName = $parts[0];
          $middlewareParams = isset($parts[1]) ? $parts[1] : null;

          // Crear la clase del middleware
          $middlewareClass = "\\App\\Middlewares\\{$middlewareName}Middleware";
          $middlewareInstance = $middlewareParams ?
            new $middlewareClass($middlewareParams) :
            new $middlewareClass();

          return $middlewareInstance->handle($request, $next);
        };
      },
      $next
    );

    return $middlewareStack($request);
  }

  /**
   * Invoca el controlador y acción
   * 
   * @param mixed $controller Controlador a invocar
   * @param Request $request Petición a procesar
   * @return mixed Respuesta del controlador
   * @throws \Exception Si el formato del controlador es inválido
   */
  private function callAction($controller, $request)
  {
    // Si el controlador es una función anónima
    if ($controller instanceof \Closure) {
      return call_user_func($controller, $request);
    }

    // Si es string con formato Controller@method
    if (is_string($controller) && strpos($controller, '@') !== false) {
      list($controller, $action) = explode('@', $controller);
      $controller = "\\App\\Controllers\\{$controller}";

      if (!class_exists($controller)) {
        throw new \Exception("Controlador no encontrado: $controller", 500);
      }

      $controllerInstance = new $controller();

      if (!method_exists($controllerInstance, $action)) {
        throw new \Exception("Método no encontrado: $action en $controller", 500);
      }

      return call_user_func(array($controllerInstance, $action), $request);
    }

    throw new \Exception("Formato de controlador inválido", 500);
  }

  /**
   * Genera una URL a partir de una ruta nombrada
   * 
   * @param string $name Nombre de la ruta
   * @param array $params Parámetros para la URL
   * @return string URL generada
   * @throws \Exception Si la ruta no existe
   */
  public function url($name, $params = array())
  {
    // Buscar la ruta por nombre
    foreach ($this->routes as $method => $routes) {
      foreach ($routes as $route) {
        if (isset($route['name']) && $route['name'] === $name) {
          $uri = $route['uri'];

          // Reemplazar parámetros
          foreach ($params as $key => $value) {
            $uri = str_replace(':' . $key, $value, $uri);
          }

          return APP_URL . ltrim($uri, '/');
        }
      }
    }

    throw new \Exception("Ruta nombrada no encontrada: $name", 500);
  }

  /**
   * Nombra una ruta para generar URLs
   * 
   * @param string $name Nombre de la ruta
   * @return Router Instancia actual para encadenamiento
   */
  public function name($name)
  {
    // Obtener la última ruta agregada
    $lastMethod = end(array_keys($this->routes));
    $lastUri = end(array_keys($this->routes[$lastMethod]));

    if ($lastUri) {
      $this->routes[$lastMethod][$lastUri]['name'] = $name;
    }
    return $this;
  }
}

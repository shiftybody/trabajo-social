<?php
// Incluir encabezado común y barra de navegación
require_once __DIR__ . '/../../public/inc/head.php';
require_once __DIR__ . '/../../public/inc/navbar.php';

// Obtener mensaje flash si existe
$flash = $this->sessionService->getFlash();
?>

<div class="dashboard-container">
  <div class="dashboard-sidebar">
    <div class="sidebar-header">
      <div class="user-info">
        <div class="user-avatar">
          <img src="/public/icons/user.svg" alt="User">
        </div>
        <div class="user-details">
          <h4><?php echo $userData['nombre'] . ' ' . $userData['apellido_paterno']; ?></h4>
          <p><?php echo $userData['rol_descripcion']; ?></p>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <ul>
        <li class="active">
          <a href="/dashboard">
            <img src="/public/icons/home.svg" alt="Dashboard">
            <span>Dashboard</span>
          </a>
        </li>

        <?php if ($userData['rol'] == 1): // Asumiendo que rol_id 1 es administrador 
        ?>
          <li>
            <a href="/users">
              <img src="/public/icons/users.svg" alt="Usuarios">
              <span>Gestión de Usuarios</span>
            </a>
          </li>
        <?php endif; ?>

        <li>
          <a href="/logout">
            <img src="/public/icons/logout.svg" alt="Logout">
            <span>Cerrar Sesión</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <div class="dashboard-content">
    <?php if ($flash): ?>
      <div class="alert alert-<?php echo $flash['type']; ?>">
        <?php echo $flash['message']; ?>
      </div>
    <?php endif; ?>

    <div class="content-header">
      <h1>Bienvenido al Sistema de Trabajo Social</h1>
      <p>Hoy es <?php echo date('d/m/Y'); ?></p>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-body">
          <h2 class="card-title">Panel de Control</h2>
          <p class="card-text">Este es el panel principal de la aplicación. Desde aquí podrás acceder a todas las funcionalidades según tu rol de usuario.</p>
        </div>
      </div>

      <!-- Aquí puedes agregar más tarjetas con información relevante según el rol del usuario -->
    </div>
  </div>
</div>

<style>
  .dashboard-container {
    display: flex;
    min-height: 100vh;
  }

  .dashboard-sidebar {
    width: 250px;
    background-color: #2c3e50;
    color: white;
    flex-shrink: 0;
  }

  .sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .user-info {
    display: flex;
    align-items: center;
  }

  .user-avatar {
    width: 40px;
    height: 40px;
    background-color: #fff;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 10px;
  }

  .user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .user-details h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
  }

  .user-details p {
    margin: 4px 0 0;
    font-size: 12px;
    opacity: 0.7;
  }

  .sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .sidebar-nav li {
    margin: 0;
  }

  .sidebar-nav li a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.2s;
  }

  .sidebar-nav li a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
  }

  .sidebar-nav li.active a {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    border-left: 3px solid #4a90e2;
  }

  .sidebar-nav li a img {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    opacity: 0.8;
  }

  .dashboard-content {
    flex-grow: 1;
    padding: 20px;
    background-color: #f5f8fa;
  }

  .content-header {
    margin-bottom: 25px;
  }

  .content-header h1 {
    margin: 0 0 5px;
    font-size: 24px;
    font-weight: 500;
    color: #333;
  }

  .content-header p {
    margin: 0;
    color: #666;
    font-size: 14px;
  }

  .dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
  }

  .card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .card-body {
    padding: 20px;
  }

  .card-title {
    margin: 0 0 15px;
    font-size: 18px;
    font-weight: 500;
    color: #2c3e50;
  }

  .card-text {
    margin: 0;
    font-size: 14px;
    color: #666;
    line-height: 1.5;
  }

  .alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
  }

  .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
</style>

<?php require_once __DIR__ . '/../../public/inc/script.php'; ?>
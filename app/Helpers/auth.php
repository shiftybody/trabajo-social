<?php

/**
 * Helpers simplificados para Auth
 */

use App\Core\Auth;

/**
 * Verifica si el usuario está autenticado
 */
function auth()
{
    return Auth::check();
}

/**
 * Obtiene el usuario autenticado
 */
function user()
{
    return Auth::user();
}

/**
 * Obtiene el ID del usuario
 */
function userId()
{
    return Auth::id();
}

/**
 * Verifica si el usuario tiene un permiso
 */
function can($permission)
{
    return Auth::can($permission);
}

/**
 * Verifica si el usuario NO tiene un permiso
 */
function cannot($permission)
{
    return Auth::cannot($permission);
}

/**
 * Verifica si el usuario tiene un rol específico
 */
function hasRole($roleId)
{
    return Auth::hasRole($roleId);
}

/**
 * Verifica si el usuario es administrador
 */
function isAdmin()
{
    return Auth::isAdmin();
}

/**
 * Obtiene el nombre del rol del usuario
 */
function roleName()
{
    return Auth::roleName();
}

/**
 * Obtiene el ID del rol del usuario
 */
function roleId()
{
    return Auth::role();
}

/**
 * Obtiene todos los permisos del usuario
 */
function permissions()
{
    return Auth::permissions();
}

/**
 * Obtiene el nombre completo del usuario
 */
function fullName()
{
    if (!auth()) return '';
    
    $user = user();
    return trim("{$user->usuario_nombre} {$user->usuario_apellido_paterno} {$user->usuario_apellido_materno}");
}

/**
 * Obtiene la URL del avatar del usuario
 */
function avatarUrl($size = 'thumbnail')
{
    if (!auth()) return APP_URL . "public/photos/{$size}/default.jpg";
    
    $avatar = user()->usuario_avatar ?: 'default.jpg';
    return APP_URL . "public/photos/{$size}/{$avatar}";
}

/**
 * Genera token CSRF
 */
function csrf()
{
    if (!isset($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

/**
 * Genera campo CSRF oculto para formularios
 */
function csrfField()
{
    return '<input type="hidden" name="_token" value="' . csrf() . '">';
}

/**
 * Verifica si el usuario es propietario de un recurso
 */
function isOwner($resourceUserId)
{
    return auth() && userId() == $resourceUserId;
}
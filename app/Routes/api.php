<?php

/**
 * En este archivo se definen las rutas de la API.
 * Las rutas están organizadas en grupos según su funcionalidad y permisos requeridos.
 */

use App\Core\Router;

$router = new Router();

// Rutas públicas
$router->post('/login', 'LoginController@login');
$router->post('/logout', 'LoginController@logout');

// Rutas protegidas (requieren autenticación)
$router->group(array('middleware' => 'Auth'), function ($router) {

  // SESSION
  $router->post('/session/refresh', 'SessionController@refresh');
  $router->get('/session/status', 'SessionController@status');

  // NAVIGATION
  $router->group(array('middleware' => 'Permission:search.view'), function ($router) {
    $router->get('/navigation-routes', 'RoleController@getNavigationRoutes');
  });

  // USERS
  $router->group(array('middleware' => 'Permission:users.manage|users.view'), function ($router) {
    $router->get('/users', 'UserController@getAllUsers');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.create|users.view|users.edit|roles.view'), function ($router) {
    $router->get('/roles', 'RoleController@getAllRoles');
    $router->post('/users', 'UserController@store');
    $router->get('/users/:id', 'UserController@getUserById');
    $router->post('/users/:id', 'UserController@update');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.edit'), function ($router) {
    $router->post('/users/:id/reset-password', 'UserController@resetPassword');
    $router->post('/users/:id/status', 'UserController@changeStatus');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.delete'), function ($router) {
    $router->delete('/users/:id', 'UserController@delete');
  });

  $router->get('/profile', 'ApiController@getProfile');

  // ROLES
  $router->group(['middleware' => 'Permission:roles.view'], function ($router) {
    $router->get('/roles/:id', 'RoleController@getRoleById');
  });

  $router->group(['middleware' => 'Permission:roles.create'], function ($router) {
    $router->post('/roles', 'RoleController@store');
  });

  // PERMISOS
  $router->group(['middleware' => 'Permission:permissions.view'], function ($router) {
    $router->get('/roles/:id/permissions', 'RoleController@getRolePermissions');
    $router->get('/permissions', 'RoleController@getAllPermissions');
  });

  $router->group(['middleware' => 'Permission:permissions.assign'], function ($router) {
    $router->post('/roles/:id', 'RoleController@update');
    $router->post('/roles/:id/permissions', 'RoleController@updateRolePermissions');
  });

  $router->group(['middleware' => 'Permission:roles.delete'], function ($router) {
    $router->delete('/roles/:id', 'RoleController@delete');
  });

  // ==================== CONFIGURACIÓN ====================

  // Rutas principales de configuración
  $router->group(['middleware' => 'Permission:settings.view|settings.manage'], function ($router) {
    // NUEVA RUTA: Estructura jerárquica de navegación
    $router->get('/settings/navigation', 'SettingController@getNavigationStructure');

    // Obtener contenido de secciones
    $router->get('/settings/section', 'SettingController@getSection');

    // Estadísticas generales
    $router->get('/settings/stats', 'SettingController@getConfigStats');

    // Datos auxiliares
    $router->get('/settings/categories', 'SettingController@getAllCategories');
    $router->get('/settings/subcategories', 'SettingController@getSubcategoriesByCategory');
  });

  // ==================== NIVELES SOCIOECONÓMICOS ====================

  $router->group(['middleware' => 'Permission:settings.levels.view|settings.manage'], function ($router) {
    $router->get('/settings/levels', 'SettingController@getAllLevels');
    $router->get('/settings/levels/:id', 'SettingController@getLevelById');
  });

  $router->group(['middleware' => 'Permission:settings.levels.create|settings.manage'], function ($router) {
    $router->post('/settings/levels', 'SettingController@createLevel');
  });

  $router->group(['middleware' => 'Permission:settings.levels.edit|settings.manage'], function ($router) {
    $router->post('/settings/levels/:id', 'SettingController@updateLevel');
    $router->post('/settings/levels/:id/status', 'SettingController@toggleLevelStatus');
  });

  $router->group(['middleware' => 'Permission:settings.levels.delete|settings.manage'], function ($router) {
    $router->delete('/settings/levels/:id', 'SettingController@deleteLevel');
  });

  // ==================== REGLAS DE APORTACIÓN ====================

  $router->group(['middleware' => 'Permission:settings.rules.view|settings.manage'], function ($router) {
    $router->get('/settings/rules', 'SettingController@getAllRules');
    $router->get('/settings/rules/:id', 'SettingController@getRuleById');
  });

  $router->group(['middleware' => 'Permission:settings.rules.create|settings.manage'], function ($router) {
    $router->post('/settings/rules', 'SettingController@createRule');
    $router->post('/settings/rules/bulk', 'SettingController@createBulkRules');
  });

  $router->group(['middleware' => 'Permission:settings.rules.edit|settings.manage'], function ($router) {
    $router->post('/settings/rules/:id', 'SettingController@updateRule');
    $router->post('/settings/rules/:id/status', 'SettingController@toggleRuleStatus');
  });

  $router->group(['middleware' => 'Permission:settings.rules.delete|settings.manage'], function ($router) {
    $router->delete('/settings/rules/:id', 'SettingController@deleteRule');
  });

  // ==================== CRITERIOS DE PUNTUACIÓN ====================

  $router->group(['middleware' => 'Permission:settings.criteria.view|settings.manage'], function ($router) {
    $router->get('/settings/criteria', 'SettingController@getAllCriteria');
    $router->get('/settings/criteria/:id', 'SettingController@getCriteriaById');
    $router->get('/settings/subcategories', 'SettingController@getAllSubcategories');
  });

  $router->group(['middleware' => 'Permission:settings.criteria.create|settings.manage'], function ($router) {
    $router->post('/settings/criteria', 'SettingController@createCriteria');
  });

  $router->group(['middleware' => 'Permission:settings.criteria.edit|settings.manage'], function ($router) {
    $router->post('/settings/criteria/:id', 'SettingController@updateCriteria');
    $router->post('/settings/criteria/:id/status', 'SettingController@toggleCriteriaStatus');
  });

  $router->group(['middleware' => 'Permission:settings.criteria.delete|settings.manage'], function ($router) {
    $router->delete('/settings/criteria/:id', 'SettingController@deleteCriteria');
  });

  // Estudios Socioeconómicos
  $router->group(['middleware' => 'Permission:patients.view'], function ($router) {
    $router->get('/studies/:study_id', 'StudiesController@getStudyById');
    $router->get('/studies/:study_id/summary', 'StudiesController@getStudySummary');
    $router->get('/studies/:study_id/family-members', 'StudiesController@getFamilyMembers');
    $router->get('/studies/:study_id/family-relations', 'StudiesController@getFamilyRelationsData');
    $router->get('/studies/:study_id/contact-data', 'StudiesController@getContactData');
    $router->get('/studies/socioeconomic-levels', 'StudiesController@getSocioeconomicLevels');
  });

  $router->group(['middleware' => 'Permission:patients.create'], function ($router) {
    $router->post('/studies/create/:patient_id', 'StudiesController@createStudy');
  });

  $router->group(['middleware' => 'Permission:patients.edit'], function ($router) {
    $router->post('/studies/update-section/:study_id', 'StudiesController@updateSection');
    $router->post('/studies/family-members/:study_id', 'StudiesController@saveFamilyMembers');
    $router->post('/studies/family-relations/:study_id', 'StudiesController@saveFamilyRelations');
    $router->post('/studies/upload-document/:study_id', 'StudiesController@uploadDocument');
    $router->post('/studies/evaluate-criteria', 'StudiesController@evaluateCriteria');
    $router->post('/studies/calculate-score/:study_id', 'StudiesController@calculateScore');
    $router->post('/studies/update-socioeconomic-level/:study_id', 'StudiesController@updateSocioeconomicLevel');
    $router->post('/studies/finalize/:study_id', 'StudiesController@finalizeStudy');
  });
});


return $router;

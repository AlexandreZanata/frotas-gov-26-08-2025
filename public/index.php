<?php
// Inicia a sessão no ponto de entrada único
session_start();

// Define uma constante para prevenir acesso direto
define('SYSTEM_LOADED', true);

// Carrega configurações e dependências
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Carrega o core
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Request.php';
require_once __DIR__ . '/../app/core/helpers.php';

$router = new Router();

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação e Cadastro Público
|--------------------------------------------------------------------------
*/
$router->get('login', 'AuthController@index');
$router->post('login/auth', 'AuthController@auth');
$router->get('logout', 'AuthController@logout');

$router->get('register', 'UserController@create');     // Página de cadastro
$router->post('register/store', 'UserController@store'); // Salvar novo usuário

/*
|--------------------------------------------------------------------------
| Rotas principais (dashboard)
|--------------------------------------------------------------------------
*/
$router->get('/', 'DashboardController@index'); 
$router->get('dashboard', 'DashboardController@index');

/*
|--------------------------------------------------------------------------
| Rotas de Gestão de Usuários (Admin)
|--------------------------------------------------------------------------
*/
$router->get('users/create', 'UserController@create'); 
$router->post('users/store', 'UserController@store');

/*
|--------------------------------------------------------------------------
| Rotas de Diário de Bordo (Runs)
|--------------------------------------------------------------------------
*/
// Fluxo principal de corrida
$router->get('runs/new', 'DiarioBordoController@create');
$router->post('runs/select-vehicle', 'DiarioBordoController@selectVehicle');

$router->get('runs/checklist', 'DiarioBordoController@checklist');
$router->post('runs/checklist/store', 'DiarioBordoController@storeChecklist');

$router->get('runs/start', 'DiarioBordoController@start');
$router->post('runs/start/store', 'DiarioBordoController@storeStart');

$router->get('runs/finish', 'DiarioBordoController@finish');
$router->post('runs/finish/store', 'DiarioBordoController@storeFinish');

// Relatórios e histórico
$router->get('runs/history', 'DiarioBordoController@history');
$router->get('runs/reports/generate', 'DiarioBordoController@generatePdfReport');

// Ajax / operações auxiliares
$router->post('runs/ajax-get-vehicle', 'DiarioBordoController@ajax_get_vehicle');
$router->post('runs/ajax-get-fuels', 'DiarioBordoController@ajax_get_fuels_by_station');
$router->post('runs/fueling/store', 'DiarioBordoController@storeFueling');

/*
|--------------------------------------------------------------------------
| Rotas do Gestor Setorial
|--------------------------------------------------------------------------
*/
// CRUD de usuários
$router->get('sector-manager/users/create', 'SectorManagerController@createUser');
$router->post('sector-manager/users/store', 'SectorManagerController@storeUser');
$router->get('sector-manager/users/manage', 'SectorManagerController@manageUsers');
$router->post('sector-manager/users/update', 'SectorManagerController@updateUser');
$router->post('sector-manager/users/reset-password', 'SectorManagerController@resetUserPassword');
$router->post('sector-manager/users/delete', 'SectorManagerController@deleteUser');

// Histórico e listagem
$router->get('sector-manager/users', 'SectorManagerController@listUsers'); // retrocompatibilidade
$router->get('sector-manager/users/history', 'SectorManagerController@history');
$router->get('sector-manager/history', 'SectorManagerController@history');

// Ajax
$router->post('sector-manager/ajax/get-user', 'SectorManagerController@ajax_get_user');
$router->get('sector-manager/ajax/search-users', 'SectorManagerController@ajax_search_users');

/*
|--------------------------------------------------------------------------
| Processamento da requisição
|--------------------------------------------------------------------------
*/
$router->dispatch(new Request());

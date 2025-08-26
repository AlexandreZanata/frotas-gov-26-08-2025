<?php
// Inicia a sessão no ponto de entrada único. Evita chamar em outros lugares.
session_start();

// Define uma constante para prevenir acesso direto a arquivos
define('SYSTEM_LOADED', true);

// Carrega configurações
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Carrega as classes principais do core
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Request.php';

// Cria uma instância do roteador
$router = new Router();

// --- DEFINIÇÃO DE ROTAS ---
// Usamos '/' para a rota raiz.

// Rotas de Autenticação
$router->get('login', 'AuthController@index');
$router->post('login/auth', 'AuthController@auth');
$router->get('logout', 'AuthController@logout');

// Rotas Principais (Protegidas)
$router->get('/', 'DashboardController@index'); // Rota raiz agora aponta para o dashboard
$router->get('dashboard', 'DashboardController@index');

// Rotas do Diário de Bordo (Exemplo)
$router->get('diario-bordo/historico', 'DiarioBordoController@historico');

$router->get('users/create', 'UserController@create'); 
$router->post('users/store', 'UserController@store'); 


// Processa a requisição atual com a URL já tratada
$router->dispatch(new Request());
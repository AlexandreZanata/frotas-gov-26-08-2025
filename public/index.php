<?php
session_start();

// Define uma constante para prevenir acesso direto a arquivos
define('SYSTEM_LOADED', true);

// Carrega configurações
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Carrega o roteador e outras classes principais
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Request.php';

// Cria uma instância do roteador
$router = new Router();

// Define as rotas da aplicação
// Use uma string vazia '' para a rota raiz
$router->get('', 'HomeController@index');
$router->get('frotas-gov/public', 'HomeController@index'); 
$router->get('diario-bordo/historico', 'DiarioBordoController@historico');
$router->get('login', 'AuthController@index');      // Exibe a página de login
$router->post('login/auth', 'AuthController@auth'); // Processa a tentativa de login
$router->get('dashboard', 'DashboardController@index');
$router->get('logout', 'AuthController@logout');


// Processa a requisição atual
$router->dispatch(new Request());
<?php
// Inicia a sessão no ponto de entrada único. Evita chamar em outros lugares.
session_start();

// Define uma constante para prevenir acesso direto a arquivos
define('SYSTEM_LOADED', true);

// Carrega configurações
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Carrega as classes principais do core
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Request.php';
require_once __DIR__ . '/../app/core/helpers.php'; // CORREÇÃO: Faltava o ';' aqui

// Cria uma instância do roteador
$router = new Router();

// --- DEFINIÇÃO DE ROTAS ---
// Usamos '/' para a rota raiz.

// Rotas de Autenticação e Cadastro Público
$router->get('login', 'AuthController@index');
$router->post('login/auth', 'AuthController@auth');
$router->get('logout', 'AuthController@logout');

// Rota para a página de cadastro (register)
$router->get('register', 'UserController@create'); // Aponta para o mesmo formulário
$router->post('register/store', 'UserController@store'); // Rota para salvar o novo usuário público

// Rotas Principais (Protegidas)
$router->get('/', 'DashboardController@index'); // Rota raiz agora aponta para o dashboard
$router->get('dashboard', 'DashboardController@index');

// Rotas de Gestão de Usuários (Protegidas para Admin)
// Mantemos estas rotas caso o admin também precise criar usuários diretamente
$router->get('users/create', 'UserController@create'); 
$router->post('users/store', 'UserController@store');

$router->get('runs/new', 'DiarioBordoController@create'); // Página 1: Escolher Veículo
$router->post('runs/select-vehicle', 'DiarioBordoController@selectVehicle'); // Processa a escolha

$router->get('runs/checklist', 'DiarioBordoController@checklist'); // Página 2: Checklist
$router->post('runs/checklist/store', 'DiarioBordoController@storeChecklist'); // Salva o checklist

$router->get('runs/start', 'DiarioBordoController@start'); // Página 3: Iniciar corrida
$router->post('runs/start/store', 'DiarioBordoController@storeStart'); // Inicia a corrida

$router->get('runs/finish', 'DiarioBordoController@finish'); // Página 4: Finalizar corrida
$router->post('runs/finish/store', 'DiarioBordoController@storeFinish'); // Finaliza a corrida

$router->get('runs/history', 'DiarioBordoController@history'); // Histórico de Corridas
$router->get('runs/reports/generate', 'DiarioBordoController@generatePdfReport');// Gerar PDF

$router->post('runs/ajax-get-vehicle', 'DiarioBordoController@ajax_get_vehicle');
$router->post('runs/select-vehicle', 'DiarioBordoController@selectVehicle'); 

// Rota para o JavaScript buscar os combustíveis e preços de um posto
$router->post('runs/ajax-get-fuels', 'DiarioBordoController@ajax_get_fuels_by_station');
// Rota para salvar o abastecimento de forma independente
$router->post('runs/fueling/store', 'DiarioBordoController@storeFueling');

// Rotas para o Gestor Setorial
$router->get('sector-manager/users/create', 'SectorManagerController@createUser');
$router->post('sector-manager/users/store', 'SectorManagerController@storeUser');

// rota para gerenciar usuarios
$router->get('sector-manager/users', 'SectorManagerController@listUsers');
$router->get('sector-manager/users/manage', 'SectorManagerController@manageUsers');

// Rota para a página de histórico de logs
$router->get('sector-manager/history', 'SectorManagerController@history');

// Processa a requisição atual com a URL já tratada
$router->dispatch(new Request());
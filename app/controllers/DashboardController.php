<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

// Inclui nossa classe de autenticação
require_once __DIR__ . '/../security/Auth.php';

class DashboardController
{
    public function index()
    {
        // **ESTA É A LINHA QUE PROTEGE A PÁGINA!**
        // Se o usuário não estiver logado, o script para aqui.
        Auth::checkAuthentication();

        // Se o script continuar, significa que o usuário está autenticado.
        // Agora podemos carregar a página de dashboard.
        require_once __DIR__ . '/../../templates/pages/dashboard.php';
    }
}
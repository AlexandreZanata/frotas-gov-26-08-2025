<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

class Auth
{
    /**
     * Verifica se o usuário está autenticado na sessão.
     * Se não estiver, redireciona para a página de login e encerra o script.
     */
    public static function checkAuthentication()
    {
        // Garante que a sessão foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Se a variável 'user_id' não existir na sessão, o usuário não está logado
        if (!isset($_SESSION['user_id'])) {
            // Redireciona para a página de login
            header('Location: /frotas-gov/public/login');
            // Encerra a execução do script para garantir que nada mais seja processado
            exit();
        }
    }
}
<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

class Request
{
    public function getUri()
    {
        // Pega a URL da requisição, remove a query string
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        // Se o projeto estiver em uma subpasta, ajuste o trim
        return trim($uri, '/');
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
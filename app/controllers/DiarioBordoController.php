<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

class DiarioBordoController
{
    /**
     * Exibe a página de histórico de corridas.
     */
    public function historico()
    {
        // 1. LÓGICA (futuramente, buscar dados do banco)
        // Por enquanto, vamos criar dados de exemplo.
        $corridas = [
            ['id' => 1, 'veiculo' => 'V-123', 'destino' => 'Secretaria de Saúde', 'data' => '2025-08-26'],
            ['id' => 2, 'veiculo' => 'A-01', 'destino' => 'Almoxarifado Central', 'data' => '2025-08-25'],
        ];

        // 2. CARREGAR A VIEW
        // Passa os dados para o arquivo de visualização (template)
        $this->view('diario_bordo/historico', ['corridas' => $corridas]);
    }

    /**
     * Função auxiliar para carregar uma view.
     * @param string $viewName O nome do arquivo da view em templates/pages
     * @param array $data Dados a serem extraídos e disponibilizados para a view
     */
    protected function view($viewName, $data = [])
    {
        // Transforma as chaves do array em variáveis (ex: $data['corridas'] vira $corridas)
        extract($data);
        
        // Carrega o header, a view da página e o footer
        require_once __DIR__ . '/../../templates/layouts/header.php';
        require_once __DIR__ . "/../../templates/pages/{$viewName}.php";
        require_once __DIR__ . '/../../templates/layouts/footer.php';
    }
}
<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../core/Database.php';

class DashboardController
{
    public function index()
    {
        Auth::checkAuthentication();

        if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 2) { // Role 'sector_manager'
            $this->loadAdminDashboard();
        } else {
            require_once __DIR__ . '/../../templates/pages/dashboard.php';
        }
    }

    private function loadAdminDashboard()
    {
        $db = new Database();
        $conn = $db->getConnection();
        // Pega o ID da secretaria do usuário logado na sessão
        $secretariat_id = $_SESSION['user_secretariat_id'] ?? 0;

        // --- DADOS PARA OS KPIs (Agora filtrados por secretaria) ---

        // Total de Corridas da Secretaria
        $stmtRuns = $conn->prepare("SELECT COUNT(id) as total FROM runs WHERE secretariat_id = ?");
        $stmtRuns->execute([$secretariat_id]);
        $totalRuns = $stmtRuns->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Veículos em Uso (da secretaria)
        $stmtVehiclesInUse = $conn->prepare("SELECT COUNT(id) as total FROM vehicles WHERE current_secretariat_id = ? AND status = 'in_use'");
        $stmtVehiclesInUse->execute([$secretariat_id]);
        $totalVehiclesInUse = $stmtVehiclesInUse->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Gasto Total com Combustível da Secretaria
        $stmtFuel = $conn->prepare("SELECT SUM(total_value) as total FROM fuelings WHERE secretariat_id = ?");
        $stmtFuel->execute([$secretariat_id]);
        $totalFuelCost = $stmtFuel->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Quilometragem Total da Secretaria
        $stmtKm = $conn->prepare("SELECT SUM(end_km - start_km) as total FROM runs WHERE secretariat_id = ? AND status = 'completed'");
        $stmtKm->execute([$secretariat_id]);
        $totalKm = $stmtKm->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // --- DADOS PARA OS GRÁFICOS (Agora filtrados por secretaria) ---

        // Gráfico de Corridas por Veículo
        $stmtRunsByVehicle = $conn->prepare("
            SELECT v.name, COUNT(r.id) as run_count
            FROM runs r
            JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.secretariat_id = ?
            GROUP BY v.name
            ORDER BY run_count DESC
        ");
        $stmtRunsByVehicle->execute([$secretariat_id]);
        $runsByVehicleData = $stmtRunsByVehicle->fetchAll(PDO::FETCH_ASSOC);

        // Gráfico de Gastos Mensais com Combustível
        $stmtMonthlyFuel = $conn->prepare("
            SELECT 
                DATE_FORMAT(f.created_at, '%Y-%m') as month,
                SUM(f.total_value) as total_value
            FROM fuelings f
            WHERE f.secretariat_id = ?
            GROUP BY month
            ORDER BY month ASC
        ");
        $stmtMonthlyFuel->execute([$secretariat_id]);
        $monthlyFuelData = $stmtMonthlyFuel->fetchAll(PDO::FETCH_ASSOC);

        // Passa os dados para a view
        $data = [
            'totalRuns' => $totalRuns,
            'totalVehiclesInUse' => $totalVehiclesInUse,
            'totalFuelCost' => number_format($totalFuelCost, 2, ',', '.'),
            'totalKm' => $totalKm,
            'runsByVehicleData' => json_encode($runsByVehicleData),
            'monthlyFuelData' => json_encode($monthlyFuelData)
        ];

        extract($data);

        require_once __DIR__ . '/../../templates/pages/admin_dashboard.php';
    }
}
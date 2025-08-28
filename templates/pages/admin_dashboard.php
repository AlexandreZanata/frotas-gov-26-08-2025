<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Gestor - Frotas Gov</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="overlay"></div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Frotas Gov</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Painel</a></li>
                <li><a href="#"><i class="fas fa-car"></i> Veículos</a></li>
                <li><a href="#"><i class="fas fa-road"></i> Corridas</a></li>
                <li><a href="#"><i class="fas fa-gas-pump"></i> Abastecimentos</a></li>
                
                <?php if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 2): ?>
                <li><a href="<?php echo BASE_URL; ?>/sector-manager/users/create"><i class="fas fa-user-plus"></i> Cadastrar Usuário</a></li>
                <?php endif; ?>

                <li><a href="/frotas-gov/public/logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="mobile-header">
            <h2>Painel do Gestor</h2>
            <button id="menu-toggle"><i class="fas fa-bars"></i></button>
        </header>

        <header class="header">
            <h1>Painel de Controle do Gestor</h1>
            <div class="user-info">
                <span>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
            </div>
        </header>

        <section class="kpi-grid">
            <div class="kpi-card">
                <h3>Total de Corridas</h3>
                <p class="kpi-value"><?php echo $totalRuns; ?></p>
            </div>
            <div class="kpi-card">
                <h3>Veículos em Uso</h3>
                <p class="kpi-value"><?php echo $totalVehiclesInUse; ?></p>
            </div>
            <div class="kpi-card">
                <h3>Gasto com Combustível</h3>
                <p class="kpi-value">R$ <?php echo $totalFuelCost; ?></p>
            </div>
            <div class="kpi-card">
                <h3>Quilometragem Total</h3>
                <p class="kpi-value"><?php echo $totalKm; ?> Km</p>
            </div>
        </section>

        <section class="charts-section">
            <div class="chart-container">
                <h3>Corridas por Veículo</h3>
                <div class="scrollable-chart">
                    <canvas id="runsByVehicleChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h3>Gastos Mensais com Combustível</h3>
                <canvas id="fuelExpensesChart"></canvas>
            </div>
        </section>

    </main>

    <script>
        const runsByVehicleData = <?php echo $runsByVehicleData; ?>;
        const monthlyFuelData = <?php echo $monthlyFuelData; ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/admin_dashboard.js"></script>
    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.overlay').classList.toggle('active');
        });
        document.querySelector('.overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('open');
            this.classList.remove('active');
        });
    </script>
</body>
</html>

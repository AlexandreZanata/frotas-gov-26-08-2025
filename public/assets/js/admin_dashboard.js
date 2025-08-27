document.addEventListener('DOMContentLoaded', function () {
    // Processa os dados para o gráfico de corridas por veículo
    const vehicleLabels = runsByVehicleData.map(item => item.name);
    const vehicleRunCounts = runsByVehicleData.map(item => item.run_count);
    
    // Processa os dados para o gráfico de gastos mensais
    const monthLabels = monthlyFuelData.map(item => {
        const [year, month] = item.month.split('-');
        return `${month}/${year}`;
    });
    const monthFuelValues = monthlyFuelData.map(item => item.total_value);

    // Gráfico de Corridas por Veículo (Barras)
    const runsByVehicleCtx = document.getElementById('runsByVehicleChart')?.getContext('2d');
    if (runsByVehicleCtx) {
        new Chart(runsByVehicleCtx, {
            type: 'bar',
            data: {
                labels: vehicleLabels,
                datasets: [{
                    label: 'Número de Corridas',
                    data: vehicleRunCounts,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Gráfico de Gastos Mensais com Combustível (Linha)
    const fuelExpensesCtx = document.getElementById('fuelExpensesChart')?.getContext('2d');
    if (fuelExpensesCtx) {
        new Chart(fuelExpensesCtx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Gastos com Combustível (R$)',
                    data: monthFuelValues,
                    fill: false,
                    borderColor: 'rgb(220, 53, 69)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
            }
        });
    }
});
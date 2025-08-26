<div class="container">
    <h1>Histórico de Corridas</h1>
    <p>Aqui está a lista de todas as corridas realizadas.</p>

    <table border="1" width="100%" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Veículo</th>
                <th>Destino</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($corridas as $corrida): ?>
                <tr>
                    <td><?php echo htmlspecialchars($corrida['id']); ?></td>
                    <td><?php echo htmlspecialchars($corrida['veiculo']); ?></td>
                    <td><?php echo htmlspecialchars($corrida['destino']); ?></td>
                    <td><?php echo htmlspecialchars($corrida['data']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
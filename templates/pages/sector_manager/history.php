<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Histórico de Alterações</title>
</head>
<body>
    <main class="main-content">
        <header class="header">
            <h1>Histórico de Alterações</h1>
        </header>

        <div class="content-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuário Responsável</th>
                            <th>Ação</th>
                            <th>Registro Afetado</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($log['user_name'] ?? 'Sistema'); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['table_name'] . ' (ID: ' . $log['record_id'] . ')'); ?></td>
                                <td>
                                    <button>Ver Detalhes</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
<main class="main-content">
    <header class="header">
        <h1>Controle de Usuários</h1>
    </header>

    <div class="content-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['cpf']); ?></td>
                            <td><?php echo $user['status'] == 'active' ? 'Ativo' : 'Inativo'; ?></td>
                            <td>
                                <a href="#" class="btn-edit">Editar</a>
                                <a href="#" class="btn-reset-password">Resetar Senha</a>
                                <a href="#" class="btn-delete">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Usuários - Frotas Gov</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        /* Estilos rápidos para a tabela e modal */
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background-color: #fefefe; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
    </style>
</head>
<body>
    <div class="container"> <h1>Controle de Usuários</h1>
        <p>Gerencie os usuários da sua secretaria.</p>

        <?php if (isset($_GET['reset_success'])): ?>
            <div class="alert alert-success">
                A senha de <strong><?php echo htmlspecialchars($_GET['user_name']); ?></strong> foi resetada com sucesso para o número do CPF (apenas números).
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nome Completo</th>
                    <th>Email</th>
                    <th>CPF</th>
                    <th>Cargo/Função</th>
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
                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role_name']))); ?></td>
                    <td><?php echo $user['status'] === 'active' ? 'Ativo' : 'Inativo'; ?></td>
                    <td>
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>)">Editar</button>
                        <button onclick="openDeleteModal(<?php echo $user['id']; ?>)">Excluir</button>
                        <form action="<?php echo BASE_URL; ?>/admin/users/reset-password" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja resetar a senha deste usuário para o CPF dele?');">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit">Resetar Senha</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <h2>Editar Usuário</h2>
            <form action="<?php echo BASE_URL; ?>/admin/users/update" method="POST">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="form-group">
                    <label for="edit_name">Nome Completo:</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                 <div class="form-group">
                    <label for="edit_cpf">CPF:</label>
                    <input type="text" id="edit_cpf" name="cpf" required>
                </div>
                <div class="form-group">
                    <label for="edit_role_id">Cargo/Função:</label>
                    <select id="edit_role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <?php if ($role['id'] > $admin_role_id): // Regra de negócio ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo htmlspecialchars($role['description']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                    </select>
                </div>
                <button type="submit" class="btn">Salvar Alterações</button>
                <button type="button" class="btn" onclick="closeEditModal()">Cancelar</button>
            </form>
        </div>
    </div>
    
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <h2>Excluir Usuário</h2>
            <p><strong>Atenção:</strong> Esta ação é irreversível. Para confirmar, preencha os campos abaixo.</p>
            <form action="<?php echo BASE_URL; ?>/admin/users/delete" method="POST">
                <input type="hidden" id="delete_user_id" name="user_id">
                <div class="form-group">
                    <label for="justification">Justificativa:</label>
                    <textarea id="justification" name="justification" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="confirmation_phrase">Digite a frase para confirmar:</label>
                    <p><code>para excluir eu entendo que essa mudança é irreversivel</code></p>
                    <input type="text" id="confirmation_phrase" name="confirmation_phrase" required>
                </div>
                <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                <button type="button" class="btn" onclick="closeDeleteModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        const editModal = document.getElementById('editUserModal');
        const deleteModal = document.getElementById('deleteUserModal');

        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_cpf').value = user.cpf;
            
            // Pré-seleciona o cargo e o status
            document.getElementById('edit_role_id').value = user.role_id;
            document.getElementById('edit_status').value = user.status;
            
            editModal.style.display = 'flex';
        }

        function closeEditModal() {
            editModal.style.display = 'none';
        }
        
        function openDeleteModal(userId) {
            document.getElementById('delete_user_id').value = userId;
            deleteModal.style.display = 'flex';
        }

        function closeDeleteModal() {
            deleteModal.style.display = 'none';
        }
        
        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>
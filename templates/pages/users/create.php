<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Novo Usuário</title>
    <link rel="stylesheet" href="/frotas-gov/public/assets/css/style.css">
</head>
<body>
    <div class="login-container" style="max-width: 600px;">
        <h2>Cadastro de Novo Usuário</h2>
        <form class="login-form" action="/frotas-gov/public/users/store" method="POST">
            
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="name">Nome Completo:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" required pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="000.000.000-00">
            </div>

            <div class="form-group">
                <label for="secretariat_id">Secretaria:</label>
                <select id="secretariat_id" name="secretariat_id" required>
                    <option value="">-- Selecione uma Secretaria --</option>
                    <?php foreach ($secretariats as $secretariat): ?>
                        <option value="<?php echo htmlspecialchars($secretariat['id']); ?>">
                            <?php echo htmlspecialchars($secretariat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Senha Provisória:</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <button type="submit" class="btn">Cadastrar Usuário</button>
        </form>
    </div>
</body>
</html>
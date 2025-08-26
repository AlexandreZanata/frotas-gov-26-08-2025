<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Frotas Gov</title>
    </head>
<body>
    <h1>Painel de Controle</h1>
    <p>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Bem-vindo ao sistema.</p>
    <hr>

    <?php 
    // Mostra o link de cadastro apenas para o Gestor Geral (role_id = 1)
    if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 1): 
    ?>
        <h3>Administração</h3>
        <a href="/frotas-gov/public/users/create" style="padding: 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">
            Cadastrar Novo Usuário
        </a>
        <br><br>
    <?php endif; ?>

    <a href="/frotas-gov/public/logout">Sair do Sistema</a>
</body>
</html>
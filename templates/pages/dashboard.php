<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Frotas Gov</title>
</head>
<body>
    <h1>Painel de Controle</h1>
    <p>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Bem-vindo ao sistema.</p>

    <a href="/frotas-gov/public/logout">Sair do Sistema</a>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Frotas Gov</title>
    <link rel="stylesheet" href="/frotas-gov/public/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Frotas Gov</h2>
        <form class="login-form" action="/frotas-gov/public/login/auth" method="POST">
            <div class="form-group">
                <label for="login">Email ou CPF:</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group-remember">
                <input type="checkbox" id="remember_me" name="remember_me" value="1">
                <label for="remember_me">Lembrar de mim por 30 dias</label>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</body>
</html>
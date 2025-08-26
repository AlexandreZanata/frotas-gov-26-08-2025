<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Frotas Gov</title>
    <link rel="stylesheet" href="/frotas-gov/public/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Frotas Gov</h2>
        <form class="login-form" action="/frotas-gov/public/login/auth" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</body>
</html>
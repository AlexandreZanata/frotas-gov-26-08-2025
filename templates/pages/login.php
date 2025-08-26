<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Frotas Gov</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <?php include_once __DIR__ . '/../layouts/icons.php'; // Inclui os ícones SVG ?>

    <div class="login-container">
        <h2>Frotas Gov</h2>
        <form class="login-form" action="<?php echo BASE_URL; ?>/login/auth" method="POST">
            <div class="form-group">
                <label for="login">Email ou CPF:</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle">
                        <svg><use xlink:href="#icon-eye"></use></svg>
                    </button>
                </div>
            </div>
            <div class="form-group-remember">
                <input type="checkbox" id="remember_me" name="remember_me" value="1">
                <label for="remember_me">Lembrar de mim por 30 dias</label>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>

        <div class="register-link" style="margin-top: 20px;">
            <p>Não tem uma conta? <a href="<?php echo BASE_URL; ?>/register">Cadastre-se aqui</a></p>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
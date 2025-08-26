<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Hash.php';

class AuthController
{
    /**
     * Exibe a página de login.
     */
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /frotas-gov/public/dashboard');
            exit();
        }
        require_once __DIR__ . '/../../templates/pages/login.php';
    }

    /**
     * Processa a tentativa de autenticação com E-mail ou CPF.
     */
    public function auth()
    {
        // 1. Recebe o login (pode ser email ou cpf) e a senha
        $login = trim($_POST['login']);
        $password = $_POST['password'];

        if (empty($login) || empty($password)) {
            die("Login e senha são obrigatórios.");
        }

        // Limpa o CPF de qualquer formatação (pontos, traços)
        $cpf = preg_replace('/[^0-9]/', '', $login);

        $database = new Database();
        $conn = $database->getConnection();
        if (!$conn) {
            die("Não foi possível conectar ao banco de dados.");
        }

        // 2. Prepara a query para buscar por email OU por cpf
        $stmt = $conn->prepare(
            "SELECT id, name, password, role_id FROM users WHERE (email = :login OR cpf = :cpf) AND status = 'active'"
        );
        $stmt->execute(['login' => $login, 'cpf' => $cpf]);
        $user = $stmt->fetch();

        // 3. Verifica a senha
        if ($user && Hash::verify($password, $user['password'])) {
            // Senha correta
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role_id'] = $user['role_id'];
            
            if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
                Auth::createRememberMeToken($user['id']);
            }
            
            header('Location: /frotas-gov/public/dashboard');
            exit();
        } else {
            // Credenciais incorretas
            // O ideal é redirecionar com uma mensagem de erro
            echo "Credenciais incorretas.";
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout()
    {
        session_unset();
        session_destroy();

        if (isset($_COOKIE['remember_me'])) {
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', '', time() - 3600, '/');
        }

        header('Location: /frotas-gov/public/login');
        exit();
    }
}
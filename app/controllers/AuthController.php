<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

// Carrega as dependências no início do arquivo
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../security/Auth.php'; // Essencial para o "Lembrar de Mim"
require_once __DIR__ . '/../security/Hash.php';   // Boa prática usar a classe Hash

class AuthController
{
    /**
     * Exibe a página de login ou redireciona se já estiver logado.
     */
    public function index()
    {
        // Se o usuário já estiver logado (via sessão ou cookie), redireciona para o dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /frotas-gov/public/dashboard');
            exit();
        }

        // Se não estiver logado, carrega a view do formulário de login
        require_once __DIR__ . '/../../templates/pages/login.php';
    }

    /**
     * Processa a tentativa de autenticação do usuário.
     */
    public function auth()
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email || empty($password)) {
            // Futuramente, redirecionar com mensagem de erro
            echo "Email ou senha inválidos.";
            return;
        }

        $database = new Database();
        $conn = $database->getConnection();

        if (!$conn) {
            die("Não foi possível conectar ao banco de dados.");
        }

        $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = :email AND status = 'active'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Usando a classe Hash para verificação
        if ($user && Hash::verify($password, $user['password'])) {
            // Senha correta!
            
            // Regenera o ID da sessão para segurança
            session_regenerate_id(true);

            // Armazena os dados do usuário na sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role_id'] = $user['role_id'];
            
            // Se o checkbox "Lembrar de mim" foi marcado, cria o token
            if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
                Auth::createRememberMeToken($user['id']);
            }
            
            // Redireciona para o painel principal (dashboard)
            header('Location: /frotas-gov/public/dashboard');
            exit();

        } else {
            // Credenciais incorretas
            // Futuramente, redirecionar de volta para o login com uma mensagem de erro
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

        // Remove o cookie "lembrar de mim" ao fazer logout
        if (isset($_COOKIE['remember_me'])) {
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', '', time() - 3600, '/'); // Define um tempo expirado
        }

        header('Location: /frotas-gov/public/login');
        exit();
    }
}
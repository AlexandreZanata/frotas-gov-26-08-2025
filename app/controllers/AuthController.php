<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

// Futuramente, esta classe irá interagir com o Model para buscar dados
require_once __DIR__ . '/../core/Database.php'; 

class AuthController
{
    /**
     * Exibe a página de login.
     */
    public function index()
    {
        session_start();

        // Se o usuário já estiver logado, redireciona para o dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /frotas-gov/public/dashboard');
            exit();
        }

        // Se não estiver logado, carrega a view do formulário de login
        require_once __DIR__ . '/../../templates/pages/login.php';
    }

    /**
     * Processa a tentativa de autenticação.
     */
public function auth()
{
    // 1. Obter e sanitizar os dados do formulário
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email || empty($password)) {
        echo "Email ou senha inválidos.";
        return;
    }

    // 2. INSTANCIAR a classe Database e OBTER a conexão
    $database = new Database();
    $conn = $database->getConnection();

    // Se a conexão falhar, $conn será null
    if (!$conn) {
        die("Não foi possível conectar ao banco de dados.");
    }

    // 3. Buscar o usuário pelo email usando prepared statement
    $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = :email AND status = 'active'");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(); // Não precisa de PDO::FETCH_ASSOC aqui, pois já é o padrão

    // 4. Verificar a senha
    if ($user && password_verify($password, $user['password'])) {
        // Senha correta!
        
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role_id'] = $user['role_id'];
        
        // Redirecionar para o painel
        header('Location: /frotas-gov/public/'); // Redirecionando para a raiz do projeto
        exit();

    } else {
        // Credenciais incorretas
        echo "Credenciais incorretas.";
    }
}

public function logout()
{
    session_start();
    session_unset(); // Remove todas as variáveis da sessão
    session_destroy(); // Destrói a sessão

    // Redireciona para a página de login
    header('Location: /frotas-gov/public/login');
    exit();
}
}
<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Hash.php';
require_once __DIR__ . '/../core/Database.php';

class SectorManagerController
{
    public function __construct()
    {
        Auth::checkAuthentication();
        // Garante que apenas o Gestor Setorial (role_id = 2) acesse este controller
        if ($_SESSION['user_role_id'] != 2) {
            show_error_page('Acesso Negado', 'Você não tem permissão para acessar esta página.', 403);
        }
    }

    /**
     * Exibe o formulário para o Gestor Setorial criar um novo usuário.
     */
    public function createUser()
    {
        // Gera um token CSRF para proteger o formulário
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $database = new Database();
        $conn = $database->getConnection();

        // Busca os papéis (roles) para preencher o <select>
        $roles = $conn->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll();

        $data = [
            'csrf_token' => $_SESSION['csrf_token'],
            'roles' => $roles,
            'secretariat_id' => $_SESSION['user_secretariat_id'] // Passa o ID da secretaria do gestor
        ];

        extract($data);
        
        // CORREÇÃO AQUI: Alterado de 'create_user.php' para 'create_users.php'
        require_once __DIR__ . '/../../templates/pages/sector_manager/create_users.php';
    }

    /**
     * Armazena o novo usuário no banco de dados.
     */
    public function storeUser()
    {
        // Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            show_error_page('Acesso Inválido', 'Houve um erro de validação de segurança (CSRF).', 403);
        }

        // Validação dos dados do formulário
        $name = trim($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
        $status = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'inactive';

        // Campos não obrigatórios
        $cnh_number = !empty($_POST['cnh_number']) ? trim($_POST['cnh_number']) : null;
        $cnh_expiry_date = !empty($_POST['cnh_expiry_date']) ? trim($_POST['cnh_expiry_date']) : null;
        $phone = !empty($_POST['phone']) ? preg_replace('/[^0-9]/', '', $_POST['phone']) : null;

        if (empty($name) || !$email || empty($cpf) || !$role_id) {
            show_error_page('Dados Inválidos', 'Nome, E-mail, CPF e Cargo são obrigatórios.');
        }

        if (strlen($cpf) != 11) {
            show_error_page('CPF Inválido', 'O CPF deve ter 11 dígitos.');
        }

        $database = new Database();
        $conn = $database->getConnection();

        // Verifica se o e-mail ou CPF já existem
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email OR cpf = :cpf");
        $stmt->execute(['email' => $email, 'cpf' => $cpf]);
        if ($stmt->fetch()) {
            show_error_page('Erro de Cadastro', 'O e-mail ou CPF informado já está cadastrado.');
        }

        // Cria a senha padrão e o hash
        $defaultPassword = $cpf . '@frotas';
        $hashedPassword = Hash::make($defaultPassword);
        $secretariat_id = $_SESSION['user_secretariat_id']; // ID da secretaria do gestor logado

        // Insere o usuário no banco
        try {
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, cpf, password, role_id, secretariat_id, status, cnh_number, cnh_expiry_date, phone)
                 VALUES (:name, :email, :cpf, :password, :role_id, :secretariat_id, :status, :cnh_number, :cnh_expiry_date, :phone)"
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'cpf' => $cpf,
                'password' => $hashedPassword,
                'role_id' => $role_id,
                'secretariat_id' => $secretariat_id,
                'status' => $status,
                'cnh_number' => $cnh_number,
                'cnh_expiry_date' => $cnh_expiry_date,
                'phone' => $phone
            ]);

            unset($_SESSION['csrf_token']);

            // Redireciona para a página de criação com mensagem de sucesso
            $_SESSION['success_message'] = "Usuário cadastrado com sucesso! A senha padrão é o CPF + @frotas.";
            header('Location: ' . BASE_URL . '/sector-manager/users/create');
            exit();

        } catch (PDOException $e) {
            // Em ambiente de desenvolvimento, você pode querer logar o erro: error_log($e->getMessage());
            show_error_page('Erro Interno', 'Não foi possível processar o cadastro. Tente novamente mais tarde.', 500);
        }
    }
}

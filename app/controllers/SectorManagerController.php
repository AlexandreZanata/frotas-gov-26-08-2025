<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Hash.php';
require_once __DIR__ . '/../core/Database.php';

class SectorManagerController
{
    private $conn;

    public function __construct()
    {
        Auth::checkAuthentication();
        
        // Garante que apenas o Gestor Setorial (role_id = 2) acesse este controller
        if ($_SESSION['user_role_id'] != 2) {
            show_error_page('Acesso Negado', 'Você não tem permissão para acessar esta página.', 403);
        }

        // Inicia a conexão com o banco de dados
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Exibe o formulário para o Gestor Setorial criar um novo usuário.
     * Os cargos exibidos são apenas aqueles com nível inferior ao do gestor.
     */
    public function createUser()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // ALTERAÇÃO: Busca apenas os papéis (roles) com ID maior que o do gestor logado.
        $stmt = $this->conn->prepare("SELECT id, name FROM roles WHERE id > :role_id ORDER BY name ASC");
        $stmt->execute(['role_id' => $_SESSION['user_role_id']]);
        $roles = $stmt->fetchAll();

        $data = [
            'csrf_token' => $_SESSION['csrf_token'],
            'roles' => $roles,
        ];

        extract($data);
        
        require_once __DIR__ . '/../../templates/pages/sector_manager/create_users.php';
    }

    /**
     * Armazena o novo usuário no banco de dados.
     */
    public function storeUser()
    {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            show_error_page('Acesso Inválido', 'Houve um erro de validação de segurança (CSRF).', 403);
        }

        $name = trim($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
        $status = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'inactive';

        // Validações
        if (empty($name) || !$email || empty($cpf) || !$role_id) {
            show_error_page('Dados Inválidos', 'Nome, E-mail, CPF e Cargo são obrigatórios.');
        }

        // VERIFICAÇÃO ADICIONAL: Garante que o gestor não está criando um usuário com cargo superior ou igual ao seu
        if ($role_id <= $_SESSION['user_role_id']) {
            show_error_page('Acesso Negado', 'Você não pode criar usuários com cargo igual ou superior ao seu.', 403);
        }

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = :email OR cpf = :cpf");
        $stmt->execute(['email' => $email, 'cpf' => $cpf]);
        if ($stmt->fetch()) {
            show_error_page('Erro de Cadastro', 'O e-mail ou CPF informado já está cadastrado.');
        }

        $defaultPassword = $cpf . '@frotas';
        $hashedPassword = Hash::make($defaultPassword);

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO users (name, email, cpf, password, role_id, secretariat_id, status)
                 VALUES (:name, :email, :cpf, :password, :role_id, :secretariat_id, :status)"
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'cpf' => $cpf,
                'password' => $hashedPassword,
                'role_id' => $role_id,
                'secretariat_id' => $_SESSION['user_secretariat_id'],
                'status' => $status
            ]);

            unset($_SESSION['csrf_token']);
            $_SESSION['success_message'] = "Usuário cadastrado com sucesso! A senha padrão é o CPF + @frotas.";
            header('Location: ' . BASE_URL . '/sector-manager/users/create');
            exit();

        } catch (PDOException $e) {
            show_error_page('Erro Interno', 'Não foi possível processar o cadastro.', 500);
        }
    }

    /**
     * Exibe a lista de usuários da mesma secretaria do gestor.
     */
    public function listUsers()
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, cpf, status FROM users WHERE secretariat_id = :secretariat_id");
        $stmt->execute(['secretariat_id' => $_SESSION['user_secretariat_id']]);
        $users = $stmt->fetchAll();

        $data = ['users' => $users];
        extract($data);

        require_once __DIR__ . '/../../templates/pages/sector_manager/list_users.php';
    }

    /**
     * Atualiza os dados de um usuário.
     */
    public function updateUser()
    {
        // Validação do token CSRF e do método POST
        // ... (implementar lógica de validação)

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        // ... (outros campos para atualizar)

        try {
            $stmt = $this->conn->prepare(
                "UPDATE users SET name = :name WHERE id = :id AND secretariat_id = :secretariat_id"
            );
            $stmt->execute([
                'name' => $name,
                'id' => $userId,
                'secretariat_id' => $_SESSION['user_secretariat_id'] // Garante que o gestor só edite usuários da sua secretaria
            ]);
            
            $_SESSION['success_message'] = "Usuário atualizado com sucesso!";
            header('Location: ' . BASE_URL . '/sector-manager/users');
            exit();
        } catch (PDOException $e) {
            show_error_page('Erro Interno', 'Não foi possível atualizar o usuário.', 500);
        }
    }

    /**
     * Reseta a senha de um usuário para o padrão.
     */
    public function resetUserPassword()
    {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        try {
            // Primeiro, busca o CPF do usuário para gerar a nova senha
            $stmt = $this->conn->prepare("SELECT cpf FROM users WHERE id = :id AND secretariat_id = :secretariat_id");
            $stmt->execute([
                'id' => $userId,
                'secretariat_id' => $_SESSION['user_secretariat_id']
            ]);
            $user = $stmt->fetch();

            if (!$user) {
                show_error_page('Usuário não encontrado', 'O usuário não foi encontrado ou você não tem permissão para esta ação.', 404);
            }

            $defaultPassword = $user['cpf'] . '@frotas';
            $hashedPassword = Hash::make($defaultPassword);

            // Atualiza a senha no banco
            $stmt = $this->conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute(['password' => $hashedPassword, 'id' => $userId]);

            $_SESSION['success_message'] = "Senha do usuário resetada com sucesso!";
            header('Location: ' . BASE_URL . '/sector-manager/users');
            exit();

        } catch (PDOException $e) {
            show_error_page('Erro Interno', 'Não foi possível resetar a senha.', 500);
        }
    }
    
    /**
     * Exclui um usuário do sistema.
     */
    public function deleteUser()
    {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $justificativa = trim($_POST['justificativa']);
        $confirmacao = trim($_POST['confirm_phrase']);

        $fraseEsperada = "eu entendo que essa mudança é irreversivel";

        // Validação rigorosa
        if ($confirmacao !== $fraseEsperada || empty($justificativa)) {
            show_error_page('Confirmação Inválida', 'A frase de confirmação está incorreta ou a justificativa está vazia.', 400);
        }

        try {
            // (Opcional, mas recomendado) Logar a exclusão antes de apagar
            // file_put_contents('delete_log.txt', "Usuário ID {$userId} excluído por gestor ID {$_SESSION['user_id']} com a justificativa: {$justificativa}\n", FILE_APPEND);

            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id AND secretariat_id = :secretariat_id");
            $stmt->execute([
                'id' => $userId,
                'secretariat_id' => $_SESSION['user_secretariat_id']
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Usuário excluído com sucesso!";
            } else {
                $_SESSION['error_message'] = "Não foi possível excluir o usuário ou ele não foi encontrado.";
            }

            header('Location: ' . BASE_URL . '/sector-manager/users');
            exit();

        } catch (PDOException $e) {
            show_error_page('Erro Interno', 'Não foi possível processar a exclusão.', 500);
        }
    }
}
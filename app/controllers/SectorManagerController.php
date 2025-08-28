<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Hash.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/AuditLog.php';

class SectorManagerController
{
    private $conn;
    private $auditLog;

    public function __construct()
    {
        Auth::checkAuthentication();
        if ($_SESSION['user_role_id'] != 2) {
            show_error_page('Acesso Negado', 'Você não tem permissão para acessar esta página.', 403);
        }
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->auditLog = new AuditLog($this->conn);
    }

    /**
     * Prepara os dados iniciais para a página (primeira carga).
     * A busca e paginação dinâmicas serão feitas via AJAX.
     */
    public function createUser()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Dados para o formulário (cargos com hierarquia)
        $stmt_roles = $this->conn->prepare("SELECT id, name FROM roles WHERE id > :role_id ORDER BY name ASC");
        $stmt_roles->execute(['role_id' => $_SESSION['user_role_id']]);
        $roles = $stmt_roles->fetchAll();
        
        $data = [
            'csrf_token' => $_SESSION['csrf_token'],
            'roles' => $roles,
            'initialUsers' => $this->fetchUsersWithPagination() // Carrega a primeira página de usuários
        ];

        extract($data);
        require_once __DIR__ . '/../../templates/pages/sector_manager/create_users.php';
    }

    private function fetchUsersWithPagination($filters = [], $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = $filters['term'] ?? '';
        $roleId = $filters['role_id'] ?? 0;

        // --- 1. Constrói a query SQL e o array de parâmetros dinamicamente ---
        $sqlBase = "FROM users u JOIN roles r ON u.role_id = r.id WHERE u.secretariat_id = :secretariat_id AND u.id != :current_user_id";
        $params = [
            ':secretariat_id' => $_SESSION['user_secretariat_id'],
            ':current_user_id' => $_SESSION['user_id']
        ];

        if (!empty($searchTerm)) {
            $sqlBase .= " AND (u.name LIKE :term OR u.email LIKE :term OR u.cpf LIKE :term)";
            $params[':term'] = '%' . $searchTerm . '%';
        }

        if (!empty($roleId)) {
            $sqlBase .= " AND u.role_id = :role_id";
            $params[':role_id'] = $roleId;
        }

        // --- 2. Conta o total de registros para a paginação ---
        $stmtTotal = $this->conn->prepare("SELECT COUNT(u.id) as total " . $sqlBase);
        $stmtTotal->execute($params);
        $totalResults = $stmtTotal->fetch()['total'];
        $totalPages = ceil($totalResults / $perPage);

        // --- 3. Busca os usuários da página atual com vinculação explícita ---
        $sqlSelect = "SELECT u.id, u.name, u.email, u.cpf, u.status, r.name as role_name " . $sqlBase . " ORDER BY u.name ASC LIMIT :limit OFFSET :offset";
        
        $stmtUsers = $this->conn->prepare($sqlSelect);

        // Vincula os parâmetros dos filtros (name, email, etc.)
        foreach ($params as $key => $value) {
            $stmtUsers->bindValue($key, $value);
        }

        // Vincula os parâmetros de paginação explicitamente como inteiros. Esta é a correção crucial.
        $stmtUsers->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmtUsers->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        
        $stmtUsers->execute();
        $users = $stmtUsers->fetchAll();
        
        // --- 4. Gera o HTML da paginação (sem alteração) ---
        $paginationHtml = $this->generatePaginationHtml($page, $totalPages, $totalResults);

        return ['users' => $users, 'paginationHtml' => $paginationHtml, 'total' => $totalResults];
    }

    
    /**
     * NOVA FUNÇÃO: Gera o HTML dos links de paginação.
     */
    private function generatePaginationHtml($currentPage, $totalPages, $totalResults)
    {
        if ($totalPages <= 1) return "<p class='pagination-summary'>$totalResults resultado(s) encontrado(s).</p>";

        $html = '<nav class="pagination-nav"><ul class="pagination">';
        
        // Botão "Anterior"
        $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
        $html .= "<li class='page-item $prevDisabled'><a class='page-link' href='#' data-page='" . ($currentPage - 1) . "'>Anterior</a></li>";

        // Links das páginas
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i == $currentPage) ? 'active' : '';
            $html .= "<li class='page-item $active'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
        }

        // Botão "Próximo"
        $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
        $html .= "<li class='page-item $nextDisabled'><a class='page-link' href='#' data-page='" . ($currentPage + 1) . "'>Próximo</a></li>";
        
        $html .= "</ul></nav><p class='pagination-summary'>$totalResults resultado(s) no total.</p>";
        return $html;
    }

    /**
     * NOVO ENDPOINT AJAX: para busca e paginação.
     */
    public function ajax_search_users()
    {
        header('Content-Type: application/json');
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $filters = [
            'term' => filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING) ?: '',
            'role_id' => filter_input(INPUT_GET, 'role_id', FILTER_VALIDATE_INT) ?: 0
        ];
        
        $result = $this->fetchUsersWithPagination($filters, $page);
        
        echo json_encode(['success' => true, 'data' => $result]);
    }

    /**
     * Armazena o novo usuário no banco e registra o log.
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

        if (empty($name) || !$email || empty($cpf) || !$role_id) {
            show_error_page('Dados Inválidos', 'Nome, E-mail, CPF e Cargo são obrigatórios.');
        }

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
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO users (name, email, cpf, password, role_id, secretariat_id, status)
                 VALUES (:name, :email, :cpf, :password, :role_id, :secretariat_id, :status)"
            );
            $newData = [
                'name' => $name, 'email' => $email, 'cpf' => $cpf,
                'password' => 'SENHA PADRÃO', 'role_id' => $role_id,
                'secretariat_id' => $_SESSION['user_secretariat_id'], 'status' => $status
            ];
            $stmt->execute([
                ':name' => $name, ':email' => $email, ':cpf' => $cpf,
                ':password' => $hashedPassword, ':role_id' => $role_id,
                ':secretariat_id' => $_SESSION['user_secretariat_id'], ':status' => $status
            ]);
            $lastId = $this->conn->lastInsertId();

            $this->auditLog->log($_SESSION['user_id'], 'create_user', 'users', $lastId, null, $newData);
            $this->conn->commit();

            unset($_SESSION['csrf_token']);
            $_SESSION['success_message'] = "Usuário cadastrado com sucesso! A senha padrão é o CPF + @frotas.";
            header('Location: ' . BASE_URL . '/sector-manager/users/create');
            exit();

        } catch (PDOException $e) {
            $this->conn->rollBack();
            show_error_page('Erro Interno', 'Não foi possível processar o cadastro.', 500);
        }
    }

    /**
     * Atualiza os dados de um usuário e registra o log.
     */
    public function updateUser()
    {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        if (!$userId) {
            show_error_page('Erro', 'ID de usuário inválido.');
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id AND secretariat_id = :secretariat_id");
        $stmt->execute(['id' => $userId, 'secretariat_id' => $_SESSION['user_secretariat_id']]);
        $oldData = $stmt->fetch();

        if (!$oldData) {
            show_error_page('Acesso Negado', 'Usuário não encontrado ou não pertence à sua secretaria.', 404);
        }
        
        $name = trim($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
        $status = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'inactive';

        if ($role_id <= $_SESSION['user_role_id']) {
            show_error_page('Acesso Negado', 'Você não pode atribuir um cargo igual ou superior ao seu.', 403);
        }
        
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "UPDATE users SET name = :name, email = :email, cpf = :cpf, role_id = :role_id, status = :status
                 WHERE id = :id AND secretariat_id = :secretariat_id"
            );
            $newData = [
                'name' => $name, 'email' => $email, 'cpf' => $cpf, 
                'role_id' => $role_id, 'status' => $status
            ];
            $stmt->execute(array_merge($newData, [
                'id' => $userId, 
                'secretariat_id' => $_SESSION['user_secretariat_id']
            ]));
            
            unset($oldData['password']);

            $this->auditLog->log($_SESSION['user_id'], 'update_user', 'users', $userId, $oldData, $newData);
            $this->conn->commit();

            $_SESSION['success_message'] = "Usuário atualizado com sucesso!";
            header('Location: ' . BASE_URL . '/sector-manager/users/create');
            exit();
        } catch (PDOException $e) {
            $this->conn->rollBack();
            show_error_page('Erro Interno', 'Não foi possível atualizar o usuário.', 500);
        }
    }

    /**
     * Reseta a senha de um usuário para o padrão e registra o log.
     */
    public function resetUserPassword()
    {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT cpf FROM users WHERE id = :id AND secretariat_id = :secretariat_id");
            $stmt->execute(['id' => $userId, 'secretariat_id' => $_SESSION['user_secretariat_id']]);
            $user = $stmt->fetch();

            if (!$user) {
                show_error_page('Usuário não encontrado', 'O usuário não foi encontrado ou você não tem permissão.', 404);
            }

            $defaultPassword = $user['cpf'] . '@frotas';
            $hashedPassword = Hash::make($defaultPassword);

            $stmt = $this->conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute(['password' => $hashedPassword, 'id' => $userId]);

            $this->auditLog->log($_SESSION['user_id'], 'reset_password', 'users', $userId, ['password' => '******'], ['password' => 'SENHA PADRÃO']);
            $this->conn->commit();

            $_SESSION['success_message'] = "Senha do usuário resetada com sucesso!";
            header('Location: ' . BASE_URL . '/sector-manager/users/create');
            exit();

        } catch (PDOException $e) {
            $this->conn->rollBack();
            show_error_page('Erro Interno', 'Não foi possível resetar a senha.', 500);
        }
    }
    
    /**
     * ATUALIZADO: Lógica de exclusão mais segura.
     */
    public function deleteUser()
    {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        // 1. Verifica se o usuário tem corridas associadas
        $stmt_runs = $this->conn->prepare("SELECT COUNT(id) as total FROM runs WHERE driver_id = :user_id");
        $stmt_runs->execute(['user_id' => $userId]);
        if ($stmt_runs->fetch()['total'] > 0) {
            show_error_page('Exclusão não permitida', 'Este usuário não pode ser excluído pois possui corridas registradas em seu nome.', 403);
        }

        // Validações da frase de confirmação (mantidas)
        $justificativa = trim($_POST['justificativa']);
        $confirmacao = trim($_POST['confirm_phrase']);
        if ($confirmacao !== "eu entendo que essa mudança é irreversivel" || empty($justificativa)) {
            show_error_page('Confirmação Inválida', 'A frase de confirmação está incorreta ou a justificativa está vazia.', 400);
        }

        try {
            $this->conn->beginTransaction();

            // Pega os dados do usuário para o log antes de deletar
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id AND secretariat_id = :secretariat_id");
            $stmt->execute(['id' => $userId, 'secretariat_id' => $_SESSION['user_secretariat_id']]);
            $userData = $stmt->fetch();
            if (!$userData) throw new Exception("Usuário não encontrado.");
            unset($userData['password']);

            // 2. Exclui registros associados menos críticos (em cascata)
            $this->conn->prepare("DELETE FROM auth_tokens WHERE user_id = :user_id")->execute(['user_id' => $userId]);
            $this->conn->prepare("DELETE FROM checklists WHERE user_id = :user_id")->execute(['user_id' => $userId]);
            $this->conn->prepare("DELETE FROM fuelings WHERE user_id = :user_id")->execute(['user_id' => $userId]);
            // Adicione outras tabelas se necessário

            // 3. Exclui o usuário
            $stmt_delete = $this->conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt_delete->execute(['id' => $userId]);

            if ($stmt_delete->rowCount() > 0) {
                $this->auditLog->log($_SESSION['user_id'], 'delete_user', 'users', $userId, $userData, ['justificativa' => $justificativa]);
                $this->conn->commit();
                $_SESSION['success_message'] = "Usuário e todos os seus registros associados foram excluídos com sucesso!";
            } else {
                throw new Exception("Falha ao excluir o registro principal do usuário.");
            }

            header('Location: ' . BASE_URL . '/sector-manager/users/create');
            exit();

        } catch (Exception $e) {
            $this->conn->rollBack();
            // error_log($e->getMessage()); // Logar o erro real
            show_error_page('Erro Interno', 'Não foi possível processar a exclusão.', 500);
        }
    }

    /**
     * Busca os dados de um usuário para a função de editar (via AJAX).
     */
    public function ajax_get_user()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($input['user_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'ID de usuário inválido.']);
            return;
        }

        // Busca os dados do usuário, garantindo que ele pertence à secretaria do gestor
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, cpf, role_id, status, cnh_number, cnh_expiry_date, phone 
             FROM users 
             WHERE id = :id AND secretariat_id = :secretariat_id"
        );
        $stmt->execute(['id' => $userId, 'secretariat_id' => $_SESSION['user_secretariat_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuário não encontrado ou acesso negado.']);
        }
    }
}
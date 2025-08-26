<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Hash.php';
require_once __DIR__ . '/../core/Database.php';

class UserController
{
    /**
     * Exibe o formulário para criar um novo usuário.
     */
    public function create()
    {
        // 1. Acesso: Garante que apenas administradores acessem esta página
        Auth::checkAdmin();

        // 2. CSRF: Gera um token para proteger o formulário
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // 3. Dados: Busca as secretarias no banco para preencher o <select>
        $database = new Database();
        $conn = $database->getConnection();
        $secretariats = $conn->query("SELECT id, name FROM secretariats ORDER BY name ASC")->fetchAll();

        // 4. View: Carrega o formulário e passa os dados necessários
        $data = [
            'secretariats' => $secretariats,
            'csrf_token' => $_SESSION['csrf_token']
        ];
        
        // Carrega a view passando as variáveis $secretariats e $csrf_token
        extract($data);
        require_once __DIR__ . '/../../templates/pages/users/create.php';
    }

    /**
     * Armazena o novo usuário no banco de dados.
     */
    public function store()
    {
        // 1. Acesso e Segurança: Garante que é um admin e que o token CSRF é válido
        Auth::checkAdmin();
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('Erro de validação CSRF. Ação bloqueada.');
        }

        // 2. Validação dos Dados (ESSENCIAL)
        $name = trim($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Remove formatação do CPF
        $password = $_POST['password'];
        $secretariat_id = filter_input(INPUT_POST, 'secretariat_id', FILTER_VALIDATE_INT);

        // Validações de campos obrigatórios e formato
        if (empty($name) || !$email || empty($password) || !$secretariat_id || strlen($cpf) != 11) {
            die('Todos os campos são obrigatórios e devem ser válidos.');
        }

        // 3. Conexão e Verificação de Duplicidade
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email OR cpf = :cpf");
        $stmt->execute(['email' => $email, 'cpf' => $cpf]);
        if ($stmt->fetch()) {
            die('O email ou CPF informado já está cadastrado no sistema.');
        }

        // 4. Inserção Segura no Banco
        try {
            // Hash da senha usando nossa classe de segurança
            $hashedPassword = Hash::make($password);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, cpf, password, secretariat_id, role_id, status) 
                 VALUES (:name, :email, :cpf, :password, :secretariat_id, 4, 'active')" // Role 4 = Motorista (padrão)
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'cpf' => $cpf,
                'password' => $hashedPassword,
                'secretariat_id' => $secretariat_id,
            ]);

            // Remove o token para que não possa ser reutilizado
            unset($_SESSION['csrf_token']);
            
            // Redireciona para uma página de sucesso ou para a lista de usuários
            echo "Usuário criado com sucesso!";
            // header('Location: /frotas-gov/public/users'); // Exemplo de redirecionamento futuro

        } catch (PDOException $e) {
            // Em produção, logar o erro em vez de exibi-lo
            die('Erro ao criar usuário: ' . $e->getMessage());
        }
    }
}
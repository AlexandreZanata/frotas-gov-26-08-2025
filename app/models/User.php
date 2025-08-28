<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../core/Database.php';

class User
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Encontra um usuário pelo seu e-mail.
     * @param string $email O e-mail a ser pesquisado.
     * @return mixed Retorna os dados do usuário se encontrado, caso contrário, false.
     */
    public function findByEmail($email)
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, email FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Em um sistema real, logar o erro.
            return false;
        }
    }

    /**
     * Encontra um usuário pelo seu CPF.
     * @param string $cpf O CPF a ser pesquisado.
     * @return mixed Retorna os dados do usuário se encontrado, caso contrário, false.
     */
    public function findByCpf($cpf)
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, cpf FROM users WHERE cpf = :cpf");
            $stmt->execute(['cpf' => $cpf]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cria um novo usuário no banco de dados.
     * @param array $data Um array associativo com os dados do usuário.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function create($data)
    {
        $sql = "INSERT INTO users (
                    name, cpf, email, password, role_id, secretariat_id, 
                    department_id, cnh_number, cnh_expiry_date, phone, 
                    profile_photo_path, status
                ) VALUES (
                    :name, :cpf, :email, :password, :role_id, :secretariat_id, 
                    :department_id, :cnh_number, :cnh_expiry_date, :phone, 
                    :profile_photo_path, :status
                )";

        try {
            $stmt = $this->conn->prepare($sql);
            
            // Bind dos parâmetros
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':cpf', $data['cpf']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':password', $data['password']);
            $stmt->bindValue(':role_id', $data['role_id'], PDO::PARAM_INT);
            $stmt->bindValue(':secretariat_id', $data['secretariat_id'], PDO::PARAM_INT);
            $stmt->bindValue(':department_id', $data['department_id'], PDO::PARAM_INT);
            $stmt->bindValue(':cnh_number', $data['cnh_number']);
            $stmt->bindValue(':cnh_expiry_date', $data['cnh_expiry_date']);
            $stmt->bindValue(':phone', $data['phone']);
            $stmt->bindValue(':profile_photo_path', $data['profile_photo_path']);
            $stmt->bindValue(':status', $data['status']);

            return $stmt->execute();

        } catch (PDOException $e) {
            // Em um sistema de produção, você deve logar este erro.
            // error_log($e->getMessage());
            return false;
        }
    }
}

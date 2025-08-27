<?php

if (!defined('SYSTEM_LOADED')) {
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../core/Database.php';

class DiarioBordoController
{
    private $db;
    private $conn;
    private $user;

    public function __construct()
    {
        Auth::checkAuthentication();
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $stmt = $this->conn->prepare("SELECT id, name, secretariat_id FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $this->user = $stmt->fetch();
    }
    
    public function create()
    {
        $stmt = $this->conn->prepare(
            "SELECT id, start_km FROM runs 
             WHERE driver_id = :driver_id AND status = 'in_progress' 
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['driver_id' => $this->user['id']]);
        $activeRun = $stmt->fetch();

        if ($activeRun) {
            if ($activeRun['start_km'] === null) {
                header('Location: ' . BASE_URL . '/runs/start');
                exit();
            } else {
                header('Location: ' . BASE_URL . '/runs/finish');
                exit();
            }
        }

        unset($_SESSION['run_vehicle_id']);
        unset($_SESSION['run_id']);
        
        require_once __DIR__ . '/../../templates/pages/diario_bordo/select_vehicle.php';
    }

    public function ajax_get_vehicle()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $prefix = trim($input['prefix'] ?? '');
        if (empty($prefix)) {
            echo json_encode(['success' => false, 'message' => 'Prefixo não informado.']);
            return;
        }
        $stmt = $this->conn->prepare("SELECT v.id, v.plate, v.name, s.name as secretariat_name, v.current_secretariat_id FROM vehicles v JOIN secretariats s ON v.current_secretariat_id = s.id WHERE v.prefix = :prefix");
        $stmt->execute(['prefix' => $prefix]);
        $vehicle = $stmt->fetch();
        if (!$vehicle) {
            echo json_encode(['success' => false, 'message' => 'Veículo não encontrado.']);
            return;
        }
        if ($vehicle['current_secretariat_id'] != $this->user['secretariat_id']) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado. Veículo pertence a outra secretaria.']);
            return;
        }
        echo json_encode(['success' => true, 'vehicle' => $vehicle]);
    }

    public function selectVehicle()
    {
        $vehicle_id = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
        if (!$vehicle_id) {
            show_error_page('Dados Inválidos', 'O veículo selecionado não é válido.');
        }
        $stmt = $this->conn->prepare("SELECT status, current_secretariat_id FROM vehicles WHERE id = :id");
        $stmt->execute(['id' => $vehicle_id]);
        $vehicle = $stmt->fetch();
        if (!$vehicle || $vehicle['current_secretariat_id'] != $this->user['secretariat_id']) {
            show_error_page('Acesso Negado', 'Você não tem permissão para usar este veículo.');
        }
        if ($vehicle['status'] === 'in_use') {
            show_error_page('Veículo em Uso', 'Este veículo já está em uma corrida.');
        }
        $_SESSION['run_vehicle_id'] = $vehicle_id;
        header('Location: ' . BASE_URL . '/runs/checklist');
        exit();
    }

    public function checklist()
    {
        if (empty($_SESSION['run_vehicle_id'])) {
            header('Location: ' . BASE_URL . '/runs/new');
            exit();
        }
        
        $vehicle_id = $_SESSION['run_vehicle_id'];

        $items_stmt = $this->conn->query("SELECT id, name FROM checklist_items ORDER BY id");
        $items = $items_stmt->fetchAll();

        foreach ($items as &$item) {
            $last_status_stmt = $this->conn->prepare(
                "SELECT ca.status, ca.notes FROM checklist_answers ca
                 JOIN checklists c ON ca.checklist_id = c.id
                 WHERE c.vehicle_id = :vehicle_id AND ca.item_id = :item_id
                 ORDER BY c.created_at DESC LIMIT 1"
            );
            $last_status_stmt->execute(['vehicle_id' => $vehicle_id, 'item_id' => $item['id']]);
            $result = $last_status_stmt->fetch();
            
            $item['last_status'] = $result['status'] ?? 'ok';
            $item['last_notes'] = ($item['last_status'] === 'problem') ? $result['notes'] : '';
        }
        unset($item);

        require_once __DIR__ . '/../../templates/pages/diario_bordo/checklist.php';
    }

    public function storeChecklist()
    {
        if (empty($_SESSION['run_vehicle_id']) || empty($_POST['items'])) {
            show_error_page('Erro', 'Sessão inválida ou dados do checklist não enviados.');
        }

        $vehicle_id = $_SESSION['run_vehicle_id'];
        $items = $_POST['items'];

        $this->conn->beginTransaction();
        try {
            $run_stmt = $this->conn->prepare(
                "INSERT INTO runs (vehicle_id, driver_id, start_time, status, start_km, destination) VALUES (:vehicle_id, :driver_id, NOW(), 'in_progress', NULL, NULL)"
            );
            $run_stmt->execute(['vehicle_id' => $vehicle_id, 'driver_id' => $this->user['id']]);
            $run_id = $this->conn->lastInsertId();

            $_SESSION['run_id'] = $run_id;

            $checklist_stmt = $this->conn->prepare(
                "INSERT INTO checklists (run_id, user_id, vehicle_id) VALUES (:run_id, :user_id, :vehicle_id)"
            );
            $checklist_stmt->execute(['run_id' => $run_id, 'user_id' => $this->user['id'], 'vehicle_id' => $vehicle_id]);
            $checklist_id = $this->conn->lastInsertId();

            foreach ($items as $item_id => $data) {
                $status = $data['status'] ?? 'ok';
                $notes = ($status === 'problem') ? trim($data['notes'] ?? '') : null;

                if ($status === 'problem' && empty($notes)) {
                    throw new Exception("A descrição é obrigatória para o item com problema.");
                }

                $answer_stmt = $this->conn->prepare(
                    "INSERT INTO checklist_answers (checklist_id, item_id, status, notes) VALUES (:checklist_id, :item_id, :status, :notes)"
                );
                $answer_stmt->execute([
                    'checklist_id' => $checklist_id,
                    'item_id'      => $item_id,
                    'status'       => $status,
                    'notes'        => $notes,
                ]);
            }
            
            $hasProblem = false;
            foreach ($items as $data) {
                if ($data['status'] === 'problem') {
                    $hasProblem = true;
                    break;
                }
            }

            if ($hasProblem) {
                $vehicle_status_stmt = $this->conn->prepare("UPDATE vehicles SET status = 'maintenance' WHERE id = :id");
                $vehicle_status_stmt->execute(['id' => $vehicle_id]);
            }

            $this->conn->commit();
            header('Location: ' . BASE_URL . '/runs/start');
            exit();

        } catch (Exception $e) {
            $this->conn->rollBack();
            show_error_page('Erro ao Salvar', 'Não foi possível salvar o checklist. Detalhe: ' . $e->getMessage());
        }
    }
    
    public function start()
    {
        if (empty($_SESSION['run_id']) || empty($_SESSION['run_vehicle_id'])) {
            header('Location: ' . BASE_URL . '/runs/new');
            exit();
        }

        $vehicle_id = $_SESSION['run_vehicle_id'];
        
        $stmt = $this->conn->prepare(
            "SELECT end_km FROM runs 
             WHERE vehicle_id = :vehicle_id AND status = 'completed' 
             ORDER BY end_time DESC LIMIT 1"
        );
        $stmt->execute(['vehicle_id' => $vehicle_id]);
        $last_run = $stmt->fetch();

        $last_km = $last_run['end_km'] ?? 0;

        require_once __DIR__ . '/../../templates/pages/diario_bordo/start_run.php';
    }

    public function storeStart()
    {
        if (empty($_SESSION['run_id']) || empty($_SESSION['run_vehicle_id'])) {
            show_error_page('Erro', 'Sessão inválida. Por favor, inicie o processo novamente.');
        }

        $run_id = $_SESSION['run_id'];
        $vehicle_id = $_SESSION['run_vehicle_id'];

        $start_km = filter_input(INPUT_POST, 'start_km', FILTER_VALIDATE_INT);
        $destination = trim(filter_input(INPUT_POST, 'destination', FILTER_SANITIZE_STRING));

        if ($start_km === false || $start_km < 0 || empty($destination)) {
            show_error_page('Dados Inválidos', 'O KM deve ser um número válido e o destino não pode estar vazio.');
        }
        
        $stmt = $this->conn->prepare("SELECT end_km FROM runs WHERE vehicle_id = :vehicle_id AND status = 'completed' ORDER BY end_time DESC LIMIT 1");
        $stmt->execute(['vehicle_id' => $vehicle_id]);
        $last_run = $stmt->fetch();
        $last_end_km = $last_run['end_km'] ?? 0;

        if ($start_km < $last_end_km) {
            show_error_page('KM Inválido', "O KM atual ($start_km) não pode ser menor que o KM final da última corrida ($last_end_km).");
        }

        $this->conn->beginTransaction();
        try {
            $update_run_stmt = $this->conn->prepare(
                "UPDATE runs SET start_km = :start_km, destination = :destination WHERE id = :id"
            );
            $update_run_stmt->execute([
                'start_km' => $start_km,
                'destination' => $destination,
                'id' => $run_id
            ]);

            $update_vehicle_stmt = $this->conn->prepare(
                "UPDATE vehicles SET status = 'in_use' WHERE id = :id"
            );
            $update_vehicle_stmt->execute(['id' => $vehicle_id]);

            $this->conn->commit();
            
            header('Location: ' . BASE_URL . '/runs/finish');
            exit();

        } catch (Exception $e) {
            $this->conn->rollBack();
            show_error_page('Erro ao Iniciar Corrida', 'Não foi possível salvar os dados. Detalhe: ' . $e->getMessage());
        }
    }

    public function finish()
    {
        if (empty($_SESSION['run_id'])) {
            header('Location: '. BASE_URL . '/runs/new');
            exit();
        }

        $run_id = $_SESSION['run_id'];

        $stmt = $this->conn->prepare(
            "SELECT r.start_km, r.destination, v.name as vehicle_name, v.fuel_tank_capacity_liters
             FROM runs r
             JOIN vehicles v ON r.vehicle_id = v.id
             WHERE r.id = :id"
        );
        $stmt->execute(['id' => $run_id]);
        $run = $stmt->fetch();

        if (!$run) {
            unset($_SESSION['run_id']);
            show_error_page('Erro', 'A corrida ativa não foi encontrada.');
        }

        $stations_stmt = $this->conn->query("SELECT id, name FROM gas_stations WHERE status = 'active' ORDER BY name");
        $gas_stations = $stations_stmt->fetchAll();

        $fuel_types_stmt = $this->conn->query("SELECT id, name FROM fuel_types ORDER BY name");
        $fuel_types = $fuel_types_stmt->fetchAll();

        require_once __DIR__ . '/../../templates/pages/diario_bordo/finish_run.php';
    }

    public function storeFinish()
    {
        if (empty($_SESSION['run_id']) || empty($_SESSION['run_vehicle_id'])) {
            show_error_page('Erro', 'Sessão inválida.');
        }

        $end_km = filter_input(INPUT_POST, 'end_km', FILTER_VALIDATE_INT);
        $stop_point = trim(filter_input(INPUT_POST, 'stop_point', FILTER_SANITIZE_STRING));

        $stmt = $this->conn->prepare("SELECT start_km FROM runs WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['run_id']]);
        $run = $stmt->fetch();
        
        if ($end_km === false || $end_km < $run['start_km'] || empty($stop_point)) {
            show_error_page('Dados Inválidos', "O KM final deve ser maior ou igual ao KM inicial ({$run['start_km']}). O ponto de parada é obrigatório.");
        }

        $this->conn->beginTransaction();
        try {
            $update_run_stmt = $this->conn->prepare(
                "UPDATE runs SET end_km = :end_km, stop_point = :stop_point, end_time = NOW(), status = 'completed' WHERE id = :id"
            );
            $update_run_stmt->execute(['end_km' => $end_km, 'stop_point' => $stop_point, 'id' => $_SESSION['run_id']]);

            $update_vehicle_stmt = $this->conn->prepare(
                "UPDATE vehicles SET status = 'available' WHERE id = :id AND status = 'in_use'"
            );
            $update_vehicle_stmt->execute(['id' => $_SESSION['run_vehicle_id']]);

            $this->conn->commit();

            unset($_SESSION['run_id'], $_SESSION['run_vehicle_id']);
            header('Location: ' . BASE_URL . '/dashboard?status=run_completed');
            exit();

        } catch (Exception $e) {
            $this->conn->rollBack();
            show_error_page('Erro ao Finalizar Corrida', 'Não foi possível salvar os dados. Detalhe: ' . $e->getMessage());
        }
    }

    public function ajax_get_fuels_by_station()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $station_id = $input['station_id'] ?? 0;

        if (!$station_id) {
            echo json_encode(['success' => false, 'message' => 'ID do posto não fornecido.']);
            return;
        }

        $stmt = $this->conn->prepare(
            "SELECT ft.id, ft.name, gsf.price 
             FROM gas_station_fuels gsf
             JOIN fuel_types ft ON gsf.fuel_type_id = ft.id
             WHERE gsf.gas_station_id = :id 
             ORDER BY ft.name"
        );
        $stmt->execute(['id' => $station_id]);
        $fuels = $stmt->fetchAll();

        echo json_encode(['success' => true, 'fuels' => $fuels]);
    }

    public function storeFueling()
    {
        header('Content-Type: application/json');
        
        if (empty($_SESSION['run_id']) || empty($_SESSION['run_vehicle_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão de corrida inválida.']);
            return;
        }

        try {
            $fueling_data = $_POST['fueling'];
            $liters = floatval(str_replace(',', '.', $fueling_data['liters']));

            $vehicle_stmt = $this->conn->prepare("SELECT fuel_tank_capacity_liters FROM vehicles WHERE id = :id");
            $vehicle_stmt->execute(['id' => $_SESSION['run_vehicle_id']]);
            $vehicle = $vehicle_stmt->fetch();
            $tank_capacity = $vehicle ? floatval($vehicle['fuel_tank_capacity_liters']) : 0;

            if ($tank_capacity > 0 && $liters > $tank_capacity) {
                throw new Exception("A quantidade de litros ($liters L) excede a capacidade do tanque ($tank_capacity L).");
            }
            
            $invoice_path = null;
            if (isset($_FILES['invoice']) && $_FILES['invoice']['error'] == UPLOAD_ERR_OK) {
                $target_dir = __DIR__ . "/../../public/uploads/invoices/";
                if (!is_dir($target_dir)) {
                    if (!mkdir($target_dir, 0775, true)) {
                         throw new Exception("Falha ao criar o diretório de uploads. Verifique as permissões do servidor na pasta 'public/uploads/'.");
                    }
                }
                $file_extension = strtolower(pathinfo($_FILES["invoice"]["name"], PATHINFO_EXTENSION));
                $file_name = uniqid('invoice_', true) . '.' . $file_extension;
                $target_file = $target_dir . $file_name;
                if (!move_uploaded_file($_FILES["invoice"]["tmp_name"], $target_file)) {
                   throw new Exception("Falha ao mover o arquivo. Verifique se o servidor tem permissão de escrita na pasta 'public/uploads/invoices/'.");
                }
                $invoice_path = 'uploads/invoices/' . $file_name;
            }
            
            $is_manual = empty($fueling_data['gas_station_id']);
            $fuel_type_id_to_save = $is_manual ? 
                                ($fueling_data['fuel_type_manual_id'] ?? null) : 
                                ($fueling_data['fuel_type_select_id'] ?? null);

            if (empty($fuel_type_id_to_save)) {
                throw new Exception("O tipo de combustível é obrigatório.");
            }

            $params = [
                'run_id' => $_SESSION['run_id'],
                'user_id' => $this->user['id'],
                'vehicle_id' => $_SESSION['run_vehicle_id'],
                'km' => filter_var($fueling_data['km'], FILTER_VALIDATE_INT),
                'liters' => $liters,
                'fuel_type_id' => $fuel_type_id_to_save,
                'gas_station_id' => $is_manual ? null : filter_var($fueling_data['gas_station_id'], FILTER_VALIDATE_INT),
                'gas_station_name' => $is_manual ? filter_var($fueling_data['gas_station_name'], FILTER_SANITIZE_STRING) : null,
                'total_value' => $is_manual ? filter_var(str_replace(',', '.', $fueling_data['total_value']), FILTER_VALIDATE_FLOAT) : filter_var($fueling_data['calculated_value'], FILTER_VALIDATE_FLOAT),
                'is_manual' => $is_manual ? 1 : 0,
                'invoice_path' => $invoice_path,
            ];

            $stmt = $this->conn->prepare(
                "INSERT INTO fuelings (run_id, user_id, vehicle_id, km, liters, fuel_type_id, gas_station_id, gas_station_name, total_value, is_manual, invoice_path)
                 VALUES (:run_id, :user_id, :vehicle_id, :km, :liters, :fuel_type_id, :gas_station_id, :gas_station_name, :total_value, :is_manual, :invoice_path)"
            );
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Abastecimento registrado com sucesso!']);

        } catch (Exception $e) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
    
    public function history() { echo "Histórico de corridas será implementado aqui."; }
    public function generatePdfReport() { echo "Geração de relatório em PDF será implementada aqui."; }
}

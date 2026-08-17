<?php
namespace App\Controllers;

use App\Models\ReservaRepository;

class AdminReservasController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $reservaRepo = new ReservaRepository($pdo);

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'add_reserva') {
                $chave_id = filter_var($_POST['chave_id'] ?? '', FILTER_VALIDATE_INT);
                $usuario_id = filter_var($_POST['usuario_id'] ?? '', FILTER_VALIDATE_INT);
                $data_reserva = $_POST['data_reserva'] ?? '';
                $hora_inicio = $_POST['hora_inicio'] ?? '';
                $hora_fim = $_POST['hora_fim'] ?? '';

                if (!$chave_id || !$usuario_id || empty($data_reserva) || empty($hora_inicio) || empty($hora_fim)) {
                    throw new \Exception("Todos os campos de agendamento são obrigatórios.");
                }

                // Saneamento básico e validação de data/hora
                $data_reserva = date('Y-m-d', strtotime($data_reserva));
                $hora_inicio = date('H:i:s', strtotime($hora_inicio));
                $hora_fim = date('H:i:s', strtotime($hora_fim));

                if ($hora_inicio >= $hora_fim) {
                    throw new \Exception("A hora de início deve ser menor que a hora de término.");
                }

                // Verificar conflito de horário para a mesma chave/sala na data
                $conflito = $reservaRepo->checkConflitoReserva($chave_id, $data_reserva, $hora_inicio, $hora_fim);

                if ($conflito) {
                    throw new \Exception("Conflito: A sala já está reservada por {$conflito['reservado_nome']} neste horário!");
                }

                // Inserir agendamento
                $reservaRepo->addReserva($chave_id, $usuario_id, $data_reserva, $hora_inicio, $hora_fim);

                // Obter nomes para o log
                $nomeSala = $reservaRepo->getNomeSala($chave_id);
                $nomeUser = $reservaRepo->getNomeUsuario($usuario_id);
                
                registrar_log($pdo, 'Agendamento de Sala', "Sala '$nomeSala' agendada para '$nomeUser' em $data_reserva das " . substr($hora_inicio, 0, 5) . " às " . substr($hora_fim, 0, 5));

                $message = "Sala reservada com sucesso!";
                $messageType = "success";
            } 
            
            elseif ($action === 'delete_reserva') {
                $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
                if (!$id) {
                    throw new \Exception("ID de agendamento inválido.");
                }

                // Obter informações para o log
                $info = $reservaRepo->getReservaInfo($id);

                $reservaRepo->deleteReserva($id);

                if ($info) {
                    registrar_log($pdo, 'Cancelamento de Reserva', "Reserva da sala '{$info['nome_sala']}' para '{$info['nome']}' no dia {$info['data_reserva']} foi cancelada.");
                }

                $message = "Reserva cancelada com sucesso.";
                $messageType = "success";
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $messageType = "error";
        }
}

// Carregar chaves, usuários e reservas
try {
    $chaves = $reservaRepo->getAllChaves();
    $usuarios = $reservaRepo->getAllUsuariosAtivos();
    
    // Obter reservas futuras e de hoje
    $reservas = $reservaRepo->getReservasFuturas();

    // Obter reservas passadas (histórico)
    $reservasPassadas = $reservaRepo->getReservasPassadas();
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        $this->render('admin_reservas', get_defined_vars());
    }
}

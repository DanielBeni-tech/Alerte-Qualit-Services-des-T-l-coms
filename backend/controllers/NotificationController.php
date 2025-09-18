<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';
require_once '../models/Notification.php';

class NotificationController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getUnreadCount() {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE lu = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getNotifications() {
        $query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function markAsRead($id) {
        $query = "UPDATE notifications SET lu = 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function createNotification($type, $message, $related_id = null) {
        $query = "INSERT INTO notifications (type, message, related_id) VALUES (:type, :message, :related_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":related_id", $related_id);
        return $stmt->execute();
    }
}

// Traitement des requêtes
$notification = new NotificationController();
$method = $_SERVER['REQUEST_METHOD'];

// Vérification du token
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if(!$token) {
    http_response_code(401);
    echo json_encode(array("message" => "Non autorisé"));
    exit;
}

require_once '../controllers/AuthController.php';
$auth = new AuthController();
$payload = $auth->verifyToken($token);

if(!$payload) {
    http_response_code(401);
    echo json_encode(array("message" => "Token invalide ou expiré"));
    exit;
}

switch($method) {
    case 'GET':
        if(isset($_GET['count'])) {
            $count = $notification->getUnreadCount();
            http_response_code(200);
            echo json_encode(array("count" => $count));
        } else {
            $notifications = $notification->getNotifications();
            $result = array();
            while($row = $notifications->fetch(PDO::FETCH_ASSOC)) {
                array_push($result, $row);
            }
            http_response_code(200);
            echo json_encode($result);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->id)) {
            if($notification->markAsRead($data->id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Notification marquée comme lue"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de mettre à jour la notification"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID de notification manquant"));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Méthode non autorisée"));
        break;
}
?> 
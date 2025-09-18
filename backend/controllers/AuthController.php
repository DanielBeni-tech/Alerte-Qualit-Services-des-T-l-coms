<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

class AuthController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($email, $password) {
        $query = "SELECT id, email, mot_de_passe, role FROM utilisateurs WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Comparaison directe (non chiffrée)
            if($password === $row['mot_de_passe']) {
                // Générer un token JWT
                $token = $this->generateToken($row);
                return array(
                    "success" => true,
                    "token" => $token,
                    "user" => array(
                        "id" => $row['id'],
                        "email" => $row['email'],
                        "role" => $row['role']
                    )
                );
            }
        }
        return array("success" => false, "message" => "Email ou mot de passe incorrect");
    }

    private function generateToken($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + (60 * 60 * 24) // Expire dans 24 heures
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'votre_cle_secrete', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function verifyToken($token) {
        $parts = explode('.', $token);
        if(count($parts) != 3) {
            return false;
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        
        if(!$payload || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}

// Traitement des requêtes
$auth = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];

if($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->email) && !empty($data->password)) {
        $result = $auth->login($data->email, $data->password);
        if($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(401);
            echo json_encode(array("message" => $result['message']));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données de connexion incomplètes"));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
}
?>

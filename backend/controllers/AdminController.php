<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';
require_once '../models/Plainte.php';

class AdminController {
    private $db;
    private $plainte;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->plainte = new Plainte($this->db);
    }

    public function getComplaintById($id) {
        $query = "SELECT p.*, o.nom as operateur_nom 
                FROM plaintes p
                LEFT JOIN operateurs o ON p.operateur_id = o.id
                WHERE p.id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        }
        return null;
    }

    public function getComplaintsByFilter($status = null, $operator = null, $search = null) {
        $query = "SELECT p.*, o.nom as operateur_nom 
                FROM plaintes p
                LEFT JOIN operateurs o ON p.operateur_id = o.id
                WHERE 1=1";
        
        $params = array();

        if($status) {
            $query .= " AND p.statut = :status";
            $params[':status'] = $status;
        }

        if($operator) {
            $query .= " AND p.operateur_id = :operator";
            $params[':operator'] = $operator;
        }

        if($search) {
            $query .= " AND (p.nom_plaignant LIKE :search 
                        OR p.email LIKE :search 
                        OR p.description LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt;
    }

    public function deleteComplaint($id) {
        $query = "DELETE FROM plaintes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateComplaint($id, $data) {
        $query = "UPDATE plaintes SET 
                statut = :statut,
                description = :description,
                type_plainte = :type_plainte,
                updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":statut", $data->statut);
        $stmt->bindParam(":description", $data->description);
        $stmt->bindParam(":type_plainte", $data->type_plainte);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function resolveComplaint($id, $resolution) {
        try {
            $this->db->beginTransaction();

            // Mettre à jour le statut de la plainte
            $query = "UPDATE plaintes SET 
                    statut = 'resolue',
                    resolution = :resolution,
                    date_resolution = NOW(),
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":resolution", $resolution);
            $stmt->execute();

            // Ajouter un commentaire de l'admin
            $commentQuery = "INSERT INTO commentaires 
                    (plainte_id, nom_utilisateur, email, contenu, date_commentaire, type_reaction, is_admin) 
                    VALUES 
                    (:plainte_id, 'Admin', 'alerte-qualite-service@gmail.com', :contenu, NOW(), 'neutre', 1)";

            $commentStmt = $this->db->prepare($commentQuery);
            $commentStmt->bindParam(":plainte_id", $id);
            $commentStmt->bindParam(":contenu", "Résolution : " . $resolution);
            $commentStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Ajouter une méthode pour récupérer les commentaires d'une plainte
    public function getComplaintComments($plainteId) {
        $query = "SELECT * FROM commentaires WHERE plainte_id = :plainte_id ORDER BY date_commentaire DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":plainte_id", $plainteId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Traitement des requêtes
$admin = new AdminController();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $complaint = $admin->getComplaintById($_GET['id']);
            if($complaint) {
                http_response_code(200);
                echo json_encode($complaint);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Plainte non trouvée."));
            }
        } else {
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $operator = isset($_GET['operator']) ? $_GET['operator'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;

            $stmt = $admin->getComplaintsByFilter($status, $operator, $search);
            $complaints = array();
            $complaints["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($complaints["records"], $row);
            }

            http_response_code(200);
            echo json_encode($complaints);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->id)) {
            if($admin->deleteComplaint($data->id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Plainte supprimée avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de supprimer la plainte."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID de plainte manquant."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->id)) {
            if($admin->updateComplaint($data->id, $data)) {
                http_response_code(200);
                echo json_encode(array("message" => "Plainte mise à jour avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de mettre à jour la plainte."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Données manquantes."));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->id) && !empty($data->resolution)) {
            if($admin->resolveComplaint($data->id, $data->resolution)) {
                http_response_code(200);
                echo json_encode(array("message" => "Plainte résolue avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de résoudre la plainte."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Données manquantes."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Méthode non autorisée."));
        break;
}
?> 
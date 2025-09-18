<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config/database.php';
include_once '../models/Plainte.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    $plainte = new Plainte($db);

    // Gestion des différentes méthodes HTTP
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['stats'])) {
                $stats = $plainte->getStats();
                echo json_encode(array(
                    "success" => true,
                    "stats" => $stats
                ));
            } else {
                try {
                    $stmt = $plainte->read();
                    $num = $stmt->rowCount();
                    
                    if($num > 0) {
                        $plaintes_arr = array();
                        $plaintes_arr["records"] = array();
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            extract($row);
                            $plainte_item = array(
                                "id" => $id,
                                "operateur_nom" => $operateur_nom ?? "Opérateur inconnu",
                                "nom_plaignant" => $nom_plaignant,
                                "email" => $email,
                                "telephone" => $telephone,
                                "region" => $region,
                                "ville" => $ville,
                                "type_plainte" => $type_plainte,
                                "description" => $description,
                                "date_plainte" => $date_plainte,
                                "statut" => $statut
                            );
                            array_push($plaintes_arr["records"], $plainte_item);
                        }
                        
                        http_response_code(200);
                        echo json_encode(array(
                            "success" => true,
                            "records" => $plaintes_arr["records"]
                        ));
                    } else {
                        http_response_code(200);
                        echo json_encode(array(
                            "success" => true,
                            "records" => array(),
                            "message" => "Aucune plainte trouvée."
                        ));
                    }
                } catch (Exception $e) {
                    error_log("Erreur dans la lecture des plaintes: " . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Erreur lors de la lecture des plaintes: " . $e->getMessage()
                    ));
                }
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            if (!$data) {
                throw new Exception("Données JSON invalides");
            }

            if (
                !empty($data->operateur_id) &&
                !empty($data->nom_plaignant) &&
                !empty($data->email) &&
                !empty($data->telephone) &&
                !empty($data->region) &&
                !empty($data->ville) &&
                !empty($data->type_plainte) &&
                !empty($data->description)
            ) {
                $plainte->operateur_id = $data->operateur_id;
                $plainte->nom_plaignant = $data->nom_plaignant;
                $plainte->email = $data->email;
                $plainte->telephone = $data->telephone;
                $plainte->region = $data->region;
                $plainte->ville = $data->ville;
                $plainte->type_plainte = $data->type_plainte;
                $plainte->description = $data->description;
                $plainte->date_plainte = date('Y-m-d H:i:s');
                $plainte->statut = "en_attente";

                if($plainte->create()) {
                    http_response_code(201);
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Plainte créée avec succès."
                    ));
                } else {
                    throw new Exception("Impossible de créer la plainte");
                }
            } else {
                throw new Exception("Données incomplètes");
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));
            if (!$data || !isset($data->id) || !isset($data->statut)) {
                throw new Exception("Données invalides pour la mise à jour");
            }

            $plainte->id = $data->id;
            $plainte->statut = $data->statut;

            if($plainte->updateStatut()) {
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Statut mis à jour avec succès"
                ));
            } else {
                throw new Exception("Impossible de mettre à jour le statut");
            }
            break;

        default:
            throw new Exception("Méthode non autorisée");
    }

} catch (Exception $e) {
    error_log("Erreur PlainteController: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}
?> 
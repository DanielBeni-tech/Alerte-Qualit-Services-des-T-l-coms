<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Commentaire.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    $commentaire = new Commentaire($db);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['plainte_id'])) {
                $commentaire->plainte_id = $_GET['plainte_id'];
                
                if (isset($_GET['stats'])) {
                    $stats = $commentaire->getReactionsStats();
                    echo json_encode(array(
                        "success" => true,
                        "stats" => $stats
                    ));
                } else {
                    $stmt = $commentaire->readByPlainte();
                    $num = $stmt->rowCount();
                    
                    if ($num > 0) {
                        $commentaires_arr = array();
                        $commentaires_arr["records"] = array();
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            extract($row);
                            $commentaire_item = array(
                                "id" => $id,
                                "plainte_id" => $plainte_id,
                                "nom_utilisateur" => $nom_utilisateur,
                                "contenu" => $contenu,
                                "date_commentaire" => $date_commentaire,
                                "type_reaction" => $type_reaction
                            );
                            array_push($commentaires_arr["records"], $commentaire_item);
                        }
                        
                        echo json_encode(array(
                            "success" => true,
                            "records" => $commentaires_arr["records"]
                        ));
                    } else {
                        echo json_encode(array(
                            "success" => true,
                            "records" => array(),
                            "message" => "Aucun commentaire trouvé."
                        ));
                    }
                }
            } else {
                throw new Exception("ID de plainte requis");
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            
            if (!$data) {
                throw new Exception("Données JSON invalides");
            }

            if (
                !empty($data->plainte_id) &&
                !empty($data->nom_utilisateur) &&
                !empty($data->email) &&
                !empty($data->contenu)
            ) {
                $commentaire->plainte_id = $data->plainte_id;
                $commentaire->nom_utilisateur = $data->nom_utilisateur;
                $commentaire->email = $data->email;
                $commentaire->contenu = $data->contenu;
                $commentaire->type_reaction = $data->type_reaction ?? 'neutre';

                if ($commentaire->create()) {
                    http_response_code(201);
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Commentaire ajouté avec succès."
                    ));
                } else {
                    throw new Exception("Impossible d'ajouter le commentaire");
                }
            } else {
                throw new Exception("Données incomplètes");
            }
            break;

        default:
            throw new Exception("Méthode non autorisée");
    }

} catch (Exception $e) {
    error_log("Erreur CommentaireController: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}
?> 
<?php
require_once '../config/database.php';
require_once '../models/Reaction.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    $reaction = new Reaction($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($method) {
        case 'POST':
            if ($action === 'add') {
                $data = json_decode(file_get_contents("php://input"));
                
                if (!isset($data->plainte_id) || !isset($data->email) || !isset($data->type_reaction)) {
                    throw new Exception("Données manquantes");
                }

                $reaction->plainte_id = $data->plainte_id;
                $reaction->email = $data->email;
                $reaction->type_reaction = $data->type_reaction;

                if ($reaction->addOrUpdate()) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Réaction ajoutée avec succès"
                    ]);
                } else {
                    throw new Exception("Erreur lors de l'ajout de la réaction");
                }
            }
            break;

        case 'GET':
            if ($action === 'stats') {
                if (!isset($_GET['plainte_id'])) {
                    throw new Exception("ID de la plainte manquant");
                }

                $reaction->plainte_id = $_GET['plainte_id'];
                $stats = $reaction->getStats();

                echo json_encode([
                    "status" => "success",
                    "data" => $stats
                ]);
            } else if ($action === 'check') {
                if (!isset($_GET['plainte_id']) || !isset($_GET['email'])) {
                    throw new Exception("Données manquantes");
                }

                $reaction->plainte_id = $_GET['plainte_id'];
                $reaction->email = $_GET['email'];
                $userReaction = $reaction->checkUserReaction();

                echo json_encode([
                    "status" => "success",
                    "data" => [
                        "type_reaction" => $userReaction
                    ]
                ]);
            }
            break;

        default:
            throw new Exception("Méthode non autorisée");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?> 
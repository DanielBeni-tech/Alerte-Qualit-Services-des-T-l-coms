<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    // Création de la table des commentaires si elle n'existe pas
    $query = "CREATE TABLE IF NOT EXISTS commentaires (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plainte_id INT NOT NULL,
        nom_utilisateur VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        contenu TEXT NOT NULL,
        date_commentaire DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (plainte_id) REFERENCES plaintes(id) ON DELETE CASCADE
    )";

    $stmt = $db->prepare($query);
    $stmt->execute();

    // Création de la table des réactions si elle n'existe pas
    $query = "CREATE TABLE IF NOT EXISTS reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plainte_id INT NOT NULL,
        email VARCHAR(100) NOT NULL,
        type_reaction ENUM('like', 'dislike') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (plainte_id) REFERENCES plaintes(id) ON DELETE CASCADE,
        UNIQUE KEY unique_reaction (plainte_id, email)
    )";

    $stmt = $db->prepare($query);
    $stmt->execute();

    echo "Base de données mise à jour avec succès !";
} catch (Exception $e) {
    echo "Erreur lors de la mise à jour de la base de données : " . $e->getMessage();
}
?> 
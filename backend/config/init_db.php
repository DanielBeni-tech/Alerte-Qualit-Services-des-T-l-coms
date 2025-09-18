<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS alerte_qualite_services CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE alerte_qualite_services");

    // Créer la table operateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS operateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Supprimer la table plaintes si elle existe
    $pdo->exec("DROP TABLE IF EXISTS plaintes");

    // Créer la table plaintes avec la bonne structure
    $pdo->exec("CREATE TABLE plaintes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        operateur_id INT NOT NULL,
        nom_plaignant VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        region VARCHAR(100) NOT NULL,
        ville VARCHAR(100) NOT NULL,
        type_plainte VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        date_plainte DATETIME NOT NULL,
        statut VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (operateur_id) REFERENCES operateurs(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insérer les opérateurs s'ils n'existent pas
    $operateurs = [
        ['id' => 1, 'nom' => 'MTN Cameroun'],
        ['id' => 2, 'nom' => 'Orange Cameroun'],
        ['id' => 3, 'nom' => 'Camtel'],
        ['id' => 4, 'nom' => 'Nexttel']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO operateurs (id, nom) VALUES (:id, :nom)");
    foreach ($operateurs as $operateur) {
        $stmt->execute($operateur);
    }

    echo "Base de données initialisée avec succès\n";
} catch(PDOException $e) {
    die("Erreur d'initialisation : " . $e->getMessage() . "\n");
}
?> 
<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Initialisation de la base de données</h2>";

try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer et sélectionner la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS alerte_qualite_services");
    $pdo->exec("USE alerte_qualite_services");
    echo "<p>✓ Base de données créée</p>";

    // Supprimer les tables existantes
    $pdo->exec("DROP TABLE IF EXISTS plaintes");
    $pdo->exec("DROP TABLE IF EXISTS operateurs");
    echo "<p>✓ Tables existantes supprimées</p>";

    // Créer la table operateurs
    $pdo->exec("CREATE TABLE operateurs (
        id INT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL
    )");
    echo "<p>✓ Table operateurs créée</p>";

    // Insérer les opérateurs
    $operateurs = [
        [1, 'MTN Cameroun'],
        [2, 'Orange Cameroun'],
        [3, 'Camtel'],
        [4, 'Nexttel']
    ];
    $stmt = $pdo->prepare("INSERT INTO operateurs (id, nom) VALUES (?, ?)");
    foreach ($operateurs as $op) {
        $stmt->execute($op);
    }
    echo "<p>✓ Opérateurs insérés</p>";

    // Créer la table plaintes
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
        FOREIGN KEY (operateur_id) REFERENCES operateurs(id)
    )");
    echo "<p>✓ Table plaintes créée</p>";

    echo "<h3 style='color: green;'>✓ Initialisation réussie!</h3>";
    echo "<p><a href='soumettre.html'>Retour au formulaire</a></p>";

} catch(PDOException $e) {
    echo "<h3 style='color: red;'>Erreur :</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 
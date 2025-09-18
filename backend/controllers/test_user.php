<?php
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$email = "admin@alertequalite.cm";
$passwordTest = "admin123"; // Ce que tu tapes à la connexion

$query = "SELECT id, email, mot_de_passe FROM utilisateurs WHERE email = :email";
$stmt = $conn->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Mot de passe (base) : " . $user['mot_de_passe'] . "<br>";
    echo "Mot de passe saisi : " . $passwordTest . "<br>";

    if (password_verify($passwordTest, $user['mot_de_passe'])) {
        echo "Mot de passe VALIDE.";
    } else {
        echo "Mot de passe INCORRECT.";
    }
} else {
    echo "Aucun utilisateur trouvé.";
}
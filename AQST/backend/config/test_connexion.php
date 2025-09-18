<?php
require_once './database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Connexion échouée !");
}

$email = 'admin@alertequalite.cm';
$password_saisi = 'admin123';

$query = "SELECT mot_de_passe FROM utilisateurs WHERE email = :email";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Mot de passe en base : " . $row['mot_de_passe'] . "<br>";
    echo "Mot de passe saisi : " . $password_saisi . "<br>";

    if (trim($password_saisi) === trim($row['mot_de_passe'])) {
        echo "✅ Mot de passe correct.";
    } else {
        echo "❌ Mot de passe INCORRECT.";
    }
} else {
    echo "❌ Aucun utilisateur trouvé avec cet email.";
}
?>

<?php
class Commentaire {
    private $conn;
    private $table_name = "commentaires";

    public $id;
    public $plainte_id;
    public $nom_utilisateur;
    public $email;
    public $contenu;
    public $date_commentaire;
    public $type_reaction; // 'like', 'dislike', 'neutre'

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un nouveau commentaire
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        plainte_id = :plainte_id,
                        nom_utilisateur = :nom_utilisateur,
                        email = :email,
                        contenu = :contenu,
                        date_commentaire = :date_commentaire,
                        type_reaction = :type_reaction";

            $stmt = $this->conn->prepare($query);

            // Nettoyage des données
            $this->plainte_id = htmlspecialchars(strip_tags($this->plainte_id));
            $this->nom_utilisateur = htmlspecialchars(strip_tags($this->nom_utilisateur));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->contenu = htmlspecialchars(strip_tags($this->contenu));
            $this->date_commentaire = date('Y-m-d H:i:s');
            $this->type_reaction = htmlspecialchars(strip_tags($this->type_reaction));

            // Liaison des paramètres
            $stmt->bindParam(":plainte_id", $this->plainte_id);
            $stmt->bindParam(":nom_utilisateur", $this->nom_utilisateur);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":contenu", $this->contenu);
            $stmt->bindParam(":date_commentaire", $this->date_commentaire);
            $stmt->bindParam(":type_reaction", $this->type_reaction);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Lire tous les commentaires d'une plainte
    public function readByPlainte() {
        try {
            $query = "SELECT * FROM " . $this->table_name . "
                    WHERE plainte_id = :plainte_id
                    ORDER BY date_commentaire DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":plainte_id", $this->plainte_id);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans Commentaire::readByPlainte(): " . $e->getMessage());
            throw new Exception("Erreur lors de la lecture des commentaires: " . $e->getMessage());
        }
    }

    // Obtenir les statistiques des réactions pour une plainte
    public function getReactionsStats() {
        try {
            $query = "SELECT 
                    type_reaction,
                    COUNT(*) as total
                    FROM " . $this->table_name . "
                    WHERE plainte_id = :plainte_id
                    GROUP BY type_reaction";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":plainte_id", $this->plainte_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans Commentaire::getReactionsStats(): " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        }
    }
}
?> 
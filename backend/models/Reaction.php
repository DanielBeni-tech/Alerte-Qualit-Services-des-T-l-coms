<?php
class Reaction {
    private $conn;
    private $table_name = "reactions";

    public $id;
    public $plainte_id;
    public $email;
    public $type_reaction;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ajouter ou mettre à jour une réaction
    public function addOrUpdate() {
        try {
            // Vérifier si une réaction existe déjà
            $check_query = "SELECT id, type_reaction FROM " . $this->table_name . "
                          WHERE plainte_id = :plainte_id AND email = :email";
            
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":plainte_id", $this->plainte_id);
            $check_stmt->bindParam(":email", $this->email);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                // Une réaction existe déjà, on la met à jour
                $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Si la réaction est la même, on la supprime (toggle)
                if ($row['type_reaction'] === $this->type_reaction) {
                    $delete_query = "DELETE FROM " . $this->table_name . "
                                   WHERE id = :id";
                    $delete_stmt = $this->conn->prepare($delete_query);
                    $delete_stmt->bindParam(":id", $row['id']);
                    return $delete_stmt->execute();
                }
                
                // Sinon, on met à jour la réaction
                $update_query = "UPDATE " . $this->table_name . "
                               SET type_reaction = :type_reaction
                               WHERE id = :id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":type_reaction", $this->type_reaction);
                $update_stmt->bindParam(":id", $row['id']);
                return $update_stmt->execute();
            } else {
                // Aucune réaction n'existe, on en crée une nouvelle
                $insert_query = "INSERT INTO " . $this->table_name . "
                               (plainte_id, email, type_reaction)
                               VALUES (:plainte_id, :email, :type_reaction)";
                
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(":plainte_id", $this->plainte_id);
                $insert_stmt->bindParam(":email", $this->email);
                $insert_stmt->bindParam(":type_reaction", $this->type_reaction);
                
                return $insert_stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Erreur SQL dans Reaction::addOrUpdate(): " . $e->getMessage());
            throw new Exception("Erreur lors de l'ajout de la réaction: " . $e->getMessage());
        }
    }

    // Obtenir les statistiques des réactions pour une plainte
    public function getStats() {
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
            error_log("Erreur SQL dans Reaction::getStats(): " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        }
    }

    // Vérifier si un utilisateur a déjà réagi
    public function checkUserReaction() {
        try {
            $query = "SELECT type_reaction FROM " . $this->table_name . "
                    WHERE plainte_id = :plainte_id AND email = :email";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":plainte_id", $this->plainte_id);
            $stmt->bindParam(":email", $this->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['type_reaction'];
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans Reaction::checkUserReaction(): " . $e->getMessage());
            throw new Exception("Erreur lors de la vérification de la réaction: " . $e->getMessage());
        }
    }
}
?> 
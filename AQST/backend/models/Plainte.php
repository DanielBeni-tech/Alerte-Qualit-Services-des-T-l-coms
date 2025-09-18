<?php
class Plainte {
    private $conn;
    private $table_name = "plaintes";

    public $id;
    public $operateur_id;
    public $nom_plaignant;
    public $email;
    public $telephone;
    public $region;
    public $ville;
    public $type_plainte;
    public $description;
    public $date_plainte;
    public $statut;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function validateData() {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format d'email invalide");
        }

        if (!preg_match("/^[0-9]{9,10}$/", $this->telephone)) {
            throw new Exception("Format de téléphone invalide");
        }

        if (strlen($this->description) < 10) {
            throw new Exception("La description doit contenir au moins 10 caractères");
        }

        return true;
    }

    // Créer une nouvelle plainte
    public function create() {
        try {
            $this->validateData();

            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        operateur_id = :operateur_id,
                        nom_plaignant = :nom_plaignant,
                        email = :email,
                        telephone = :telephone,
                        region = :region,
                        ville = :ville,
                        type_plainte = :type_plainte,
                        description = :description,
                        date_plainte = :date_plainte,
                        statut = :statut";

            $stmt = $this->conn->prepare($query);

            // Nettoyage des données
            $this->operateur_id = htmlspecialchars(strip_tags($this->operateur_id));
            $this->nom_plaignant = htmlspecialchars(strip_tags($this->nom_plaignant));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->telephone = htmlspecialchars(strip_tags($this->telephone));
            $this->region = htmlspecialchars(strip_tags($this->region));
            $this->ville = htmlspecialchars(strip_tags($this->ville));
            $this->type_plainte = htmlspecialchars(strip_tags($this->type_plainte));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->date_plainte = htmlspecialchars(strip_tags($this->date_plainte));
            $this->statut = htmlspecialchars(strip_tags($this->statut));

            // Liaison des paramètres
            $stmt->bindParam(":operateur_id", $this->operateur_id);
            $stmt->bindParam(":nom_plaignant", $this->nom_plaignant);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":telephone", $this->telephone);
            $stmt->bindParam(":region", $this->region);
            $stmt->bindParam(":ville", $this->ville);
            $stmt->bindParam(":type_plainte", $this->type_plainte);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":date_plainte", $this->date_plainte);
            $stmt->bindParam(":statut", $this->statut);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Lire toutes les plaintes
    public function read() {
        try {
            $query = "SELECT p.*, COALESCE(o.nom, 'Opérateur inconnu') as operateur_nom 
                    FROM " . $this->table_name . " p
                    LEFT JOIN operateurs o ON p.operateur_id = o.id
                    ORDER BY p.date_plainte DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans Plainte::read(): " . $e->getMessage());
            throw new Exception("Erreur lors de la lecture des plaintes: " . $e->getMessage());
        }
    }

    // Lire une plainte par ID
    public function readOne() {
        $query = "SELECT p.*, o.nom as operateur_nom 
                FROM " . $this->table_name . " p
                LEFT JOIN operateurs o ON p.operateur_id = o.id
                WHERE p.id = :id
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    // Mettre à jour le statut d'une plainte
    public function updateStatut() {
        $query = "UPDATE " . $this->table_name . "
                SET statut = :statut
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function getStats() {
        $query = "SELECT 
            COUNT(*) as total_plaintes,
            COUNT(DISTINCT email) as total_utilisateurs,
            COUNT(DISTINCT region) as total_regions
            FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?> 
<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $db = "alerte_qualite_services";
    public $conn; 

    public function getConnection() {
    $this->conn = null; 

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
            return $this->conn;
        } catch(PDOException $exception) {
            throw new Exception("Erreur de connexion à la base de données : " . $exception->getMessage());
        }
    }
}
    ?>
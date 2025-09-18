<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $type;
    public $message;
    public $related_id;
    public $lu;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (type, message, related_id)
                VALUES
                (:type, :message, :related_id)";

        $stmt = $this->conn->prepare($query);

        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->related_id = htmlspecialchars(strip_tags($this->related_id));

        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":related_id", $this->related_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . "
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function markAsRead() {
        $query = "UPDATE " . $this->table_name . "
                SET lu = 1
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getUnreadCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                WHERE lu = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['count'];
    }
}
?> 
<?php
// app/models/Model.php
require_once __DIR__ . '/../../config/database.php';

class Model {
    protected $db;
    protected $table;

    public function __construct($table) {
        $this->table = $table;
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE activo = 1 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        
        $query = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $placeholders . ")";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $set = "";
        foreach($data as $key => $value) {
            $set .= $key . " = :" . $key . ", ";
        }
        $set = rtrim($set, ", ");

        $data['id'] = $id;
        $query = "UPDATE " . $this->table . " SET " . $set . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($data);
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET activo = 0 WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>
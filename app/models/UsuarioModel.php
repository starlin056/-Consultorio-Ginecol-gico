<?php
// app/models/UsuarioModel.php
require_once 'Model.php';

class UsuarioModel extends Model {
    public function __construct() {
        parent::__construct('usuarios');
    }

    public function getByEmail($email) {
        $query = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($userData) {
        // ✅ CORREGIDO: No hacer hash doble
        return $this->create($userData);
    }

    public function updateUser($userId, $userData) {
        return $this->update($userId, $userData);
    }

    public function getAllWithConsultorio() {
        $query = "SELECT u.*, c.nombre as consultorio_nombre 
                 FROM usuarios u 
                 LEFT JOIN consultorios c ON u.consultorio_id = c.id 
                 WHERE u.activo = 1 
                 ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($userId) {
        $query = "SELECT u.*, c.nombre as consultorio_nombre 
                 FROM usuarios u 
                 LEFT JOIN consultorios c ON u.consultorio_id = c.id 
                 WHERE u.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function countMedicos() {
    $query = "SELECT COUNT(*) as total FROM usuarios 
              WHERE rol = 'medico' 
              AND activo = 1";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
}
?>
<?php
// app/models/PacienteModel.php
require_once 'Model.php';

class PacienteModel extends Model {
    public function __construct() {
        parent::__construct('pacientes');
    }

    public function getByCedula($cedula) {
        $query = "SELECT * FROM pacientes WHERE cedula = ? AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search($term) {
        $query = "SELECT * FROM pacientes WHERE 
                 (nombre LIKE ? OR cedula LIKE ? OR email LIKE ?) AND activo = 1 
                 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $searchTerm = "%$term%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistorial($paciente_id) {
        $query = "SELECT c.*, u.nombre as medico_nombre 
                 FROM consultas c 
                 LEFT JOIN usuarios u ON c.usuario_id = u.id 
                 WHERE c.paciente_id = ? AND c.activo = 1
                 ORDER BY c.fecha_consulta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$paciente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function countPacientesNuevos($fechaInicio, $fechaFin) {
        $query = "SELECT COUNT(*) as total FROM pacientes 
                  WHERE DATE(created_at) BETWEEN ? AND ? 
                  AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>
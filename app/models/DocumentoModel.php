<?php
// app/models/DocumentoModel.php
class DocumentoModel extends Model {
    public function __construct() {
        parent::__construct('documentos');
    }

    public function getByPaciente($paciente_id) {
        $query = "SELECT * FROM documentos WHERE paciente_id = ? AND activo = 1 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$paciente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByConsulta($consulta_id) {
        $query = "SELECT * FROM documentos WHERE consulta_id = ? AND activo = 1 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$consulta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   

    // En tu DocumentoModel, agrega este método si no existe:
public function countByConsulta($consulta_id) {
    $query = "SELECT COUNT(*) as total FROM documentos WHERE consulta_id = ? AND activo = 1";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$consulta_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
}
?>
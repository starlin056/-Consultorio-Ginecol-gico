<?php
// app/models/ConsultaModel.php
require_once 'Model.php';

class ConsultaModel extends Model {
    public function __construct() {
        parent::__construct('consultas');
    }

    public function getAllWithPacientes() {
        $query = "SELECT c.*, p.nombre as paciente_nombre, p.cedula 
                 FROM consultas c 
                 LEFT JOIN pacientes p ON c.paciente_id = p.id 
                 WHERE c.activo = 1 
                 ORDER BY c.fecha_consulta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConsultasHoy() {
        $query = "SELECT c.*, p.nombre as paciente_nombre, p.cedula 
                 FROM consultas c 
                 LEFT JOIN pacientes p ON c.paciente_id = p.id 
                 WHERE DATE(c.fecha_consulta) = CURDATE() AND c.activo = 1 
                 ORDER BY c.fecha_consulta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConsultasEsteMes() {
        $query = "SELECT COUNT(*) as total 
                 FROM consultas 
                 WHERE YEAR(fecha_consulta) = YEAR(CURDATE()) 
                 AND MONTH(fecha_consulta) = MONTH(CURDATE()) 
                 AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getProximasCitas() {
        $query = "SELECT c.*, p.nombre as paciente_nombre, p.cedula 
                 FROM consultas c 
                 LEFT JOIN pacientes p ON c.paciente_id = p.id 
                 WHERE c.proxima_visita >= CURDATE() AND c.activo = 1 
                 ORDER BY c.proxima_visita ASC 
                 LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAndGetId($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        
        $query = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $placeholders . ")";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getByIdWithPaciente($id) {
        $query = "SELECT c.*, p.nombre as paciente_nombre, p.cedula, p.fecha_nacimiento, 
                         p.telefono, p.email, p.direccion, p.alergias, p.antecedentes,
                         u.nombre as medico_nombre, u.exequatur as medico_exequatur
                 FROM consultas c 
                 LEFT JOIN pacientes p ON c.paciente_id = p.id 
                 LEFT JOIN usuarios u ON c.usuario_id = u.id 
                 WHERE c.id = ? AND c.activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getConsultasDelDia($fecha = null) {
        if ($fecha === null) {
            $fecha = date('Y-m-d');
        }

        $sql = "SELECT c.*, p.nombre AS paciente_nombre 
                FROM consultas c
                INNER JOIN pacientes p ON p.id = c.paciente_id
                WHERE DATE(c.fecha_consulta) = :fecha
                ORDER BY c.fecha_consulta ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countConsultasPorRango($fechaInicio, $fechaFin) {
        $query = "SELECT COUNT(*) as total FROM consultas 
                  WHERE DATE(fecha_consulta) BETWEEN ? AND ? 
                  AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function countConsultasHoy() {
        $query = "SELECT COUNT(*) as total FROM consultas 
                  WHERE DATE(fecha_consulta) = CURDATE() 
                  AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getConsultasAgrupadasPorMes($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    DATE_FORMAT(fecha_consulta, '%Y-%m') as mes, 
                    COUNT(*) as total 
                  FROM consultas 
                  WHERE DATE(fecha_consulta) BETWEEN ? AND ? 
                  AND activo = 1
                  GROUP BY mes 
                  ORDER BY mes";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistribucionDiagnosticos($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    cie10_codigo,
                    COUNT(*) as total 
                  FROM consultas 
                  WHERE DATE(fecha_consulta) BETWEEN ? AND ? 
                  AND activo = 1
                  AND cie10_codigo IS NOT NULL
                  AND cie10_codigo != ''
                  GROUP BY cie10_codigo 
                  ORDER BY total DESC 
                  LIMIT 8";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopPacientes($fechaInicio, $fechaFin, $limite = 5) {
        $limite = (int)$limite;
        $query = "SELECT 
                    p.nombre as paciente_nombre,
                    COUNT(c.id) as total_consultas,
                    MAX(c.fecha_consulta) as ultima_consulta
                  FROM consultas c
                  INNER JOIN pacientes p ON c.paciente_id = p.id
                  WHERE DATE(c.fecha_consulta) BETWEEN ? AND ? 
                  AND c.activo = 1
                  GROUP BY p.id, p.nombre
                  ORDER BY total_consultas DESC 
                  LIMIT " . $limite;
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasPorMedico($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    u.nombre as medico_nombre,
                    COUNT(c.id) as total_consultas,
                    COUNT(DISTINCT c.paciente_id) as pacientes_unicos
                  FROM consultas c
                  LEFT JOIN usuarios u ON c.usuario_id = u.id
                  WHERE DATE(c.fecha_consulta) BETWEEN ? AND ? 
                  AND c.activo = 1
                  GROUP BY u.id, u.nombre
                  ORDER BY total_consultas DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTendenciasMensuales($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    DATE_FORMAT(fecha_consulta, '%Y-%m') as mes,
                    COUNT(*) as total_consultas,
                    COUNT(DISTINCT paciente_id) as pacientes_unicos
                  FROM consultas 
                  WHERE DATE(fecha_consulta) BETWEEN ? AND ? 
                  AND activo = 1
                  GROUP BY mes 
                  ORDER BY mes";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
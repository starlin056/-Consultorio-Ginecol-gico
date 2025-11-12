<?php
// app/models/RecetaModel.php
require_once 'Model.php';

class RecetaModel extends Model {
    public function __construct() {
        parent::__construct('recetas');
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

    public function createMedicamento($data) {
        $query = "INSERT INTO receta_medicamentos (receta_id, medicamento, dosis, frecuencia, duracion, instrucciones, tipo_item) 
                  VALUES (:receta_id, :medicamento, :dosis, :frecuencia, :duracion, :instrucciones, :tipo_item)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($data);
    }

    public function getByIdWithDetails($id) {
        $query = "SELECT 
                    r.*,
                    c.nombre as consultorio_nombre,
                    c.rnc as consultorio_rnc,
                    c.direccion as consultorio_direccion,
                    c.telefono as consultorio_telefono,
                    c.logo as consultorio_logo,
                    c.medico_exequatur as medico_exequatur,
                    p.nombre as paciente_nombre,
                    p.cedula,
                    p.fecha_nacimiento
                 FROM recetas r
                 LEFT JOIN consultorios c ON r.consultorio_id = c.id
                 LEFT JOIN usuarios u ON r.medico_id = u.id
                 LEFT JOIN pacientes p ON r.paciente_id = p.id
                 WHERE r.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMedicamentosByReceta($receta_id) {
        $query = "SELECT * FROM receta_medicamentos WHERE receta_id = ? ORDER BY id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$receta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByConsulta($consulta_id) {
        $query = "SELECT * FROM recetas WHERE consulta_id = ? AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$consulta_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByPaciente($paciente_id) {
        $query = "SELECT r.*, u.nombre as medico_nombre, c.fecha_consulta
                 FROM recetas r
                 LEFT JOIN usuarios u ON r.medico_id = u.id
                 LEFT JOIN consultas c ON r.consulta_id = c.id
                 WHERE r.paciente_id = ? AND r.activo = 1
                 ORDER BY r.fecha_emision DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$paciente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithPagination($offset = 0, $limit = 10, $filters = []) {
        $query = "SELECT 
                r.*,
                u.nombre as medico_nombre,
                p.nombre as paciente_nombre,
                p.cedula
             FROM recetas r
             LEFT JOIN usuarios u ON r.medico_id = u.id
             LEFT JOIN pacientes p ON r.paciente_id = p.id
             WHERE r.activo = 1";

        $params = [];

        // Aplicar filtros
        if (!empty($filters['numero_receta'])) {
            $query .= " AND r.numero_receta LIKE ?";
            $params[] = '%' . $filters['numero_receta'] . '%';
        }

        if (!empty($filters['paciente'])) {
            $query .= " AND p.nombre LIKE ?";
            $params[] = '%' . $filters['paciente'] . '%';
        }

        if (!empty($filters['medico'])) {
            $query .= " AND u.nombre LIKE ?";
            $params[] = '%' . $filters['medico'] . '%';
        }

        if (!empty($filters['fecha_desde'])) {
            $query .= " AND r.fecha_emision >= ?";
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $query .= " AND r.fecha_emision <= ?";
            $params[] = $filters['fecha_hasta'];
        }

        // Ordenar y limitar (sin parámetros preparados para LIMIT/OFFSET)
        $query .= " ORDER BY r.fecha_emision DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($filters = []) {
        $query = "SELECT COUNT(*) as total 
             FROM recetas r
             LEFT JOIN usuarios u ON r.medico_id = u.id
             LEFT JOIN pacientes p ON r.paciente_id = p.id
             WHERE r.activo = 1";

        $params = [];

        // Aplicar filtros
        if (!empty($filters['numero_receta'])) {
            $query .= " AND r.numero_receta LIKE ?";
            $params[] = '%' . $filters['numero_receta'] . '%';
        }

        if (!empty($filters['paciente'])) {
            $query .= " AND p.nombre LIKE ?";
            $params[] = '%' . $filters['paciente'] . '%';
        }

        if (!empty($filters['medico'])) {
            $query .= " AND u.nombre LIKE ?";
            $params[] = '%' . $filters['medico'] . '%';
        }

        if (!empty($filters['fecha_desde'])) {
            $query .= " AND r.fecha_emision >= ?";
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $query .= " AND r.fecha_emision <= ?";
            $params[] = $filters['fecha_hasta'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getCountHoy() {
        $query = "SELECT COUNT(*) as total FROM recetas 
              WHERE DATE(fecha_emision) = CURDATE() AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getCountEstaSemana() {
        $query = "SELECT COUNT(*) as total FROM recetas 
              WHERE YEARWEEK(fecha_emision) = YEARWEEK(CURDATE()) AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getCountEsteMes() {
        $query = "SELECT COUNT(*) as total FROM recetas 
              WHERE YEAR(fecha_emision) = YEAR(CURDATE()) 
              AND MONTH(fecha_emision) = MONTH(CURDATE()) 
              AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function crearRapidaDesdeConsulta($consulta_id, $paciente_id, $data) {
        $stmt = $this->db->prepare("INSERT INTO recetas (consulta_id, paciente_id, fecha_emision, activo) VALUES (?, ?, NOW(), 1)");
        $stmt->execute([$consulta_id, $paciente_id]);
        $receta_id = $this->db->lastInsertId();

        $stmt2 = $this->db->prepare("INSERT INTO receta_medicamentos (receta_id, medicamento, dosis, frecuencia, duracion, instrucciones) 
                                 VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->execute([
            $receta_id,
            $data['medicamento'],
            $data['dosis'],
            $data['frecuencia'],
            $data['duracion'],
            $data['instrucciones'] ?? ''
        ]);

        return $receta_id;
    }

    public function countRecetasPorRango($fechaInicio, $fechaFin) {
        $query = "SELECT COUNT(*) as total FROM recetas 
                  WHERE DATE(created_at) BETWEEN ? AND ? 
                  AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getRecetasPorTipo($fechaInicio, $fechaFin) {
        $query = "SELECT 
                tipo_receta,
                COUNT(*) as total 
              FROM recetas 
              WHERE DATE(created_at) BETWEEN ? AND ? 
              AND activo = 1
              GROUP BY tipo_receta";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
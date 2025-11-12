<?php
// app/models/ConsultorioModel.php
class ConsultorioModel extends Model {
    public function __construct() {
        parent::__construct('consultorios');
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLogo($id, $logoPath) {
        $query = "UPDATE " . $this->table . " SET logo = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$logoPath, $id]);
    }

    // Método para actualizar todos los campos
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
}
?>
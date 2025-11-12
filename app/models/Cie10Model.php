<?php
// app/models/Cie10Model.php
class Cie10Model extends Model {
    public function __construct() {
        parent::__construct('cie10_codigos');
    }

    public function buscarPorCodigoODescripcion($termino) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE (codigo LIKE ? OR descripcion LIKE ?) 
                 AND activo = 1 
                 ORDER BY codigo 
                 LIMIT 50";
        $stmt = $this->db->prepare($query);
        $terminoBusqueda = '%' . $termino . '%';
        $stmt->execute([$terminoBusqueda, $terminoBusqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCodigo($codigo) {
        $query = "SELECT * FROM " . $this->table . " WHERE codigo = ? AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCategorias() {
        $query = "SELECT DISTINCT categoria FROM " . $this->table . " WHERE categoria IS NOT NULL AND activo = 1 ORDER BY categoria";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getByCategoria($categoria) {
        $query = "SELECT * FROM " . $this->table . " WHERE categoria = ? AND activo = 1 ORDER BY codigo";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$categoria]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
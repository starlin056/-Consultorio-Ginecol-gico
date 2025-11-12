<?php
// app/controllers/ApiController.php
class ApiController
{
    private $cie10Model;

    public function __construct()
    {
        require_once __DIR__ . '/../models/Cie10Model.php';
        $this->cie10Model = new Cie10Model();
    }

    public function buscarCIE10()
    {
        header('Content-Type: application/json');
        
        $termino = $_GET['q'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        
        if (!empty($categoria)) {
            $resultados = $this->cie10Model->getByCategoria($categoria);
        } else {
            $resultados = $this->cie10Model->buscarPorCodigoODescripcion($termino);
        }
        
        echo json_encode($resultados);
    }

    public function getCategoriasCIE10()
    {
        header('Content-Type: application/json');
        $categorias = $this->cie10Model->getAllCategorias();
        echo json_encode($categorias);
    }
}
?>
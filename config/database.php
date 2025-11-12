<?php
date_default_timezone_set('America/Santo_Domingo');

class Database {
    private $host = "localhost";
    private $db_name = "u548411484_consultorio";
    private $username = "u548411484_psurena13";
    private $password = "Lorent07@@";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            error_log("Error de conexión BD: " . $exception->getMessage());
            // En producción, mostrar mensaje genérico
            die("Error de conexión a la base de datos. Por favor, intente más tarde.");
        }
        return $this->conn;
    }
}
?>
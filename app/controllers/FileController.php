<?php
// app/controllers/FileController.php
class FileController {
    
    public function serve($filePath) {
        // Ruta base de uploads (fuera de public)
        $basePath = __DIR__ . '/../../uploads/';
        $fullPath = $basePath . $filePath;
        
        // Log para debugging
        error_log("FileController - Solicitado: $filePath");
        error_log("FileController - Ruta completa: $fullPath");
        
        // Verificar seguridad
        if (!$this->isSafePath($basePath, $fullPath)) {
            error_log("FileController - Ruta insegura: $filePath");
            http_response_code(403);
            die('Acceso denegado');
        }
        
        // Verificar que el archivo existe
        if (!file_exists($fullPath)) {
            error_log("FileController - Archivo no existe: $fullPath");
            http_response_code(404);
            die('Archivo no encontrado');
        }
        
        // Determinar tipo MIME
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'webp' => 'image/webp'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        } else {
            header('Content-Type: application/octet-stream');
        }
        
        // Headers de seguridad y cache
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: *');
        
        // Limpiar buffer de salida y enviar archivo
        if (ob_get_level()) ob_end_clean();
        readfile($fullPath);
        exit;
    }
    
    private function isSafePath($basePath, $fullPath) {
        // Prevenir directory traversal
        $realBase = realpath($basePath);
        $realPath = realpath($fullPath);
        
        if ($realPath === false || strpos($realPath, $realBase) !== 0) {
            return false;
        }
        
        return true;
    }
}
?>
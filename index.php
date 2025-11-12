<?php
// index.php - Configurado para easyturnos.com
session_start();

// Configuración para producción
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');

// Configuración básica para tu dominio
define('BASE_URL', 'https://easyturnos.com');
define('ROOT_PATH', __DIR__);

// Función para cargar clases automáticamente
function autoLoadClass($className) {
    $paths = [
        '/app/controllers/',
        '/app/models/',
        '/config/'
    ];

    foreach ($paths as $path) {
        $file = ROOT_PATH . $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
}

spl_autoload_register('autoLoadClass');

// Manejar CORS
header('Access-Control-Allow-Origin: ' . BASE_URL);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$path = explode('?', $requestUri)[0];

// Enrutamiento principal
try {
    switch (true) {
        case $path === '/' || $path === '':
        case $path === '/login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController = new AuthController();
                $authController->handleLogin();
            } else {
                $authController = new AuthController();
                $authController->login();
            }
            break;

        case $path === '/dashboard':
            $dashboardController = new DashboardController();
            $dashboardController->index();
            break;

        case $path === '/pacientes':
            $pacienteController = new PacienteController();
            $pacienteController->index();
            break;

        case $path === '/pacientes/crear':
            $pacienteController = new PacienteController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $pacienteController->crear();
            } else {
                $pacienteController->renderForm();
            }
            break;

        case preg_match('/^\/pacientes\/ver\/(\d+)$/', $path, $matches):
            $pacienteController = new PacienteController();
            $pacienteController->ver($matches[1]);
            break;

        case $path === '/pacientes/buscar':
            $pacienteController = new PacienteController();
            $pacienteController->buscar();
            break;

        case $path === '/consultas':
            $consultaController = new ConsultaController();
            $consultaController->index();
            break;

        case $path === '/consultas/nueva':
            $consultaController = new ConsultaController();
            $consultaController->renderForm();
            break;

        case $path === '/consultas/crear':
            $consultaController = new ConsultaController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaController->crear();
            }
            break;

        case preg_match('/^\/consultas\/ver\/(\d+)$/', $path, $matches):
            $consultaController = new ConsultaController();
            $consultaController->ver($matches[1]);
            break;

        // Rutas de recetas
        case $path === '/ajustes-receta':
            $ajusteRecetaController = new AjusteRecetaController();
            $ajusteRecetaController->index();
            break;

        case $path === '/ajustes-receta/guardar':
            $ajusteRecetaController = new AjusteRecetaController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ajusteRecetaController->guardar();
            }
            break;

        case preg_match('/^\/recetas\/modal-crear\/(\d+)$/', $path, $matches):
            $recetaController = new RecetaController();
            $recetaController->modalCrear($matches[1]);
            break;

        case preg_match('/^\/recetas\/modal-ver\/(\d+)$/', $path, $matches):
            $recetaController = new RecetaController();
            $recetaController->modalVer($matches[1]);
            break;

        case preg_match('/^\/recetas\/imprimir\/(\d+)$/', $path, $matches):
            $recetaController = new RecetaController();
            $recetaController->imprimir($matches[1]);
            break;

        case $path === '/recetas':
            $recetaController = new RecetaController();
            $recetaController->index();
            break;

        case preg_match('/^\/recetas\/crear\/(\d+)$/', $path, $matches):
            $recetaController = new RecetaController();
            $recetaController->crear($matches[1]);
            break;

        case preg_match('/^\/recetas\/ver\/(\d+)$/', $path, $matches):
            $recetaController = new RecetaController();
            $recetaController->ver($matches[1]);
            break;

        // Rutas de usuarios
        case $path === '/usuarios':
            $usuarioController = new UsuarioController();
            $usuarioController->index();
            break;

        case $path === '/usuarios/crear':
            $usuarioController = new UsuarioController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $usuarioController->crear();
            } else {
                $usuarioController->renderForm();
            }
            break;

        case preg_match('/^\/usuarios\/editar\/(\d+)$/', $path, $matches):
            $usuarioController = new UsuarioController();
            $usuarioController->editar($matches[1]);
            break;

        case preg_match('/^\/usuarios\/actualizar\/(\d+)$/', $path, $matches):
            $usuarioController = new UsuarioController();
            $usuarioController->actualizar($matches[1]);
            break;

        case $path === '/reportes':
            $reporteController = new ReporteController();
            $reporteController->index();
            break;

        case $path === '/logout':
            $authController = new AuthController();
            $authController->logout();
            break;

        // Rutas API para CIE-10
        case $path === '/api/cie10/buscar':
            $apiController = new ApiController();
            $apiController->buscarCIE10();
            break;

        case $path === '/api/cie10/categorias':
            $apiController = new ApiController();
            $apiController->getCategoriasCIE10();
            break;

        // Ruta para archivos
       case preg_match('/^\/files\/(.+)$/', $path, $matches):
    require_once 'app/controllers/FileController.php';
    $fileController = new FileController();
    $fileController->serve($matches[1]);
    break;

        default:
            // Servir archivos estáticos
            if (strpos($path, '/assets/') === 0 || strpos($path, '/uploads/') === 0) {
                $filePath = ROOT_PATH . '/public' . $path;
                if (file_exists($filePath)) {
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'css' => 'text/css',
                        'js' => 'application/javascript',
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml',
                        'pdf' => 'application/pdf',
                        'woff' => 'font/woff',
                        'woff2' => 'font/woff2',
                        'ttf' => 'font/ttf'
                    ];

                    if (isset($mimeTypes[$extension])) {
                        header('Content-Type: ' . $mimeTypes[$extension]);
                    }
                    readfile($filePath);
                    exit;
                }
            }

            // Página no encontrada
            http_response_code(404);
            echo "Página no encontrada";
            break;
    }
} catch (Exception $e) {
    error_log("Error en enrutamiento: " . $e->getMessage());
    http_response_code(500);
    
    if (isset($_SESSION['usuario'])) {
        echo "Ha ocurrido un error inesperado. Por favor, intente más tarde.";
    } else {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
?>
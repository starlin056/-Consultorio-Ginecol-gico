<?php
class AjusteRecetaController
{
    private $consultorioModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/ConsultorioModel.php';
        $this->consultorioModel = new ConsultorioModel();
        $this->checkAuth();
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index()
    {
        $consultorio = $this->consultorioModel->getById($_SESSION['usuario']['consultorio_id']);

        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1><i class="fas fa-cog"></i> Ajustes de Receta Médica</h1>
                <a href="' . BASE_URL . '/recetas" class="btn" style="background: #718096; color: white;">
                    <i class="fas fa-arrow-left"></i> Volver a Recetas
                </a>
            </div>

            <form method="POST" action="' . BASE_URL . '/ajustes-receta/guardar" enctype="multipart/form-data">
                <div class="grid grid-2">
                    <!-- Información del Consultorio -->
                    <div class="card">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-hospital"></i> Información del Consultorio
                        </h3>
                        <div class="form-group">
                            <label class="form-label">Nombre del Consultorio *</label>
                            <input type="text" name="nombre" class="form-control" required 
                                   value="' . htmlspecialchars($consultorio['nombre'] ?? '') . '">
                        </div>
                        <div class="form-group">
                            <label class="form-label">RNC</label>
                            <input type="text" name="rnc" class="form-control" 
                                   value="' . htmlspecialchars($consultorio['rnc'] ?? '') . '">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" 
                                   value="' . htmlspecialchars($consultorio['telefono'] ?? '') . '">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="form-control" rows="3">' . htmlspecialchars($consultorio['direccion'] ?? '') . '</textarea>
                        </div>
                    </div>

                    <!-- Logo y Configuración -->
                    <div class="card">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-image"></i> Logo del Consultorio
                        </h3>
                        ' . $this->renderLogoSection($consultorio) . '
                        
                        <div class="form-group">
                            <label class="form-label">Texto del Pie de Página</label>
                            <textarea name="pie_pagina" class="form-control" rows="3" 
                                      placeholder="Texto que aparecerá al final de la receta...">' . htmlspecialchars($consultorio['pie_pagina'] ?? '') . '</textarea>
                        </div>
                    </div>
                </div>

                <!-- Información del Médico por Defecto -->
                <div class="card">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">
                        <i class="fas fa-user-md"></i> Información del Médico Principal
                    </h3>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre del Médico *</label>
                            <input type="text" name="medico_nombre" class="form-control" required 
                                   value="' . htmlspecialchars($consultorio['medico_nombre'] ?? $_SESSION['usuario']['nombre'] ?? '') . '">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Exequatur *</label>
                            <input type="text" name="medico_exequatur" class="form-control" required 
                                   value="' . htmlspecialchars($consultorio['medico_exequatur'] ?? '') . '" 
                                   placeholder="Número de colegiado médico">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Especialidad</label>
                            <input type="text" name="medico_especialidad" class="form-control" 
                                   value="' . htmlspecialchars($consultorio['medico_especialidad'] ?? 'Ginecología') . '">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                    <a href="' . BASE_URL . '/recetas" class="btn btn-lg" style="background: #718096; color: white;">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <script>
        function previewLogo(input) {
            const preview = document.getElementById("logoPreview");
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = \'<img src="\' + e.target.result + \'" style="max-width: 200px; max-height: 100px; border: 2px solid #e2e8f0; border-radius: 0.5rem;">\';
                }
                reader.readAsDataURL(file);
            }
        }

        function eliminarLogo() {
            if (confirm("¿Está seguro de que desea eliminar el logo?")) {
                document.getElementById("eliminar_logo").value = "1";
                document.getElementById("logoPreview").innerHTML = \'<div style="color: #718096; text-align: center; padding: 2rem;"><i class="fas fa-image" style="font-size: 3rem;"></i><p>No hay logo</p></div>\';
                document.querySelector(\'[name="logo"]\').value = "";
            }
        }
        </script>
        ';

        $this->renderWithLayout($content);
    }

   private function renderLogoSection($consultorio)
{
    $logoHtml = '';

    if (!empty($consultorio['logo'])) {
        // ✅ CAMBIO: Usar FileController en lugar de acceso directo
        $logoHtml = '
            <div id="logoPreview" style="margin-bottom: 1rem;">
                <img src="' . BASE_URL . '/files/logos/' . $consultorio['logo'] . '" style="max-width: 200px; max-height: 100px; border: 2px solid #e2e8f0; border-radius: 0.5rem;">
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarLogo()">
                <i class="fas fa-trash"></i> Eliminar Logo
            </button>
            <input type="hidden" name="eliminar_logo" id="eliminar_logo" value="0">
        ';
    } else {
        $logoHtml = '
            <div id="logoPreview" style="margin-bottom: 1rem;">
                <div style="color: #718096; text-align: center; padding: 2rem; border: 2px dashed #e2e8f0; border-radius: 0.5rem;">
                    <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No hay logo cargado</p>
                </div>
            </div>
        ';
    }

    return '
    <div class="form-group">
        <label class="form-label">Logo del Consultorio</label>
        <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewLogo(this)">
        <small class="text-muted">Recomendado: 200x100px, formato PNG o JPG</small>
    </div>
    ' . $logoHtml;
}

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $consultorioId = $_SESSION['usuario']['consultorio_id'];
                $data = [
                    'nombre' => $_POST['nombre'],
                    'rnc' => $_POST['rnc'],
                    'telefono' => $_POST['telefono'],
                    'direccion' => $_POST['direccion'],
                    'medico_nombre' => $_POST['medico_nombre'],
                    'medico_exequatur' => $_POST['medico_exequatur'],
                    'medico_especialidad' => $_POST['medico_especialidad'],
                    'pie_pagina' => $_POST['pie_pagina'] ?? ''
                ];

                // Procesar logo
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $logoPath = $this->procesarLogo($consultorioId, $_FILES['logo']);
                    if ($logoPath) {
                        $data['logo'] = $logoPath;
                    }
                }

                // Eliminar logo si se solicitó
                if (isset($_POST['eliminar_logo']) && $_POST['eliminar_logo'] === '1') {
                    $this->eliminarLogoExistente($consultorioId);
                    $data['logo'] = null;
                }

                // Actualizar consultorio
                if ($this->consultorioModel->update($consultorioId, $data)) {
                    $_SESSION['success'] = "Configuración de recetas guardada exitosamente";
                } else {
                    throw new Exception("Error al guardar la configuración");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }

            header('Location: ' . BASE_URL . '/ajustes-receta');
            exit;
        }
    }

    private function procesarLogo($consultorioId, $file)
    {
        // ✅ CAMBIO IMPORTANTE: Nueva ruta fuera de public/
        $uploadDir = __DIR__ . '/../../uploads/logos/';

        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validar tipo de archivo
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedTypes)) {
            throw new Exception("Tipo de archivo no permitido. Use JPG, PNG o GIF.");
        }

        // Validar tamaño (2MB máximo)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("El archivo es demasiado grande. Máximo 2MB.");
        }

        // Generar nombre único
        $fileName = 'logo_' . $consultorioId . '_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Establecer permisos correctos
            chmod($targetPath, 0644);
            
            // Eliminar logo anterior si existe
            $this->eliminarLogoExistente($consultorioId);
            
            // ✅ CAMBIO: Devolver solo el nombre del archivo, no la ruta completa
            return $fileName;
        }

        throw new Exception("No se pudo guardar el logo. Verifique los permisos.");
    }

    private function eliminarLogoExistente($consultorioId)
    {
        $consultorio = $this->consultorioModel->getById($consultorioId);
        if (!empty($consultorio['logo'])) {
            // ✅ CAMBIO IMPORTANTE: Nueva ruta fuera de public/
            $oldLogoPath = __DIR__ . '/../../uploads/logos/' . $consultorio['logo'];
            if (file_exists($oldLogoPath)) {
                unlink($oldLogoPath);
            }
        }
    }

    private function renderWithLayout($content)
    {
?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ajustes de Receta - Consultorio Ginecológico</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
        </head>

        <body>
            <?php include __DIR__ . '/../../config/navbar.php'; ?>
            <main class="main-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </main>
            <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
        </body>

        </html>
<?php
    }
}
?>
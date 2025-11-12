<?php
// app/controllers/UsuarioController.php
class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        require_once __DIR__ . '/../models/UsuarioModel.php';
        $this->usuarioModel = new UsuarioModel();
        $this->checkAuth();
        $this->checkAdmin();
    }

    private function checkAuth() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function checkAdmin() {
        if ($_SESSION['usuario']['rol'] !== 'administrador') {
            $_SESSION['error'] = "No tienes permisos para acceder a esta sección";
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public function index() {
        $usuarios = $this->usuarioModel->getAllWithConsultorio();
        
        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Gestión de Usuarios</h1>
                <a href="' . BASE_URL . '/usuarios/crear" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Consultorio</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Expiración</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        ';
        
        if (empty($usuarios)) {
            $content .= '
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-users" style="font-size: 3rem; color: #CBD5E0; margin-bottom: 1rem;"></i>
                            <p>No hay usuarios registrados</p>
                        </td>
                    </tr>
            ';
        } else {
            foreach ($usuarios as $usuario) {
                $estado = $usuario['activo'] ? 
                    '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> Activo</span>' : 
                    '<span style="color: var(--error);"><i class="fas fa-times-circle"></i> Inactivo</span>';
                
                $expiracion = $usuario['fecha_expiracion'] ? 
                    date('d/m/Y', strtotime($usuario['fecha_expiracion'])) : 
                    '<span style="color: var(--text-light);">Sin expiración</span>';
                
                $consultorio = $usuario['consultorio_nombre'] ?: '<span style="color: var(--text-light);">Sin asignar</span>';
                
                $content .= '
                    <tr>
                        <td>' . htmlspecialchars($usuario['nombre']) . '</td>
                        <td>' . htmlspecialchars($usuario['email']) . '</td>
                        <td>' . $consultorio . '</td>
                        <td><span class="badge badge-' . $usuario['rol'] . '">' . ucfirst($usuario['rol']) . '</span></td>
                        <td>' . $estado . '</td>
                        <td>' . $expiracion . '</td>
                        <td>
                            <a href="' . BASE_URL . '/usuarios/editar/' . $usuario['id'] . '" class="btn" style="background: var(--warning); color: white;">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                ';
            }
        }
        
        $content .= '
                </tbody>
            </table>
        </div>

        <style>
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-administrador { background: var(--error); color: white; }
        .badge-medico { background: var(--primary); color: white; }
        .badge-recepcionista { background: var(--success); color: white; }
        </style>
        ';

        $this->renderWithLayout($content);
    }

    public function renderForm() {
        $content = '
        <div class="card">
            <h1>Registrar Nuevo Usuario</h1>
            
            <form method="POST" action="' . BASE_URL . '/usuarios/crear">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: María González">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required placeholder="Ej: maria@consultorio.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rol *</label>
                        <select name="rol" class="form-control" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="recepcionista">Recepcionista</option>
                            <option value="medico">Médico</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Duración de Licencia</label>
                        <select name="duracion_licencia" class="form-control">
                            <option value="7">7 días</option>
                            <option value="30">30 días</option>
                            <option value="365" selected>365 días</option>
                            <option value="0">Sin expiración</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña Temporal *</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="text" name="password_temp" class="form-control" value="' . $this->generarClaveTemporal() . '" required style="flex: 1;">
                        <button type="button" class="btn" style="background: var(--warning); color: white;" onclick="generarNuevaClave()">
                            <i class="fas fa-sync-alt"></i> Regenerar
                        </button>
                    </div>
                    <small style="color: var(--text-light); margin-top: 0.5rem; display: block;">
                        Esta contraseña será enviada al usuario para su primer acceso. Guárdela de forma segura.
                    </small>
                </div>

                <div class="form-group">
                    <div style="background: #F7FAFC; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--primary);">
                        <h4 style="margin: 0 0 0.5rem 0; color: var(--primary);">Permisos por Rol:</h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-light);">
                            <li><strong>Administrador:</strong> Acceso completo a todos los módulos</li>
                            <li><strong>Médico:</strong> Gestión de pacientes, consultas y reportes</li>
                            <li><strong>Recepcionista:</strong> Solo gestión de pacientes y agenda</li>
                        </ul>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Usuario
                </button>
                
                <a href="' . BASE_URL . '/usuarios" class="btn" style="background: #718096; color: white; margin-left: 1rem;">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </form>
        </div>

        <script>
        function generarNuevaClave() {
            const nuevaClave = Math.random().toString(36).substring(2, 10).toUpperCase();
            document.querySelector(\'[name="password_temp"]\').value = nuevaClave;
        }
        </script>
        ';

        $this->renderWithLayout($content);
    }

    public function editar($usuarioId) {
        $usuario = $this->usuarioModel->getById($usuarioId);
        
        if (!$usuario) {
            $_SESSION['error'] = "Usuario no encontrado";
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        $content = '
        <div class="card">
            <h1>Editar Usuario</h1>
            
            <form method="POST" action="' . BASE_URL . '/usuarios/actualizar/' . $usuarioId . '">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" class="form-control" required 
                               value="' . htmlspecialchars($usuario['nombre']) . '" placeholder="Ej: María González">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required 
                               value="' . htmlspecialchars($usuario['email']) . '" placeholder="Ej: maria@consultorio.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rol *</label>
                        <select name="rol" class="form-control" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="recepcionista" ' . ($usuario['rol'] == 'recepcionista' ? 'selected' : '') . '>Recepcionista</option>
                            <option value="medico" ' . ($usuario['rol'] == 'medico' ? 'selected' : '') . '>Médico</option>
                            <option value="administrador" ' . ($usuario['rol'] == 'administrador' ? 'selected' : '') . '>Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="activo" class="form-control">
                            <option value="1" ' . ($usuario['activo'] ? 'selected' : '') . '>Activo</option>
                            <option value="0" ' . (!$usuario['activo'] ? 'selected' : '') . '>Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Duración de Licencia</label>
                    <select name="duracion_licencia" class="form-control">
                        <option value="7">7 días</option>
                        <option value="30">30 días</option>
                        <option value="365" selected>365 días</option>
                        <option value="0">Sin expiración</option>
                    </select>
                    <small style="color: var(--text-light); margin-top: 0.5rem; display: block;">
                        Fecha actual de expiración: ' . ($usuario['fecha_expiracion'] ? date('d/m/Y', strtotime($usuario['fecha_expiracion'])) : 'Sin expiración') . '
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Nueva Contraseña (opcional)</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="text" name="nueva_password" class="form-control" placeholder="Dejar vacío para mantener la actual" style="flex: 1;">
                        <button type="button" class="btn" style="background: var(--warning); color: white;" onclick="generarNuevaClave()">
                            <i class="fas fa-sync-alt"></i> Generar
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <div style="background: #F7FAFC; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--primary);">
                        <h4 style="margin: 0 0 0.5rem 0; color: var(--primary);">Información del Usuario:</h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-light);">
                            <li><strong>ID:</strong> ' . $usuario['id'] . '</li>
                            <li><strong>Fecha de creación:</strong> ' . date('d/m/Y H:i', strtotime($usuario['created_at'])) . '</li>
                            <li><strong>Consultorio:</strong> ' . ($usuario['consultorio_nombre'] ?: 'No asignado') . '</li>
                        </ul>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Usuario
                </button>
                
                <a href="' . BASE_URL . '/usuarios" class="btn" style="background: #718096; color: white; margin-left: 1rem;">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </form>
        </div>

        <script>
        function generarNuevaClave() {
            const nuevaClave = Math.random().toString(36).substring(2, 10).toUpperCase();
            document.querySelector(\'[name="nueva_password"]\').value = nuevaClave;
        }
        </script>
        ';

        $this->renderWithLayout($content);
    }

    public function actualizar($usuarioId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si el email ya existe en otro usuario
            $existente = $this->usuarioModel->getByEmail($_POST['email']);
            if ($existente && $existente['id'] != $usuarioId) {
                $_SESSION['error'] = "Ya existe un usuario con este email";
                $this->editar($usuarioId);
                return;
            }

            // Calcular fecha de expiración
            $duracion = intval($_POST['duracion_licencia']);
            $fechaExpiracion = $duracion > 0 ? date('Y-m-d', strtotime("+$duracion days")) : null;

            $data = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'rol' => $_POST['rol'],
                'activo' => $_POST['activo'],
                'fecha_expiracion' => $fechaExpiracion
            ];

            // Si se proporcionó una nueva contraseña, actualizarla
            if (!empty($_POST['nueva_password'])) {
                $data['password'] = password_hash($_POST['nueva_password'], PASSWORD_DEFAULT);
            }

            if ($this->usuarioModel->updateUser($usuarioId, $data)) {
                $_SESSION['success'] = "Usuario actualizado exitosamente" . 
                    (!empty($_POST['nueva_password']) ? ". Nueva contraseña: <strong>" . $_POST['nueva_password'] . "</strong>" : "");
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar usuario";
                $this->editar($usuarioId);
            }
        }
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si el email ya existe
            $existente = $this->usuarioModel->getByEmail($_POST['email']);
            if ($existente) {
                $_SESSION['error'] = "Ya existe un usuario con este email";
                $this->renderForm();
                return;
            }

            // Calcular fecha de expiración
            $duracion = intval($_POST['duracion_licencia']);
            $fechaExpiracion = $duracion > 0 ? date('Y-m-d', strtotime("+$duracion days")) : null;

            // Hash simple de la contraseña
            $data = [
                'consultorio_id' => $_SESSION['usuario']['consultorio_id'],
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password_temp'], PASSWORD_DEFAULT),
                'rol' => $_POST['rol'],
                'fecha_expiracion' => $fechaExpiracion
            ];

            if ($this->usuarioModel->createUser($data)) {
                $_SESSION['success'] = "Usuario creado exitosamente. Contraseña temporal: <strong>" . $_POST['password_temp'] . "</strong>";
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            } else {
                $_SESSION['error'] = "Error al crear usuario";
                $this->renderForm();
            }
        }
    }

    private function generarClaveTemporal() {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    private function renderWithLayout($content) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Consultorio Ginecológico - Usuarios</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
        </head>
        <body>
            <?php include __DIR__ . '/../../config/navbar.php'; ?>
            <main class="main-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
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
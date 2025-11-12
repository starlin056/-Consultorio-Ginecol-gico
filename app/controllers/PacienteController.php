<?php
// app/controllers/PacienteController.php
class PacienteController {
    private $pacienteModel;
    private $consultaModel;
    private $documentoModel;

    public function __construct() {
        require_once __DIR__ . '/../models/PacienteModel.php';
        require_once __DIR__ . '/../models/ConsultaModel.php';
        require_once __DIR__ . '/../models/DocumentoModel.php';
        
        $this->pacienteModel = new PacienteModel();
        $this->consultaModel = new ConsultaModel();
        $this->documentoModel = new DocumentoModel();
        $this->checkAuth();
    }

    private function checkAuth() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index() {
        $pacientes = $this->pacienteModel->getAll();
        
        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Gestión de Pacientes</h1>
                <a href="' . BASE_URL . '/pacientes/crear" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Paciente
                </a>
            </div>

            <div class="search-box">
                <form method="GET" action="' . BASE_URL . '/pacientes/buscar" style="display: flex; gap: 1rem;">
                    <div style="position: relative; flex: 1;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" class="search-input" placeholder="Buscar pacientes por nombre, cédula o email...">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        ';
        
        if (empty($pacientes)) {
            $content .= '
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-users" style="font-size: 3rem; color: #CBD5E0; margin-bottom: 1rem;"></i>
                            <p>No hay pacientes registrados</p>
                            <a href="' . BASE_URL . '/pacientes/crear" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-user-plus"></i> Registrar Primer Paciente
                            </a>
                        </td>
                    </tr>
            ';
        } else {
            foreach ($pacientes as $paciente) {
                $content .= '
                        <tr>
                            <td>' . htmlspecialchars($paciente['cedula']) . '</td>
                            <td>' . htmlspecialchars($paciente['nombre']) . '</td>
                            <td>' . htmlspecialchars($paciente['telefono']) . '</td>
                            <td>' . htmlspecialchars($paciente['email']) . '</td>
                            <td>
                                <a href="' . BASE_URL . '/pacientes/ver/' . $paciente['id'] . '" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Ver
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
        ';

        $this->renderWithLayout($content);
    }

    public function renderForm() {
        $content = '
        <div class="card">
            <h1>Registrar Nuevo Paciente</h1>
            
            <form method="POST" action="' . BASE_URL . '/pacientes/crear">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Cédula *</label>
                        <input type="text" name="cedula" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <textarea name="direccion" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Alergias</label>
                    <textarea name="alergias" class="form-control" rows="3" placeholder="Lista de alergias conocidas..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Antecedentes Médicos</label>
                    <textarea name="antecedentes" class="form-control" rows="3" placeholder="Antecedentes médicos relevantes..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Paciente
                </button>
                
                <a href="' . BASE_URL . '/pacientes" class="btn" style="background: #718096; color: white; margin-left: 1rem;">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </form>
        </div>
        ';

        $this->renderWithLayout($content);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si ya existe paciente con esa cédula
            $existente = $this->pacienteModel->getByCedula($_POST['cedula']);
            if ($existente) {
                $_SESSION['error'] = "Ya existe un paciente con esta cédula";
                $this->renderForm();
                return;
            }

            $data = [
                'consultorio_id' => 1,
                'cedula' => $_POST['cedula'],
                'nombre' => $_POST['nombre'],
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'direccion' => $_POST['direccion'],
                'alergias' => $_POST['alergias'],
                'antecedentes' => $_POST['antecedentes']
            ];

            if ($this->pacienteModel->create($data)) {
                $_SESSION['success'] = "Paciente creado exitosamente";
                header('Location: ' . BASE_URL . '/pacientes');
                exit;
            } else {
                $_SESSION['error'] = "Error al crear paciente";
                $this->renderForm();
            }
        }
    }

    public function ver($id) {
        $paciente = $this->pacienteModel->getById($id);
        if (!$paciente) {
            $_SESSION['error'] = "Paciente no encontrado";
            header('Location: ' . BASE_URL . '/pacientes');
            exit;
        }

        $historial = $this->pacienteModel->getHistorial($id);
        $documentos = $this->documentoModel->getByPaciente($id);
        
        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Paciente: ' . htmlspecialchars($paciente['nombre']) . '</h1>
                <div>
                    <a href="' . BASE_URL . '/consultas/nueva" class="btn btn-primary">
                        <i class="fas fa-stethoscope"></i> Nueva Consulta
                    </a>
                    <a href="' . BASE_URL . '/pacientes" class="btn" style="background: #718096; color: white; margin-left: 0.5rem;">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="grid grid-2">
                <div>
                    <h3>Información Personal</h3>
                    <p><strong>Cédula:</strong> ' . htmlspecialchars($paciente['cedula']) . '</p>
                    <p><strong>Fecha Nacimiento:</strong> ' . ($paciente['fecha_nacimiento'] ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : 'No especificada') . '</p>
                    <p><strong>Teléfono:</strong> ' . htmlspecialchars($paciente['telefono']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($paciente['email']) . '</p>
                    <p><strong>Dirección:</strong> ' . ($paciente['direccion'] ? htmlspecialchars($paciente['direccion']) : 'No especificada') . '</p>
                </div>
                <div>
                    <h3>Información Médica</h3>
                    <p><strong>Alergias:</strong> ' . ($paciente['alergias'] ? htmlspecialchars($paciente['alergias']) : 'Ninguna registrada') . '</p>
                    <p><strong>Antecedentes:</strong> ' . ($paciente['antecedentes'] ? htmlspecialchars($paciente['antecedentes']) : 'Ninguno registrado') . '</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Historial de Consultas</h3>
        ';
        
        if (empty($historial)) {
            $content .= '
                <div style="text-align: center; padding: 2rem; color: #718096;">
                    <i class="fas fa-stethoscope" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No hay consultas registradas para este paciente</p>
                    <a href="' . BASE_URL . '/consultas/nueva" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Registrar Primera Consulta
                    </a>
                </div>
            ';
        } else {
            $content .= '<div class="timeline">';
            foreach ($historial as $consulta) {
                $content .= '
                    <div class="timeline-item">
                        <strong>' . date('d/m/Y H:i', strtotime($consulta['fecha_consulta'])) . '</strong>
                        <p><strong>Médico:</strong> ' . htmlspecialchars($consulta['medico_nombre']) . '</p>
                        ' . ($consulta['diagnostico'] ? '<p><strong>Diagnóstico:</strong> ' . htmlspecialchars($consulta['diagnostico']) . '</p>' : '') . '
                        ' . ($consulta['tratamiento'] ? '<p><strong>Tratamiento:</strong> ' . htmlspecialchars($consulta['tratamiento']) . '</p>' : '') . '
                        ' . ($consulta['proxima_visita'] ? '<p><strong>Próxima visita:</strong> ' . date('d/m/Y', strtotime($consulta['proxima_visita'])) . '</p>' : '') . '
                    </div>
                ';
            }
            $content .= '</div>';
        }
        
        $content .= '
        </div>
        ';

        $this->renderWithLayout($content);
    }

    public function buscar() {
        $term = $_GET['q'] ?? '';
        $resultados = [];

        if (!empty($term)) {
            $resultados = $this->pacienteModel->search($term);
        }

        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Buscar Pacientes</h1>
                <a href="' . BASE_URL . '/pacientes" class="btn" style="background: #718096; color: white;">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="search-box">
                <form method="GET" action="' . BASE_URL . '/pacientes/buscar" style="display: flex; gap: 1rem;">
                    <div style="position: relative; flex: 1;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" class="search-input" placeholder="Buscar pacientes por nombre, cédula o email..." value="' . htmlspecialchars($term) . '">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>
        ';

        if (!empty($term)) {
            if (empty($resultados)) {
                $content .= '
                    <div style="text-align: center; padding: 3rem; color: #718096;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No se encontraron pacientes para: "' . htmlspecialchars($term) . '"</p>
                    </div>
                ';
            } else {
                $content .= '
                    <h3>Resultados de búsqueda: ' . count($resultados) . ' encontrados</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                ';
                
                foreach ($resultados as $paciente) {
                    $content .= '
                            <tr>
                                <td>' . htmlspecialchars($paciente['cedula']) . '</td>
                                <td>' . htmlspecialchars($paciente['nombre']) . '</td>
                                <td>' . htmlspecialchars($paciente['telefono']) . '</td>
                                <td>' . htmlspecialchars($paciente['email']) . '</td>
                                <td>
                                    <a href="' . BASE_URL . '/pacientes/ver/' . $paciente['id'] . '" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                    ';
                }
                
                $content .= '
                        </tbody>
                    </table>
                ';
            }
        }

        $content .= '</div>';

        $this->renderWithLayout($content);
    }

    private function renderWithLayout($content) {
?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Consultorio Ginecológico - Consultas</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
        </head>

        <body>

            <?php include __DIR__ . '/../../config/navbar.php'
; ?>

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
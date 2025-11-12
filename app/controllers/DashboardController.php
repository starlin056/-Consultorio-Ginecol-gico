<?php
// app/controllers/DashboardController.php
class DashboardController {
    private $pacienteModel;
    private $consultaModel;

    public function __construct() {
        require_once __DIR__ . '/../models/PacienteModel.php';
        require_once __DIR__ . '/../models/ConsultaModel.php';
        
        $this->pacienteModel = new PacienteModel();
        $this->consultaModel = new ConsultaModel();
        $this->checkAuth();
    }

    private function checkAuth() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index() {
        $totalPacientes = $this->pacienteModel->count();
        $consultasHoy = $this->consultaModel->getConsultasHoy();
        $proximasCitas = $this->consultaModel->getProximasCitas();
        
        $this->renderWithLayout('
        <div class="dashboard">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <p>Bienvenido, ' . $_SESSION['usuario']['nombre'] . '</p>
            </div>

            <div class="grid grid-3">
                <div class="stats-card">
                    <div class="stats-number">' . $totalPacientes . '</div>
                    <div class="stats-label">Pacientes Registrados</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number">' . count($consultasHoy) . '</div>
                    <div class="stats-label">Consultas Hoy</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number">' . count($proximasCitas) . '</div>
                    <div class="stats-label">Próximas Citas</div>
                </div>
            </div>

            <div class="card">
                <h2>Acciones Rápidas</h2>
                <div class="grid grid-2">
                    <a href="' . BASE_URL . '/pacientes/crear" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Nuevo Paciente
                    </a>
                    <a href="' . BASE_URL . '/pacientes" class="btn btn-primary">
                        <i class="fas fa-users"></i> Ver Pacientes
                    </a>
                    <a href="' . BASE_URL . '/pacientes/buscar" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar Paciente
                    </a>
                    <a href="' . BASE_URL . '/consultas" class="btn btn-primary">
                        <i class="fas fa-stethoscope"></i> Ver Consultas
                    </a>
                    ' . ($_SESSION['usuario']['rol'] === 'administrador' ? '
                    <a href="' . BASE_URL . '/usuarios" class="btn btn-primary">
                        <i class="fas fa-users-cog"></i> Gestionar Usuarios
                    </a>
                    ' : '') . '
                    <a href="' . BASE_URL . '/reportes" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                </div>
            </div>

            <!-- Sección de Próximas Citas -->
            <div class="card">
                <h2>Próximas Citas</h2>
                ' . $this->renderProximasCitas($proximasCitas) . '
            </div>

            <!-- Sección de Consultas de Hoy -->
            <div class="card">
                <h2>Consultas de Hoy</h2>
                ' . $this->renderConsultasHoy($consultasHoy) . '
            </div>
        </div>
        ');
    }

    private function renderProximasCitas($citas) {
        if (empty($citas)) {
            return '
                <div style="text-align: center; padding: 2rem; color: #718096;">
                    <i class="fas fa-calendar-check" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No hay próximas citas programadas</p>
                </div>
            ';
        }

        $html = '<div class="timeline">';
        foreach ($citas as $cita) {
            $html .= '
                <div class="timeline-item">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <strong>' . htmlspecialchars($cita['paciente_nombre']) . '</strong>
                            <p style="margin: 0.25rem 0; color: #718096;">' . htmlspecialchars($cita['cedula']) . '</p>
                            <p style="margin: 0; color: #4A5568;"><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($cita['proxima_visita'])) . '</p>
                        </div>
                        <a href="' . BASE_URL . '/consultas/ver/' . $cita['id'] . '" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </div>
                </div>
            ';
        }
        $html .= '</div>';
        return $html;
    }

    private function renderConsultasHoy($consultas) {
        if (empty($consultas)) {
            return '
                <div style="text-align: center; padding: 2rem; color: #718096;">
                    <i class="fas fa-stethoscope" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No hay consultas programadas para hoy</p>
                </div>
            ';
        }

        $html = '<div class="table-responsive">';
        $html .= '<table class="table">';
        $html .= '
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
        ';
        
        foreach ($consultas as $consulta) {
            $html .= '
                <tr>
                    <td>' . date('H:i', strtotime($consulta['fecha_consulta'])) . '</td>
                    <td>' . htmlspecialchars($consulta['paciente_nombre']) . '</td>
                    <td>' . htmlspecialchars($consulta['cedula']) . '</td>
                    <td>
                        <a href="' . BASE_URL . '/consultas/ver/' . $consulta['id'] . '" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </td>
                </tr>
            ';
        }
        
        $html .= '
            </tbody>
            </table>
        </div>
        ';
        return $html;
    }

    private function renderWithLayout($content) {
?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Consultorio Ginecológico - Dashboard</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
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
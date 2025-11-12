<?php
// app/controllers/ReporteController.php
class ReporteController {
    private $pacienteModel;
    private $consultaModel;
    private $usuarioModel;
    private $recetaModel;

    public function __construct() {
        require_once __DIR__ . '/../models/PacienteModel.php';
        require_once __DIR__ . '/../models/ConsultaModel.php';
        require_once __DIR__ . '/../models/UsuarioModel.php';
        require_once __DIR__ . '/../models/RecetaModel.php';
        
        $this->pacienteModel = new PacienteModel();
        $this->consultaModel = new ConsultaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->recetaModel = new RecetaModel();
        $this->checkAuth();
    }

    private function checkAuth() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index() {
        // Obtener parámetros de filtro
        $filtros = $this->obtenerFiltros();
        
        // Obtener datos REALES según filtros
        $datos = $this->obtenerDatosReporteReal($filtros);
        
        $this->renderWithLayout($this->renderReportesReales($datos, $filtros));
    }

    private function obtenerFiltros() {
        return [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-t'),
            'consultorio_id' => $_GET['consultorio_id'] ?? '',
            'medico_id' => $_GET['medico_id'] ?? '',
            'tipo_reporte' => $_GET['tipo_reporte'] ?? 'general'
        ];
    }

    private function obtenerDatosReporteReal($filtros) {
        // Obtener datos REALES de la base de datos
        return [
            'estadisticasGenerales' => $this->getEstadisticasGenerales($filtros),
            'consultasPorMes' => $this->getConsultasPorMesReal($filtros),
            'distribucionDiagnosticos' => $this->getDistribucionDiagnosticosReal($filtros),
            'topPacientes' => $this->getTopPacientes($filtros),
            'estadisticasMedicos' => $this->getEstadisticasMedicosReal($filtros),
            'recetasPorTipo' => $this->getRecetasPorTipo($filtros),
            'tendenciasMensuales' => $this->getTendenciasMensuales($filtros)
        ];
    }

    private function getEstadisticasGenerales($filtros) {
        return [
            'totalPacientes' => $this->pacienteModel->count(),
            'totalConsultas' => $this->consultaModel->countConsultasPorRango($filtros['fecha_inicio'], $filtros['fecha_fin']),
            'consultasHoy' => $this->consultaModel->countConsultasHoy(),
            'totalMedicos' => $this->usuarioModel->countMedicos(),
            'totalRecetas' => $this->recetaModel->countRecetasPorRango($filtros['fecha_inicio'], $filtros['fecha_fin']),
            'pacientesNuevos' => $this->pacienteModel->countPacientesNuevos($filtros['fecha_inicio'], $filtros['fecha_fin'])
        ];
    }

    private function getConsultasPorMesReal($filtros) {
        $consultas = $this->consultaModel->getConsultasAgrupadasPorMes(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $meses = [];
        foreach ($consultas as $consulta) {
            $mes = date('M', strtotime($consulta['mes']));
            $meses[$mes] = (int)$consulta['total'];
        }
        
        return $meses;
    }

    private function getDistribucionDiagnosticosReal($filtros) {
        $diagnosticos = $this->consultaModel->getDistribucionDiagnosticos(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $distribucion = [];
        foreach ($diagnosticos as $diag) {
            if (!empty($diag['cie10_codigo'])) {
                $clave = $diag['cie10_codigo'] . ' - ' . $this->getDescripcionCIE10($diag['cie10_codigo']);
            } else {
                $clave = 'Sin diagnóstico específico';
            }
            $distribucion[$clave] = (int)$diag['total'];
        }
        
        return array_slice($distribucion, 0, 8); // Top 8 diagnósticos
    }

    private function getDescripcionCIE10($codigo) {
        $cie10 = [
            'N80' => 'Endometriosis',
            'N81' => 'Prolapso genital femenino',
            'N82' => 'Fístulas que afectan el tracto genital femenino',
            'N83' => 'Trastornos no inflamatorios del ovario',
            'N84' => 'Pólipo del tracto genital femenino',
            'N85' => 'Otros trastornos no inflamatorios del útero',
            'N86' => 'Erosión y ectropión del cuello del útero',
            'N87' => 'Displasia del cuello del útero',
            'N88' => 'Otros trastornos no inflamatorios del cuello del útero',
            'N89' => 'Otros trastornos no inflamatorios de la vagina',
            'N90' => 'Otros trastornos no inflamatorios de la vulva',
            'N91' => 'Menstruación ausente, escasa o poco frecuente',
            'N92' => 'Menstruación excesiva, frecuente e irregular',
            'N93' => 'Otras hemorragias uterinas o vaginales anormales',
            'N94' => 'Dolor y otras afecciones relacionadas con los órganos genitales femeninos',
            'N95' => 'Otros trastornos menopáusicos y perimenopáusicos',
            'N96' => 'Abortadora habitual',
            'N97' => 'Infertilidad femenina',
            'O00' => 'Embarazo ectópico',
            'O01' => 'Mola hidatiforme',
            'O02' => 'Otros productos anormales de la concepción',
            'O03' => 'Aborto espontáneo',
            'O04' => 'Aborto médico',
            'O09' => 'Atención del embarazo',
            'O10' => 'Hipertensión preexistente que complica el embarazo',
            'O24' => 'Diabetes mellitus en el embarazo',
            'O80' => 'Parto único espontáneo',
            'O85' => 'Sepsis puerperal',
            'O86' => 'Otras infecciones puerperales',
            'O98' => 'Enfermedades infecciosas y parasitarias maternas',
            'A54' => 'Infección gonocócica',
            'A56' => 'Otras infecciones de transmisión sexual debidas a clamidias',
            'A59' => 'Tricomoniasis',
            'B37' => 'Candidiasis',
            'N70' => 'Salpingitis y ooforitis',
            'N71' => 'Enfermedad inflamatoria del útero',
            'N72' => 'Enfermedad inflamatoria del cuello del útero',
            'N73' => 'Otras enfermedades inflamatorias pélvicas femeninas',
            'N76' => 'Otras inflamaciones de la vagina y de la vulva',
            'C53' => 'Neoplasia maligna del cuello del útero',
            'C54' => 'Neoplasia maligna del cuerpo del útero',
            'C56' => 'Neoplasia maligna del ovario',
            'D06' => 'Carcinoma in situ del cuello del útero',
            'D07' => 'Carcinoma in situ de otros órganos genitales'
        ];
        
        return $cie10[$codigo] ?? 'Diagnóstico no especificado';
    }

    private function getTopPacientes($filtros) {
        return $this->consultaModel->getTopPacientes(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin'],
            5
        );
    }

    private function getEstadisticasMedicosReal($filtros) {
        $medicos = $this->consultaModel->getEstadisticasPorMedico(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $estadisticas = [];
        foreach ($medicos as $medico) {
            $estadisticas[] = [
                'medico' => $medico['medico_nombre'] ?: 'Médico no asignado',
                'consultas' => (int)$medico['total_consultas'],
                'pacientes_unicos' => (int)$medico['pacientes_unicos'],
                'promedio_consultas' => $medico['total_consultas'] > 0 ? 
                    round($medico['total_consultas'] / $medico['pacientes_unicos'], 1) : 0
            ];
        }
        
        return $estadisticas;
    }

    private function getRecetasPorTipo($filtros) {
        $recetas = $this->recetaModel->getRecetasPorTipo(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $tipos = ['medicamento' => 0, 'analisis' => 0];
        foreach ($recetas as $receta) {
            $tipo = $receta['tipo_receta'] ?? 'medicamento';
            $tipos[$tipo] = (int)$receta['total'];
        }
        
        return $tipos;
    }

    private function getTendenciasMensuales($filtros) {
        $tendencias = $this->consultaModel->getTendenciasMensuales(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $datos = [
            'meses' => [],
            'consultas' => [],
            'pacientes' => []
        ];
        
        foreach ($tendencias as $tendencia) {
            $datos['meses'][] = date('M Y', strtotime($tendencia['mes']));
            $datos['consultas'][] = (int)$tendencia['total_consultas'];
            $datos['pacientes'][] = (int)$tendencia['pacientes_unicos'];
        }
        
        return $datos;
    }

    private function renderReportesReales($datos, $filtros) {
        ob_start();
        ?>
        <div class="reportes-container">
            <!-- Header -->
            <div class="card">
                <div class="reportes-header">
                    <div>
                        <h1><i class="fas fa-chart-line"></i> Reportes en Tiempo Real</h1>
                        <p class="text-muted">Datos actuales del consultorio ginecológico - <?= date('d/m/Y H:i') ?></p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card">
                <div class="filtros-header">
                    <h3><i class="fas fa-filter"></i> Filtros de Reporte</h3>
                </div>
                <form method="GET" class="filtros-grid">
                    <div class="filtro-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?>" class="form-control">
                    </div>
                    
                    <div class="filtro-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?>" class="form-control">
                    </div>
                    
                    <div class="filtro-group">
                        <label>Tipo de Reporte</label>
                        <select name="tipo_reporte" class="form-control">
                            <option value="general" <?= $filtros['tipo_reporte'] == 'general' ? 'selected' : '' ?>>General</option>
                            <option value="medico" <?= $filtros['tipo_reporte'] == 'medico' ? 'selected' : '' ?>>Por Médico</option>
                            <option value="diagnosticos" <?= $filtros['tipo_reporte'] == 'diagnosticos' ? 'selected' : '' ?>>Diagnósticos</option>
                        </select>
                    </div>
                    
                    <div class="filtro-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <a href="<?= BASE_URL ?>/reportes" class="btn btn-outline">
                            <i class="fas fa-eraser"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="grid grid-4">
                <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= $datos['estadisticasGenerales']['totalPacientes'] ?></div>
                        <div class="stats-label">Pacientes Totales</div>
                        <div class="stats-trend">
                            +<?= $datos['estadisticasGenerales']['pacientesNuevos'] ?> nuevos
                        </div>
                    </div>
                </div>

                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <div class="stats-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= $datos['estadisticasGenerales']['totalConsultas'] ?></div>
                        <div class="stats-label">Consultas Período</div>
                        <div class="stats-trend">
                            <?= $datos['estadisticasGenerales']['consultasHoy'] ?> hoy
                        </div>
                    </div>
                </div>

                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <div class="stats-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= $datos['estadisticasGenerales']['totalMedicos'] ?></div>
                        <div class="stats-label">Médicos Activos</div>
                        <div class="stats-trend">
                            <?= count($datos['estadisticasMedicos']) ?> con actividad
                        </div>
                    </div>
                </div>

                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                    <div class="stats-icon">
                        <i class="fas fa-prescription"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= $datos['estadisticasGenerales']['totalRecetas'] ?></div>
                        <div class="stats-label">Recetas Emitidas</div>
                        <div class="stats-trend">
                            <?= $datos['recetasPorTipo']['medicamento'] ?> medicamentos
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos Principales -->
            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Consultas por Mes</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="graficoConsultas"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> Distribución de Diagnósticos</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="graficoDiagnosticos"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tendencias -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Tendencias Mensuales</h3>
                </div>
                <div class="chart-container">
                    <canvas id="graficoTendencias"></canvas>
                </div>
            </div>

            <!-- Tablas de Datos -->
            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-md"></i> Rendimiento por Médico</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Consultas</th>
                                    <th>Pacientes Únicos</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($datos['estadisticasMedicos'] as $medico): ?>
                                <tr>
                                    <td><?= htmlspecialchars($medico['medico']) ?></td>
                                    <td><span class="badge badge-primary"><?= $medico['consultas'] ?></span></td>
                                    <td><?= $medico['pacientes_unicos'] ?></td>
                                    <td><?= $medico['promedio_consultas'] ?> cons/pac</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-diagnoses"></i> Diagnósticos Más Frecuentes</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Diagnóstico</th>
                                    <th>Cantidad</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalDiagnosticos = array_sum($datos['distribucionDiagnosticos']);
                                foreach($datos['distribucionDiagnosticos'] as $diagnostico => $cantidad): 
                                    $porcentaje = $totalDiagnosticos > 0 ? ($cantidad / $totalDiagnosticos) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($diagnostico) ?></td>
                                    <td><?= $cantidad ?></td>
                                    <td>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: <?= $porcentaje ?>%"></div>
                                            <span><?= number_format($porcentaje, 1) ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Recetas -->
            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-prescription-bottle"></i> Distribución de Recetas</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="graficoRecetas"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Pacientes Más Frecuentes</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Consultas</th>
                                    <th>Última Visita</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($datos['topPacientes'] as $paciente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($paciente['paciente_nombre']) ?></td>
                                    <td><span class="badge badge-info"><?= $paciente['total_consultas'] ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($paciente['ultima_consulta'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts para gráficos -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Datos reales desde PHP
            const consultasData = <?= json_encode(array_values($datos['consultasPorMes'])) ?>;
            const consultasLabels = <?= json_encode(array_keys($datos['consultasPorMes'])) ?>;
            
            const diagnosticosData = <?= json_encode(array_values($datos['distribucionDiagnosticos'])) ?>;
            const diagnosticosLabels = <?= json_encode(array_keys($datos['distribucionDiagnosticos'])) ?>;
            
            const recetasData = <?= json_encode(array_values($datos['recetasPorTipo'])) ?>;
            const recetasLabels = ['Medicamentos', 'Análisis'];
            
            const tendenciasData = <?= json_encode($datos['tendenciasMensuales']) ?>;

            // Colores para gráficos
            const colors = [
                '#8B5FBF', '#ED64A6', '#48BB78', '#ECC94B', '#4299E1',
                '#F56565', '#38B2AC', '#ED8936', '#9F7AEA', '#4FD1C7'
            ];

            // Inicializar gráficos
            document.addEventListener('DOMContentLoaded', function() {
                initGraficoConsultas();
                initGraficoDiagnosticos();
                initGraficoRecetas();
                initGraficoTendencias();
            });

            function initGraficoConsultas() {
                const ctx = document.getElementById('graficoConsultas').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: consultasLabels,
                        datasets: [{
                            label: 'Consultas',
                            data: consultasData,
                            backgroundColor: colors[0],
                            borderColor: colors[0],
                            borderWidth: 2,
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.1)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            function initGraficoDiagnosticos() {
                const ctx = document.getElementById('graficoDiagnosticos').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: diagnosticosLabels,
                        datasets: [{
                            data: diagnosticosData,
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { 
                                    padding: 20,
                                    usePointStyle: true,
                                    boxWidth: 12
                                }
                            }
                        }
                    }
                });
            }

            function initGraficoRecetas() {
                const ctx = document.getElementById('graficoRecetas').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: recetasLabels,
                        datasets: [{
                            data: recetasData,
                            backgroundColor: [colors[0], colors[1]],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 20 }
                            }
                        }
                    }
                });
            }

            function initGraficoTendencias() {
                const ctx = document.getElementById('graficoTendencias').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: tendenciasData.meses,
                        datasets: [
                            {
                                label: 'Consultas',
                                data: tendenciasData.consultas,
                                borderColor: colors[0],
                                backgroundColor: colors[0] + '20',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Pacientes Únicos',
                                data: tendenciasData.pacientes,
                                borderColor: colors[1],
                                backgroundColor: colors[1] + '20',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.1)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        </script>

        <style>
            .reportes-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .filtros-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                align-items: end;
            }

            .filtro-actions {
                grid-column: 1 / -1;
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }

            .stats-card {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1.5rem;
                border-radius: 0.5rem;
                color: white;
            }

            .stats-icon {
                font-size: 2.5rem;
                opacity: 0.9;
            }

            .stats-content {
                flex: 1;
            }

            .stats-number {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 0.25rem;
            }

            .stats-label {
                font-size: 0.9rem;
                opacity: 0.9;
                margin-bottom: 0.25rem;
            }

            .stats-trend {
                font-size: 0.8rem;
                opacity: 0.8;
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--border);
            }

            .chart-container {
                height: 300px;
                position: relative;
            }

            .progress-bar-container {
                position: relative;
                background: #E2E8F0;
                border-radius: 10px;
                height: 20px;
                overflow: hidden;
            }

            .progress-bar {
                background: #8B5FBF;
                height: 100%;
                border-radius: 10px;
                transition: width 0.3s ease;
            }

            .progress-bar-container span {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 0.8rem;
                font-weight: 600;
                color: #2D3748;
            }

            .badge {
                padding: 0.25rem 0.5rem;
                border-radius: 0.25rem;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .badge-primary { background: var(--primary); color: white; }
            .badge-info { background: var(--info); color: white; }

            .btn-outline {
                background: transparent;
                border: 2px solid var(--border);
                color: var(--text);
            }

            .text-muted {
                color: var(--text-light);
            }

            @media (max-width: 768px) {
                .filtros-grid {
                    grid-template-columns: 1fr;
                }
                
                .reportes-header {
                    flex-direction: column;
                    gap: 1rem;
                    align-items: flex-start;
                }
                
                .grid-4 {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }

    private function renderWithLayout($content) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Consultorio Ginecológico - Reportes</title>
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
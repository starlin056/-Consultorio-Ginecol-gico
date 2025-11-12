<?php
// app/controllers/RecetaController.php
class RecetaController
{
    private $recetaModel;
    private $consultaModel;
    private $pacienteModel;
    private $usuarioModel;
    private $consultorioModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/RecetaModel.php';
        require_once __DIR__ . '/../models/ConsultaModel.php';
        require_once __DIR__ . '/../models/PacienteModel.php';
        require_once __DIR__ . '/../models/UsuarioModel.php';
        require_once __DIR__ . '/../models/ConsultorioModel.php';

        $this->recetaModel = new RecetaModel();
        $this->consultaModel = new ConsultaModel();
        $this->pacienteModel = new PacienteModel();
        $this->usuarioModel = new UsuarioModel();
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
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        // Preparar filtros
        $filters = [];
        if (!empty($_GET['numero_receta'])) {
            $filters['numero_receta'] = $_GET['numero_receta'];
        }
        if (!empty($_GET['paciente'])) {
            $filters['paciente'] = $_GET['paciente'];
        }
        if (!empty($_GET['medico'])) {
            $filters['medico'] = $_GET['medico'];
        }
        if (!empty($_GET['fecha_desde'])) {
            $filters['fecha_desde'] = $_GET['fecha_desde'];
        }
        if (!empty($_GET['fecha_hasta'])) {
            $filters['fecha_hasta'] = $_GET['fecha_hasta'];
        }

        // Obtener recetas con paginación y filtros
        $recetas = $this->recetaModel->getAllWithPagination($offset, $porPagina, $filters);
        $totalRecetas = $this->recetaModel->getTotalCount($filters);
        $totalPaginas = ceil($totalRecetas / $porPagina);

        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1><i class="fas fa-prescription"></i> Gestión de Recetas Médicas</h1>
                <div>
                    <button class="btn btn-primary" onclick="filtrarRecetas()">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div id="filtrosContainer" class="card" style="margin-bottom: 2rem; ' . (!empty($filters) ? 'display: block;' : 'display: none;') . '">
                <h3 style="color: var(--primary); margin-bottom: 1rem;">
                    <i class="fas fa-filter"></i> Filtros de Búsqueda
                </h3>
                <form method="GET" action="' . BASE_URL . '/recetas" id="filtrosForm">
                    <div class="grid grid-3" style="gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Número de Receta</label>
                            <input type="text" name="numero_receta" class="form-control" 
                                   value="' . htmlspecialchars($_GET['numero_receta'] ?? '') . '" 
                                   placeholder="Ej: REC-20231201-0001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Paciente</label>
                            <input type="text" name="paciente" class="form-control" 
                                   value="' . htmlspecialchars($_GET['paciente'] ?? '') . '" 
                                   placeholder="Nombre del paciente">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Médico</label>
                            <input type="text" name="medico" class="form-control" 
                                   value="' . htmlspecialchars($_GET['medico'] ?? '') . '" 
                                   placeholder="Nombre del médico">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" 
                                   value="' . htmlspecialchars($_GET['fecha_desde'] ?? '') . '">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" 
                                   value="' . htmlspecialchars($_GET['fecha_hasta'] ?? '') . '">
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="' . BASE_URL . '/recetas" class="btn" style="background: #718096; color: white;">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="grid grid-4" style="gap: 1rem; margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--primary);">
                        <i class="fas fa-prescription"></i>
                    </div>
                    <div class="stat-info">
                        <h3>' . $totalRecetas . '</h3>
                        <p>Total Recetas</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--success);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3>' . $this->recetaModel->getCountHoy() . '</h3>
                        <p>Hoy</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--warning);">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-info">
                        <h3>' . $this->recetaModel->getCountEstaSemana() . '</h3>
                        <p>Esta Semana</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--info);">
                        <i class="fas fa-calendar-month"></i>
                    </div>
                    <div class="stat-info">
                        <h3>' . $this->recetaModel->getCountEsteMes() . '</h3>
                        <p>Este Mes</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de recetas -->
            <div class="card">
        ';

        if ($totalRecetas > 0) {
            $content .= '
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Número Receta</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Fecha Emisión</th>
                                <th>Seguro Médico</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            ';
        }

        if (empty($recetas)) {
            $content .= '
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: #718096;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    ' . (empty($filters) ? 'No se encontraron recetas médicas' : 'No se encontraron recetas con los filtros aplicados') . '
                                </td>
                            </tr>
            ';
        } else {
            foreach ($recetas as $receta) {
                $content .= '
                            <tr>
                                <td>
                                    <strong>' . htmlspecialchars($receta['numero_receta']) . '</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>' . htmlspecialchars($receta['paciente_nombre']) . '</strong>
                                        <br>
                                        <small style="color: #718096;">CI: ' . htmlspecialchars($receta['cedula']) . '</small>
                                    </div>
                                </td>
                                <td>' . htmlspecialchars($receta['medico_nombre']) . '</td>
                                <td>' . date('d/m/Y', strtotime($receta['fecha_emision'])) . '</td>
                                <td>
                                    <span class="badge badge-info">' . htmlspecialchars($receta['seguro_medico']) . '</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="' . BASE_URL . '/recetas/ver/' . $receta['id'] . '" 
                                           class="btn btn-primary btn-sm" 
                                           title="Ver Receta">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="' . BASE_URL . '/consultas/ver/' . $receta['consulta_id'] . '" 
                                           class="btn btn-info btn-sm" 
                                           title="Ver Consulta">
                                            <i class="fas fa-stethoscope"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                ';
            }
        }

        if ($totalRecetas > 0) {
            $content .= '
                        </tbody>
                    </table>
                </div>
            ';
        }

        $content .= '
            </div>
        ';

        // Paginación
        if ($totalPaginas > 1) {
            $content .= '
            <div class="pagination" style="margin-top: 2rem;">
                ' . $this->generarPaginacion($pagina, $totalPaginas, $filters) . '
            </div>
            ';
        }

        $content .= '
        </div>

        <script>
        function filtrarRecetas() {
            const container = document.getElementById("filtrosContainer");
            container.style.display = container.style.display === "none" ? "block" : "none";
        }
        </script>
        ';

        $this->renderWithLayout($content);
    }

    private function generarPaginacion($paginaActual, $totalPaginas, $filters = [])
    {
        $html = '';
        $baseUrl = BASE_URL . '/recetas';

        // Construir query string con filtros
        $queryParams = $filters;
        $queryParams['pagina'] = '';

        $queryString = http_build_query($queryParams);
        $queryString = rtrim($queryString, '=');

        if ($queryString) {
            $baseUrl .= '?' . $queryString . '&';
        } else {
            $baseUrl .= '?';
        }

        // Botón anterior
        if ($paginaActual > 1) {
            $html .= '<a href="' . $baseUrl . 'pagina=' . ($paginaActual - 1) . '" class="pagination-link">';
            $html .= '<i class="fas fa-chevron-left"></i> Anterior</a>';
        }

        // Números de página (máximo 5 páginas alrededor de la actual)
        $inicio = max(1, $paginaActual - 2);
        $fin = min($totalPaginas, $paginaActual + 2);

        for ($i = $inicio; $i <= $fin; $i++) {
            if ($i == $paginaActual) {
                $html .= '<span class="pagination-link active">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $baseUrl . 'pagina=' . $i . '" class="pagination-link">' . $i . '</a>';
            }
        }

        // Botón siguiente
        if ($paginaActual < $totalPaginas) {
            $html .= '<a href="' . $baseUrl . 'pagina=' . ($paginaActual + 1) . '" class="pagination-link">';
            $html .= 'Siguiente <i class="fas fa-chevron-right"></i></a>';
        }

        return $html;
    }

    public function crear($consulta_id)
    {
        $consulta = $this->consultaModel->getByIdWithPaciente($consulta_id);
        if (!$consulta) {
            $_SESSION['error'] = "Consulta no encontrada";
            header('Location: ' . BASE_URL . '/consultas');
            exit;
        }

        // Verificar si ya existe receta para esta consulta
        $recetaExistente = $this->recetaModel->getByConsulta($consulta_id);
        if ($recetaExistente) {
            $_SESSION['info'] = "Ya existe una receta para esta consulta";
            header('Location: ' . BASE_URL . '/recetas/ver/' . $recetaExistente['id']);
            exit;
        }

        $consultorio = $this->consultorioModel->getById($_SESSION['usuario']['consultorio_id']);
        $medico = $this->usuarioModel->getById($_SESSION['usuario']['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarReceta($consulta_id, $consulta, $consultorio, $medico);
        } else {
            $this->renderFormularioReceta($consulta, $consultorio, $medico);
        }
    }

    private function renderFormularioReceta($consulta, $consultorio, $medico)
    {
        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1><i class="fas fa-prescription"></i> Generar Receta Médica</h1>
                <a href="' . BASE_URL . '/consultas/ver/' . $consulta['id'] . '" class="btn" style="background: #718096; color: white;">
                    <i class="fas fa-arrow-left"></i> Volver a Consulta
                </a>
            </div>

            <!-- Información del Paciente -->
            <div class="card" style="background: #f8f9fa; margin-bottom: 2rem;">
                <h3 style="color: var(--primary); margin-bottom: 1rem;">
                    <i class="fas fa-user-injured"></i> Información del Paciente
                </h3>
                <div class="grid grid-3">
                    <div>
                        <strong>Paciente:</strong> ' . htmlspecialchars($consulta['paciente_nombre']) . '
                    </div>
                    <div>
                        <strong>Cédula:</strong> ' . htmlspecialchars($consulta['cedula']) . '
                    </div>
                    <div>
                        <strong>Fecha Nacimiento:</strong> ' . ($consulta['fecha_nacimiento'] ? date('d/m/Y', strtotime($consulta['fecha_nacimiento'])) : 'No especificada') . '
                    </div>
                </div>
            </div>

            <form method="POST" action="' . BASE_URL . '/recetas/crear/' . $consulta['id'] . '" id="formReceta">
               
                <!-- Información del Seguro -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">
                        <i class="fas fa-shield-alt"></i> Información del Seguro Médico
                    </h3>
                    <div class="form-group">
                        <label class="form-label">Tipo de Seguro Médico</label>
                        <select name="seguro_medico" class="form-control" id="seguroSelect">
                            <option value="">Seleccionar seguro...</option>
                            <option value="SENASA">SENASA</option>
                            <option value="ARS Humano">ARS Humano</option>
                            <option value="ARS Palic">ARS Palic</option>
                            <option value="ARS Universal">ARS Universal</option>
                            <option value="ARS Monumental">ARS Monumental</option>
                            <option value="ARS Futuro">ARS Futuro</option>
                            <option value="ARS Renacer">ARS Renacer</option>
                            <option value="ARS MetaSalud">ARS MetaSalud</option>
                            <option value="ARS Simag">ARS Simag</option>
                            <option value="Privado">Privado</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group" id="otroSeguroContainer" style="display: none;">
                        <label class="form-label">Especificar Seguro</label>
                        <input type="text" name="seguro_otro" class="form-control" placeholder="Especificar nombre del seguro">
                    </div>
                </div>

                <!-- Medicamentos -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="color: var(--primary); margin: 0;">
                            <i class="fas fa-pills"></i> Medicamentos Recetados
                        </h3>
                        <button type="button" class="btn btn-primary" onclick="agregarMedicamento()">
                            <i class="fas fa-plus"></i> Agregar Medicamento
                        </button>
                    </div>

                    <div id="medicamentosContainer">
                        <!-- Los medicamentos se agregarán aquí dinámicamente -->
                        <div class="medicamento-item card" style="margin-bottom: 1rem; border: 2px solid var(--primary);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h4 style="color: var(--primary); margin: 0;">Medicamento #1</h4>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMedicamento(this)" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="grid grid-2" style="gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Medicamento *</label>
                                    <input type="text" name="medicamentos[0][nombre]" class="form-control" required 
                                           placeholder="Ej: Paracetamol 500mg">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Dosis *</label>
                                    <input type="text" name="medicamentos[0][dosis]" class="form-control" required 
                                           placeholder="Ej: 1 tableta">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Frecuencia *</label>
                                    <input type="text" name="medicamentos[0][frecuencia]" class="form-control" required 
                                           placeholder="Ej: Cada 8 horas">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Duración *</label>
                                    <input type="text" name="medicamentos[0][duracion]" class="form-control" required 
                                           placeholder="Ej: 7 días">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Instrucciones Especiales</label>
                                <textarea name="medicamentos[0][instrucciones]" class="form-control" rows="2" 
                                          placeholder="Instrucciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones Generales -->
                <div class="card">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">
                        <i class="fas fa-stethoscope"></i> Instrucciones Generales
                    </h3>
                    <div class="form-group">
                        <textarea name="instrucciones_generales" class="form-control" rows="4" 
                                  placeholder="Instrucciones generales para el paciente..."></textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-medical"></i> Generar Receta
                    </button>
                    <a href="' . BASE_URL . '/consultas/ver/' . $consulta['id'] . '" class="btn btn-lg" style="background: #718096; color: white;">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <script>
        let medicamentoCount = 1;

        function agregarMedicamento() {
            const container = document.getElementById("medicamentosContainer");
            const newItem = document.createElement("div");
            newItem.className = "medicamento-item card";
            newItem.style.cssText = "margin-bottom: 1rem; border: 2px solid var(--success);";
            
            newItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: var(--success); margin: 0;">Medicamento #${medicamentoCount + 1}</h4>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMedicamento(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-2" style="gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Medicamento *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][nombre]" class="form-control" required 
                               placeholder="Ej: Paracetamol 500mg">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dosis *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][dosis]" class="form-control" required 
                               placeholder="Ej: 1 tableta">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frecuencia *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][frecuencia]" class="form-control" required 
                               placeholder="Ej: Cada 8 horas">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][duracion]" class="form-control" required 
                               placeholder="Ej: 7 días">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Instrucciones Especiales</label>
                    <textarea name="medicamentos[${medicamentoCount}][instrucciones]" class="form-control" rows="2" 
                              placeholder="Instrucciones adicionales..."></textarea>
                </div>
            `;
            
            container.appendChild(newItem);
            medicamentoCount++;
        }

        function eliminarMedicamento(button) {
            const item = button.closest(".medicamento-item");
            item.remove();
            reordenarMedicamentos();
        }

        function reordenarMedicamentos() {
            const items = document.querySelectorAll(".medicamento-item");
            items.forEach((item, index) => {
                const title = item.querySelector("h4");
                title.textContent = "Medicamento #" + (index + 1);
                title.style.color = index === 0 ? "var(--primary)" : "var(--success)";
                
                // Actualizar los names de los inputs
                const inputs = item.querySelectorAll(\'[name^="medicamentos["]\');
                inputs.forEach(input => {
                    const name = input.getAttribute("name");
                    const newName = name.replace(/medicamentos\\[\\d+\\]/, "medicamentos[" + index + "]");
                    input.setAttribute("name", newName);
                });
            });
            medicamentoCount = items.length;
        }

        // Manejo del seguro médico
        document.getElementById("seguroSelect").addEventListener("change", function() {
            const otroContainer = document.getElementById("otroSeguroContainer");
            otroContainer.style.display = this.value === "Otro" ? "block" : "none";
        });

        // Validación del formulario
        document.getElementById("formReceta").addEventListener("submit", function(e) {
            const medicamentos = document.querySelectorAll(".medicamento-item");
            if (medicamentos.length === 0) {
                e.preventDefault();
                alert("Debe agregar al menos un medicamento");
                return;
            }

            // Validar que todos los medicamentos tengan los campos requeridos
            let todosValidos = true;
            medicamentos.forEach((item, index) => {
                const inputs = item.querySelectorAll("[required]");
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = "var(--error)";
                        todosValidos = false;
                    } else {
                        input.style.borderColor = "";
                    }
                });
            });

            if (!todosValidos) {
                e.preventDefault();
                alert("Por favor complete todos los campos requeridos en los medicamentos");
            }
        });
        </script>
        ';

        $this->renderWithLayout($content);
    }

    private function procesarReceta($consulta_id, $consulta, $consultorio, $medico)
    {
        try {
            // Validar que el consultorio tenga exequatur
            if (empty($consultorio['medico_exequatur'])) {
                throw new Exception("El exequatur del médico no está configurado. Por favor, configure el consultorio primero.");
            }


            // Generar número de receta único
            $numero_receta = 'REC-' . date('Ymd') . '-' . str_pad($consulta_id, 4, '0', STR_PAD_LEFT);

            // Determinar el seguro médico
            $seguro_medico = $_POST['seguro_medico'];
            if ($seguro_medico === 'Otro') {
                $seguro_medico = $_POST['seguro_otro'] ?? 'Otro';
            }

            // Crear la receta principal
            $recetaData = [
                'consultorio_id' => $consultorio['id'],
                'medico_id' => $medico['id'],
                'paciente_id' => $consulta['paciente_id'],
                'consulta_id' => $consulta_id,
                'seguro_medico' => $seguro_medico,
                'numero_receta' => $numero_receta,
                'fecha_emision' => date('Y-m-d'),
                'instrucciones' => $_POST['instrucciones_generales'] ?? ''
            ];

            $receta_id = $this->recetaModel->createAndGetId($recetaData);

            if ($receta_id) {
                // Guardar los medicamentos
                if (isset($_POST['medicamentos']) && is_array($_POST['medicamentos'])) {
                    foreach ($_POST['medicamentos'] as $medicamento) {
                        if (!empty($medicamento['nombre'])) {
                            $medData = [
                                'receta_id' => $receta_id,
                                'medicamento' => $medicamento['nombre'],
                                'dosis' => $medicamento['dosis'],
                                'frecuencia' => $medicamento['frecuencia'],
                                'duracion' => $medicamento['duracion'],
                                'instrucciones' => $medicamento['instrucciones'] ?? ''
                            ];
                            $this->recetaModel->createMedicamento($medData);
                        }
                    }
                }

                $_SESSION['success'] = "Receta generada exitosamente. Número: " . $numero_receta;
                header('Location: ' . BASE_URL . '/recetas/ver/' . $receta_id);
                exit;
            } else {
                throw new Exception("Error al generar la receta");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/recetas/crear/' . $consulta_id);
            exit;
        }
    }
    public function ver($receta_id)
    {
        $receta = $this->recetaModel->getByIdWithDetails($receta_id);
        if (!$receta) {
            $_SESSION['error'] = "Receta no encontrada";
            header('Location: ' . BASE_URL . '/consultas');
            exit;
        }

        $medicamentos = $this->recetaModel->getMedicamentosByReceta($receta_id);

        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1><i class="fas fa-prescription"></i> Receta Médica #' . htmlspecialchars($receta['numero_receta']) . '</h1>
                <div>
                    <button class="btn btn-primary" onclick="imprimirReceta()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="' . BASE_URL . '/consultas/ver/' . $receta['consulta_id'] . '" class="btn" style="background: #718096; color: white; margin-left: 0.5rem;">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div id="recetaContent" style="background: white; padding: 2rem; border: 2px solid #e2e8f0; border-radius: 0.5rem;">
                ' . $this->generarVistaReceta($receta, $medicamentos) . '
            </div>
        </div>

        <script>
        function imprimirReceta() {
            const ventana = window.open("", "_blank");
            const contenido = document.getElementById("recetaContent").innerHTML;
            
            ventana.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Receta Médica - ' . htmlspecialchars($receta['numero_receta']) . '</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                        .header { text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #333; padding-bottom: 1rem; }
                        .section { margin-bottom: 1.5rem; }
                        .medicamento { border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 0.25rem; }
                        .firma { margin-top: 3rem; text-align: right; }
                        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
                        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    ${contenido}
                    <div class="no-print" style="text-align: center; margin-top: 2rem;">
                        <button onclick="window.print()" style="padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 0.25rem; cursor: pointer;">Imprimir</button>
                        <button onclick="window.close()" style="padding: 0.5rem 1rem; background: #6c757d; color: white; border: none; border-radius: 0.25rem; cursor: pointer; margin-left: 1rem;">Cerrar</button>
                    </div>
                </body>
                </html>
            `);
            ventana.document.close();
        }
        </script>
        ';

        $this->renderWithLayout($content);
    }

   private function generarVistaReceta($receta, $medicamentos)
{
    $consultorioModel = new ConsultorioModel();
    $consultorio = $consultorioModel->getById($receta['consultorio_id']);

    $logo = !empty($consultorio['logo']) ?
        '<img src="' . BASE_URL .  '/files/logos/' . $consultorio['logo'] . '" style="max-height: 80px; margin-bottom: 1rem;">' :
        '';

    $medicoNombre = !empty($consultorio['medico_nombre']) ? $consultorio['medico_nombre'] : $receta['medico_nombre'];
    $medicoExequatur = $consultorio['medico_exequatur'];
    $medicoEspecialidad = !empty($consultorio['medico_especialidad']) ? $consultorio['medico_especialidad'] : 'Ginecología';

    // Filtrar medicamentos y análisis según el tipo de receta
    $medicamentosFiltrados = [];
    $analisisFiltrados = [];
    
    foreach ($medicamentos as $item) {
        if ($item['tipo_item'] === 'medicamento') {
            $medicamentosFiltrados[] = $item;
        } else if ($item['tipo_item'] === 'analisis') {
            $analisisFiltrados[] = $item;
        }
    }

    // Determinar qué sección mostrar según el tipo de receta
    $seccionContenido = '';
    if ($receta['tipo_receta'] === 'analisis') {
        $tituloSeccion = 'ANÁLISIS SOLICITADOS';
        $seccionContenido = $this->generarListaAnalisis($analisisFiltrados);
    } else {
        $tituloSeccion = 'MEDICAMENTOS RECETADOS';
        $seccionContenido = $this->generarListaMedicamentos($medicamentosFiltrados);
    }

    return '
    <div class="header">
        ' . $logo . '
        <h1 style="color: #2c5282; margin-bottom: 0.5rem;">RECETA MÉDICA</h1>
        <p style="color: #4a5568; margin: 0;">' . htmlspecialchars($consultorio['nombre']) . '</p>
        <p style="color: #718096; margin: 0.25rem 0;">' . htmlspecialchars($consultorio['direccion']) . '</p>
        <p style="color: #718096; margin: 0;">Tel: ' . htmlspecialchars($consultorio['telefono']) . ' | RNC: ' . htmlspecialchars($consultorio['rnc']) . '</p>
    </div>

    <div class="grid-2" style="margin-bottom: 2rem;">
        <div class="section">
            <h3 style="color: #2c5282; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">PACIENTE</h3>
            <p><strong>Nombre:</strong> ' . htmlspecialchars($receta['paciente_nombre']) . '</p>
            <p><strong>Cédula:</strong> ' . htmlspecialchars($receta['cedula']) . '</p>
            <p><strong>Fecha Nacimiento:</strong> ' . ($receta['fecha_nacimiento'] ? date('d/m/Y', strtotime($receta['fecha_nacimiento'])) : 'No especificada') . '</p>
            <p><strong>Seguro Médico:</strong> ' . htmlspecialchars($receta['seguro_medico']) . '</p>
        </div>

        <div class="section">
            <h3 style="color: #2c5282; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">MÉDICO</h3>
            <p><strong>Dr./Dra.:</strong> ' . htmlspecialchars($medicoNombre) . '</p>
            <p><strong>Exequatur:</strong> ' . htmlspecialchars($medicoExequatur) . '</p>
            <p><strong>Especialidad:</strong> ' . htmlspecialchars($medicoEspecialidad) . '</p>
            <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($receta['fecha_emision'])) . '</p>
        </div>
    </div>

    <div class="section">
        <h3 style="color: #2c5282; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">' . $tituloSeccion . '</h3>
        ' . $seccionContenido . '
    </div>

    ' . ($receta['instrucciones'] ? '
    <div class="section">
        <h3 style="color: #2c5282; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">INSTRUCCIONES GENERALES</h3>
        <p style="background: #f7fafc; padding: 1rem; border-radius: 0.25rem;">' . nl2br(htmlspecialchars($receta['instrucciones'])) . '</p>
    </div>
    ' : '') . '

    ' . (!empty($consultorio['pie_pagina']) ? '
    <div class="section">
        <p style="color: #718096; font-size: 0.9rem; font-style: italic; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
            ' . nl2br(htmlspecialchars($consultorio['pie_pagina'])) . '
        </p>
    </div>
    ' : '') . '

    <div class="firma">
        <p style="border-top: 1px solid #333; padding-top: 1rem; width: 300px; margin-left: auto;">
            <strong>Firma del Médico:</strong><br><br>
            _________________________<br>
            ' . htmlspecialchars($medicoNombre) . '<br>
            Exequatur: ' . htmlspecialchars($medicoExequatur) . '
        </p>
    </div>
    ';
}


private function generarListaAnalisis($analisis)
{
    if (empty($analisis)) {
        return '<p style="text-align: center; color: #718096; padding: 2rem;">No hay análisis solicitados</p>';
    }

    $html = '';
    foreach ($analisis as $index => $anal) {
        $html .= '
        <div class="analisis" style="background: ' . ($index % 2 === 0 ? '#f7fafc' : 'white') . '; border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 0.25rem;">
            <h4 style="color: #2c5282; margin-bottom: 0.5rem;">' . ($index + 1) . '. ' . htmlspecialchars($anal['medicamento']) . '</h4>
            ' . ($anal['instrucciones'] ? '<div style="margin-top: 0.5rem;"><strong>Indicaciones:</strong> ' . htmlspecialchars($anal['instrucciones']) . '</div>' : '') . '
        </div>
        ';
    }
    return $html;
}

    public function modalCrear($consulta_id)
    {
        $consulta = $this->consultaModel->getByIdWithPaciente($consulta_id);
        if (!$consulta) {
            http_response_code(404);
            echo "Consulta no encontrada";
            exit;
        }

        $consultorio = $this->consultorioModel->getById($_SESSION['usuario']['consultorio_id']);
        $medico = $this->usuarioModel->getById($_SESSION['usuario']['id']);

        echo '
        <div class="modal-header">
            <h3><i class="fas fa-prescription"></i> Generar Receta Médica</h3>
            <button type="button" class="close" onclick="cerrarModalReceta()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formRecetaModal" method="POST" action="' . BASE_URL . '/recetas/crear/' . $consulta_id . '">
                <!-- Información del Paciente -->
                <div class="card" style="background: #f8f9fa; margin-bottom: 1rem;">
                    <h4 style="color: var(--primary); margin-bottom: 0.5rem;">
                        <i class="fas fa-user-injured"></i> Información del Paciente
                    </h4>
                    <div class="grid grid-3">
                        <div>
                            <strong>Paciente:</strong> ' . htmlspecialchars($consulta['paciente_nombre']) . '
                        </div>
                        <div>
                            <strong>Cédula:</strong> ' . htmlspecialchars($consulta['cedula']) . '
                        </div>
                        <div>
                            <strong>Fecha Nacimiento:</strong> ' . ($consulta['fecha_nacimiento'] ? date('d/m/Y', strtotime($consulta['fecha_nacimiento'])) : 'No especificada') . '
                        </div>
                    </div>
                </div>

                <!-- Información del Seguro -->
                <div class="form-group">
                    <label class="form-label">Tipo de Seguro Médico</label>
                    <select name="seguro_medico" class="form-control" id="seguroSelectModal">
                        <option value="">Seleccionar seguro...</option>
                        <option value="SENASA">SENASA</option>
                        <option value="ARS Humano">ARS Humano</option>
                        <option value="ARS Palic">ARS Palic</option>
                        <option value="ARS Universal">ARS Universal</option>
                        <option value="ARS Monumental">ARS Monumental</option>
                        <option value="ARS Futuro">ARS Futuro</option>
                        <option value="ARS Renacer">ARS Renacer</option>
                        <option value="ARS MetaSalud">ARS MetaSalud</option>
                        <option value="ARS Simag">ARS Simag</option>
                        <option value="Privado">Privado</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group" id="otroSeguroContainerModal" style="display: none;">
                    <label class="form-label">Especificar Seguro</label>
                    <input type="text" name="seguro_otro" class="form-control" placeholder="Especificar nombre del seguro">
                </div>

                <!-- Medicamentos -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 style="color: var(--primary); margin: 0;">
                            <i class="fas fa-pills"></i> Medicamentos Recetados
                        </h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarMedicamentoModal()">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>

                    <div id="medicamentosContainerModal">
                        <div class="medicamento-item card" style="margin-bottom: 1rem; border: 2px solid var(--primary);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h5 style="color: var(--primary); margin: 0;">Medicamento #1</h5>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMedicamentoModal(this)" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="grid grid-2" style="gap: 0.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Medicamento *</label>
                                    <input type="text" name="medicamentos[0][nombre]" class="form-control" required 
                                           placeholder="Ej: Paracetamol 500mg">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Dosis *</label>
                                    <input type="text" name="medicamentos[0][dosis]" class="form-control" required 
                                           placeholder="Ej: 1 tableta">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Frecuencia *</label>
                                    <input type="text" name="medicamentos[0][frecuencia]" class="form-control" required 
                                           placeholder="Ej: Cada 8 horas">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Duración *</label>
                                    <input type="text" name="medicamentos[0][duracion]" class="form-control" required 
                                           placeholder="Ej: 7 días">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Instrucciones Especiales</label>
                                <textarea name="medicamentos[0][instrucciones]" class="form-control" rows="2" 
                                          placeholder="Instrucciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones Generales -->
                <div class="form-group">
                    <label class="form-label">Instrucciones Generales</label>
                    <textarea name="instrucciones_generales" class="form-control" rows="3" 
                              placeholder="Instrucciones generales para el paciente..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-medical"></i> Generar Receta
                    </button>
                    <button type="button" class="btn" style="background: #718096; color: white;" onclick="cerrarModalReceta()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>

        <script>
        let medicamentoCountModal = 1;

        function agregarMedicamentoModal() {
            const container = document.getElementById("medicamentosContainerModal");
            const newItem = document.createElement("div");
            newItem.className = "medicamento-item card";
            newItem.style.cssText = "margin-bottom: 1rem; border: 2px solid var(--success);";
            
            newItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h5 style="color: var(--success); margin: 0;">Medicamento #${medicamentoCountModal + 1}</h5>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMedicamentoModal(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-2" style="gap: 0.5rem;">
                    <div class="form-group">
                        <label class="form-label">Medicamento *</label>
                        <input type="text" name="medicamentos[${medicamentoCountModal}][nombre]" class="form-control" required 
                               placeholder="Ej: Paracetamol 500mg">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dosis *</label>
                        <input type="text" name="medicamentos[${medicamentoCountModal}][dosis]" class="form-control" required 
                               placeholder="Ej: 1 tableta">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frecuencia *</label>
                        <input type="text" name="medicamentos[${medicamentoCountModal}][frecuencia]" class="form-control" required 
                               placeholder="Ej: Cada 8 horas">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración *</label>
                        <input type="text" name="medicamentos[${medicamentoCountModal}][duracion]" class="form-control" required 
                               placeholder="Ej: 7 días">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Instrucciones Especiales</label>
                    <textarea name="medicamentos[${medicamentoCountModal}][instrucciones]" class="form-control" rows="2" 
                              placeholder="Instrucciones adicionales..."></textarea>
                </div>
            `;
            
            container.appendChild(newItem);
            medicamentoCountModal++;
        }

        function eliminarMedicamentoModal(button) {
            const item = button.closest(".medicamento-item");
            item.remove();
            reordenarMedicamentosModal();
        }

        function reordenarMedicamentosModal() {
            const items = document.querySelectorAll("#medicamentosContainerModal .medicamento-item");
            items.forEach((item, index) => {
                const title = item.querySelector("h5");
                title.textContent = "Medicamento #" + (index + 1);
                title.style.color = index === 0 ? "var(--primary)" : "var(--success)";
                
                // Actualizar los names de los inputs
                const inputs = item.querySelectorAll(\'[name^="medicamentos["]\');
                inputs.forEach(input => {
                    const name = input.getAttribute("name");
                    const newName = name.replace(/medicamentos\\[\\d+\\]/, "medicamentos[" + index + "]");
                    input.setAttribute("name", newName);
                });
            });
            medicamentoCountModal = items.length;
        }

        // Manejo del seguro médico en modal
        document.getElementById("seguroSelectModal").addEventListener("change", function() {
            const otroContainer = document.getElementById("otroSeguroContainerModal");
            otroContainer.style.display = this.value === "Otro" ? "block" : "none";
        });

        // Validación del formulario modal
        document.getElementById("formRecetaModal").addEventListener("submit", function(e) {
            const medicamentos = document.querySelectorAll("#medicamentosContainerModal .medicamento-item");
            if (medicamentos.length === 0) {
                e.preventDefault();
                alert("Debe agregar al menos un medicamento");
                return;
            }

            // Validar que todos los medicamentos tengan los campos requeridos
            let todosValidos = true;
            medicamentos.forEach((item, index) => {
                const inputs = item.querySelectorAll("[required]");
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = "var(--error)";
                        todosValidos = false;
                    } else {
                        input.style.borderColor = "";
                    }
                });
            });

            if (!todosValidos) {
                e.preventDefault();
                alert("Por favor complete todos los campos requeridos en los medicamentos");
            }
        });
        </script>
        ';
    }

    public function modalVer($receta_id)
    {
        $receta = $this->recetaModel->getByIdWithDetails($receta_id);
        if (!$receta) {
            http_response_code(404);
            echo "Receta no encontrada";
            exit;
        }

        $medicamentos = $this->recetaModel->getMedicamentosByReceta($receta_id);

        echo '
        <div class="modal-header">
            <h3><i class="fas fa-file-medical"></i> Receta #' . htmlspecialchars($receta['numero_receta']) . '</h3>
            <button type="button" class="close" onclick="cerrarModalReceta()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="max-height: 70vh; overflow-y: auto;">
                ' . $this->generarVistaReceta($receta, $medicamentos) . '
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-primary" onclick="imprimirReceta(' . $receta_id . ')">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn" style="background: #718096; color: white;" onclick="cerrarModalReceta()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
        ';
    }

    public function imprimir($receta_id)
    {
        $receta = $this->recetaModel->getByIdWithDetails($receta_id);
        if (!$receta) {
            $_SESSION['error'] = "Receta no encontrada";
            header('Location: ' . BASE_URL . '/consultas');
            exit;
        }

        $medicamentos = $this->recetaModel->getMedicamentosByReceta($receta_id);

        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receta Médica - ' . htmlspecialchars($receta['numero_receta']) . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    line-height: 1.6;
                    color: #333;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 2rem; 
                    border-bottom: 2px solid #333; 
                    padding-bottom: 1rem; 
                }
                .section { 
                    margin-bottom: 1.5rem; 
                }
                .medicamento { 
                    border: 1px solid #ddd; 
                    padding: 1rem; 
                    margin-bottom: 1rem; 
                    border-radius: 0.25rem; 
                }
                .firma { 
                    margin-top: 3rem; 
                    text-align: right; 
                }
                .grid-2 { 
                    display: grid; 
                    grid-template-columns: 1fr 1fr; 
                    gap: 2rem; 
                }
                .grid-3 { 
                    display: grid; 
                    grid-template-columns: 1fr 1fr 1fr; 
                    gap: 1rem; 
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            ' . $this->generarVistaReceta($receta, $medicamentos) . '
            <div class="no-print" style="text-align: center; margin-top: 2rem;">
                <button onclick="window.print()" style="padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 0.25rem; cursor: pointer;">Imprimir</button>
                <button onclick="window.close()" style="padding: 0.5rem 1rem; background: #6c757d; color: white; border: none; border-radius: 0.25rem; cursor: pointer; margin-left: 1rem;">Cerrar</button>
            </div>
        </body>
        </html>
        ';
        exit;
    }

    private function generarListaMedicamentos($medicamentos)
{
    if (empty($medicamentos)) {
        return '<p style="text-align: center; color: #718096; padding: 2rem;">No hay medicamentos recetados</p>';
    }

    $html = '';
    foreach ($medicamentos as $index => $med) {
        $html .= '
        <div class="medicamento" style="background: ' . ($index % 2 === 0 ? '#f7fafc' : 'white') . '; border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 0.25rem;">
            <h4 style="color: #2c5282; margin-bottom: 0.5rem;">' . ($index + 1) . '. ' . htmlspecialchars($med['medicamento']) . '</h4>
            <div class="grid-3" style="gap: 1rem; font-size: 0.9rem;">
                <div><strong>Dosis:</strong> ' . htmlspecialchars($med['dosis']) . '</div>
                <div><strong>Frecuencia:</strong> ' . htmlspecialchars($med['frecuencia']) . '</div>
                <div><strong>Duración:</strong> ' . htmlspecialchars($med['duracion']) . '</div>
            </div>
            ' . ($med['instrucciones'] ? '<div style="margin-top: 0.5rem;"><strong>Instrucciones:</strong> ' . htmlspecialchars($med['instrucciones']) . '</div>' : '') . '
        </div>
        ';
    }
    return $html;
}


    public function crearRapida()
{
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    try {
        if (empty($_POST['consulta_id']) || empty($_POST['paciente_id']) || empty($_POST['medicamento'])) {
            throw new Exception('Faltan datos obligatorios.');
        }

        $dataReceta = [
            'consultorio_id' => $_SESSION['usuario']['consultorio_id'],
            'medico_id' => $_SESSION['usuario']['id'],
            'paciente_id' => $_POST['paciente_id'],
            'consulta_id' => $_POST['consulta_id'],
            'fecha_emision' => date('Y-m-d'),
            'numero_receta' => 'REC-' . date('Ymd-His'),
            'activo' => 1
        ];

        $recetaId = $this->recetaModel->createAndGetId($dataReceta);

        if (!$recetaId) throw new Exception('Error al crear la receta.');

        $dataMed = [
            'receta_id' => $recetaId,
            'medicamento' => $_POST['medicamento'],
            'dosis' => $_POST['dosis'] ?? '',
            'frecuencia' => $_POST['frecuencia'] ?? '',
            'duracion' => $_POST['duracion'] ?? '',
            'instrucciones' => $_POST['instrucciones'] ?? ''
        ];

        $this->recetaModel->createMedicamento($dataMed);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
            <title>Consultorio Ginecológico - Recetas</title>
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
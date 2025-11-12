<?php
// app/controllers/ConsultaController.php
class ConsultaController
{
    private $consultaModel;
    private $pacienteModel;
    private $usuarioModel;
    private $documentoModel;
    private $cie10Model;
    private $recetaModel;
    private $consultorioModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/ConsultaModel.php';
        require_once __DIR__ . '/../models/PacienteModel.php';
        require_once __DIR__ . '/../models/UsuarioModel.php';
        require_once __DIR__ . '/../models/DocumentoModel.php';
        require_once __DIR__ . '/../models/Cie10Model.php';
        require_once __DIR__ . '/../models/RecetaModel.php';
        require_once __DIR__ . '/../models/ConsultorioModel.php';

        $this->consultaModel = new ConsultaModel();
        $this->pacienteModel = new PacienteModel();
        $this->usuarioModel = new UsuarioModel();
        $this->documentoModel = new DocumentoModel();
        $this->cie10Model = new Cie10Model();
        $this->recetaModel = new RecetaModel();
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
        // Fecha actual del sistema (según zona horaria configurada en PHP.ini o setTimezone)
        $fechaActual = date('Y-m-d');

        // Obtener las consultas del día desde el modelo
        $consultas = $this->consultaModel->getConsultasDelDia($fechaActual);

        // Construir encabezado de la vista
        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1><i class="fas fa-calendar-day"></i> Consultas del Día - ' . date('d/m/Y') . '</h1>
                <a href="' . BASE_URL . '/consultas/nueva" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Consulta
                </a>
            </div>
        ';

        // Si no hay consultas, mostrar mensaje
        if (empty($consultas)) {
            $content .= '
            <div style="text-align:center; padding:3rem;">
                <i class="fas fa-calendar-times" style="font-size:4rem; color:#CBD5E0;"></i>
                <h3 style="margin-top:1rem; color:#4A5568;">No hay consultas programadas para hoy</h3>
                <p style="color:#718096;">Puedes agregar una nueva consulta con el botón de arriba.</p>
                <a href="' . BASE_URL . '/consultas/nueva" class="btn btn-primary" style="margin-top:1rem;">
                    <i class="fas fa-plus-circle"></i> Agregar Primera Consulta
                </a>
            </div>
            ';
        } else {
            // Tabla de consultas del día
            $content .= '
            <div class="table-responsive">
                <table class="table table-striped" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Próxima Visita</th>
                            <th>Documentos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

            foreach ($consultas as $consulta) {
                $documentosCount = $this->documentoModel->countByConsulta($consulta['id']);
                $tieneReceta = $this->recetaModel->getByConsulta($consulta['id']);

                $hora = date('h:i A', strtotime($consulta['fecha_consulta']));
                $diagnostico = !empty($consulta['diagnostico'])
                    ? htmlspecialchars(substr($consulta['diagnostico'], 0, 60))
                    : 'Sin diagnóstico';
                $proxima = $consulta['proxima_visita']
                    ? date('d/m/Y', strtotime($consulta['proxima_visita']))
                    : '<span style="color:#A0AEC0;">No programada</span>';

                $content .= '
                <tr>
                    <td><i class="far fa-clock"></i> ' . $hora . '</td>
                    <td><strong>' . htmlspecialchars($consulta['paciente_nombre']) . '</strong></td>
                    <td>' . $diagnostico . '...</td>
                    <td>' . $proxima . '</td>
                    <td>
                        <span class="badge" style="background:' . ($documentosCount > 0 ? 'var(--success)' : 'var(--text-light)') . ';color:white;">
                            ' . $documentosCount . ' archivo' . ($documentosCount != 1 ? 's' : '') . '
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <a href="' . BASE_URL . '/consultas/ver/' . $consulta['id'] . '" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Ver
                            </a>';

                // Mostrar botón de receta según estado
                if (!$tieneReceta) {
                    $content .= '
                            <a href="' . BASE_URL . '/recetas/crear/' . $consulta['id'] . '" class="btn btn-success btn-sm">
                                <i class="fas fa-prescription"></i> Receta
                            </a>';
                } else {
                    $content .= '
                            <a href="' . BASE_URL . '/recetas/ver/' . $tieneReceta['id'] . '" class="btn btn-info btn-sm">
                                <i class="fas fa-file-medical"></i> Ver Receta
                            </a>';
                }
/*
                // Botón para cerrar consulta
                $content .= '
                            <a href="' . BASE_URL . '/consultas/cerrar/' . $consulta['id'] . '" class="btn btn-warning btn-sm" 
                                onclick="return confirm(\'¿Está seguro de que desea cerrar esta consulta?\')">
                                <i class="fas fa-check-circle"></i> Cerrar
                            </a>
                        </div>
                    </td>
                </tr>'; */
            }

            $content .= '
                    </tbody>
                </table>
            </div>';
        }

        $content .= '</div>';

        $this->renderWithLayout($content);
    }

    public function nueva()
    {
        $this->renderForm();
    }

    public function cerrar($consulta_id)
    {
        try {
            $consulta = $this->consultaModel->getByIdWithPaciente($consulta_id);
            if (!$consulta) {
                throw new Exception("Consulta no encontrada");
            }

            // Verificar si tiene receta
            $tieneReceta = $this->recetaModel->getByConsulta($consulta_id);
            
            if ($tieneReceta) {
                // Redirigir a la vista de la receta para imprimir
                $_SESSION['success'] = "Consulta cerrada exitosamente. Puede imprimir la receta.";
                header('Location: ' . BASE_URL . '/recetas/ver/' . $tieneReceta['id']);
            } else {
                // Solo cerrar la consulta
                $_SESSION['success'] = "Consulta cerrada exitosamente.";
                header('Location: ' . BASE_URL . '/consultas');
            }
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/consultas');
            exit;
        }
    }

    public function renderForm()
    {
        $pacientes = $this->pacienteModel->getAll();
        $content = '
        <div class="card">
            <h1>Nueva Consulta</h1>
            <form method="POST" action="' . BASE_URL . '/consultas/crear" enctype="multipart/form-data" id="consultaForm" novalidate>
                <div class="form-group">
                    <label class="form-label">Paciente *</label>
                    <select name="paciente_id" class="form-control" required id="pacienteSelect">
                        <option value="">Seleccionar paciente...</option>';
        foreach ($pacientes as $paciente) {
            $content .= '<option value="' . $paciente['id'] . '">' . htmlspecialchars($paciente['nombre']) . ' - ' . htmlspecialchars($paciente['cedula']) . '</option>';
        }
        $content .= '
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha y Hora de la Consulta *</label>
                    <input type="datetime-local" name="fecha_consulta" class="form-control" required 
                           value="' . date('Y-m-d\TH:i') . '">
                </div>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Síntomas</label>
                        <textarea name="sintomas" class="form-control" rows="4" placeholder="Descripción de los síntomas..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Diagnóstico (CIE-10)</label>
                        <div class="diagnostico-container">
                            <div class="input-group">
                                <input type="text" name="diagnostico_busqueda" class="form-control" 
                                       placeholder="Buscar código o descripción CIE-10..." 
                                       id="cie10Search">
                                <button type="button" class="btn btn-outline" onclick="mostrarModalCIE10()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                            <input type="hidden" name="cie10_codigo" id="cie10Codigo">
                            <textarea name="diagnostico" class="form-control" rows="3" 
                                      placeholder="Diagnóstico médico..." id="diagnosticoTextarea"
                                      readonly style="margin-top: 0.5rem; background-color: #f8f9fa;"></textarea>
                            <small class="text-muted">
                                Presione el botón de búsqueda para seleccionar un diagnóstico CIE-10
                            </small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tratamiento</label>
                    <textarea name="tratamiento" class="form-control" rows="4" placeholder="Tratamiento indicado..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Indicaciones</label>
                    <textarea name="indicaciones" class="form-control" rows="3" placeholder="Indicaciones para el paciente..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Notas Adicionales</label>
                    <textarea name="notas" class="form-control" rows="3" placeholder="Notas adicionales..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Próxima Visita</label>
                    <input type="date" name="proxima_visita" class="form-control">
                </div>
                ' . $this->renderSeccionRecetaCompleta() . '
                <!-- Sección de Subida de Archivos -->
                <div class="card" style="background: #f8f9fa; border: 2px dashed #dee2e6;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 1.1rem; color: var(--primary);">
                            <i class="fas fa-file-upload"></i> Resultados de Análisis y Documentos
                        </label>
                        <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                            <div class="form-group">
                                <label class="form-label">Subir Archivos</label>
                                <input type="file" name="documentos[]" multiple class="form-control" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx,.txt" 
                                       id="fileInput">
                                <small class="text-muted">
                                    Formatos permitidos: PDF, Word (.doc, .docx), Excel (.xls, .xlsx), Imágenes (.jpg, .jpeg, .png), Texto (.txt)
                                    <br>Máximo 10MB por archivo
                                </small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descripción de los documentos</label>
                                <textarea name="descripcion_documentos" class="form-control" rows="2" 
                                          placeholder="Ej: Resultados de hematología, ultrasonido, etc..."></textarea>
                            </div>
                            <div id="filePreview" style="margin-top: 1rem; display: none;">
                                <h4 style="margin-bottom: 1rem; color: var(--text);">Archivos seleccionados:</h4>
                                <div id="fileList"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Guardar Consulta y Documentos
                    </button>
                    <a href="' . BASE_URL . '/consultas" class="btn" style="background: #718096; color: white;">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        ' . $this->renderModalCIE10() . '
        <script>
        // Funcionalidad para CIE-10
        function mostrarModalCIE10() {
            document.getElementById("cie10Modal").style.display = "block";
            cargarCategoriasCIE10();
            buscarCIE10();
        }
        function cerrarModalCIE10() {
            document.getElementById("cie10Modal").style.display = "none";
        }
        function cargarCategoriasCIE10() {
            fetch("' . BASE_URL . '/api/cie10/categorias")
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Error al cargar categorías");
                    }
                    return response.json();
                })
                .then(data => {
                    const select = document.getElementById("cie10Categoria");
                    select.innerHTML = \'<option value="">Todas las categorías</option>\';
                    data.forEach(categoria => {
                        const option = document.createElement("option");
                        option.value = categoria;
                        option.textContent = categoria;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error al cargar las categorías CIE-10");
                });
        }
        function buscarCIE10() {
            const termino = document.getElementById("cie10BuscarInput").value;
            const categoria = document.getElementById("cie10Categoria").value;
            let url = "' . BASE_URL . '/api/cie10/buscar?q=" + encodeURIComponent(termino);
            if (categoria) {
                url += "&categoria=" + encodeURIComponent(categoria);
            }
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Error en la búsqueda");
                    }
                    return response.json();
                })
                .then(data => {
                    mostrarResultadosCIE10(data);
                })
                .catch(error => {
                    console.error("Error:", error);
                    document.getElementById("cie10Resultados").innerHTML = \'<div class="alert alert-error">Error al buscar diagnósticos</div>\';
                });
        }
        function filtrarPorCategoria() {
            buscarCIE10();
        }
        function mostrarResultadosCIE10(resultados) {
            const container = document.getElementById("cie10Resultados");
            if (resultados.length === 0) {
                container.innerHTML = \'<div class="text-center" style="padding: 2rem; color: var(--text-light);">No se encontraron resultados</div>\';
                return;
            }
            let html = \'<div class="grid grid-1" style="gap: 0.5rem;">\';
            resultados.forEach(item => {
                const descripcionEscapada = item.descripcion.replace(/\'/g, "\\\\\'");
                html += `
                    <div class="cie10-item" 
                         onclick="seleccionarCIE10(\'${item.codigo}\', \'${descripcionEscapada}\')"
                         style="border: 1px solid var(--border); border-radius: 0.5rem; padding: 1rem; cursor: pointer; transition: all 0.3s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <strong style="color: var(--primary);">${item.codigo}</strong>
                                <div style="margin-top: 0.25rem;">${item.descripcion}</div>
                                ${item.categoria ? \'<div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.25rem;">\' + item.categoria + \'</div>\' : \'\'}
                            </div>
                            <i class="fas fa-chevron-right" style="color: var(--primary); margin-left: 1rem;"></i>
                        </div>
                    </div>
                `;
            });
            html += \'</div>\';
            container.innerHTML = html;
        }
        function seleccionarCIE10(codigo, descripcion) {
            document.getElementById("cie10Codigo").value = codigo;
            document.getElementById("diagnosticoTextarea").value = codigo + " - " + descripcion;
            cerrarModalCIE10();
        }
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById("cie10Modal");
            if (event.target === modal) {
                cerrarModalCIE10();
            }
        }
        // Funcionalidad existente para archivos
        document.getElementById("fileInput").addEventListener("change", function(e) {
            const fileList = document.getElementById("fileList");
            const filePreview = document.getElementById("filePreview");
            const files = e.target.files;
            fileList.innerHTML = "";
            if (files.length > 0) {
                filePreview.style.display = "block";
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileItem = document.createElement("div");
                    fileItem.className = "file-item";
                    fileItem.style.cssText = `
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                        padding: 0.5rem;
                        background: white;
                        border: 1px solid var(--border);
                        border-radius: 0.25rem;
                        margin-bottom: 0.5rem;
                    `;
                    const fileIcon = getFileIcon(file.name);
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    fileItem.innerHTML = `
                        <i class="${fileIcon}" style="color: var(--primary);"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">${file.name}</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">${fileSize} MB</div>
                        </div>
                        <span class="badge" style="background: var(--success); color: white;">Listo</span>
                    `;
                    fileList.appendChild(fileItem);
                }
            } else {
                filePreview.style.display = "none";
            }
        });
        function getFileIcon(filename) {
            const ext = filename.split(".").pop().toLowerCase();
            switch(ext) {
                case "pdf": return "fas fa-file-pdf";
                case "doc": case "docx": return "fas fa-file-word";
                case "xls": case "xlsx": return "fas fa-file-excel";
                case "jpg": case "jpeg": case "png": return "fas fa-file-image";
                case "txt": return "fas fa-file-alt";
                default: return "fas fa-file";
            }
        }
        document.getElementById("consultaForm").addEventListener("submit", function(e) {
            const fileInput = document.getElementById("fileInput");
            const files = fileInput.files;
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ["pdf", "doc", "docx", "jpg", "jpeg", "png", "xls", "xlsx", "txt"];
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileExt = file.name.split(".").pop().toLowerCase();
                if (!allowedTypes.includes(fileExt)) {
                    e.preventDefault();
                    alert("Tipo de archivo no permitido: " + file.name + "\\nFormatos permitidos: PDF, Word, Excel, Imágenes, Texto");
                    return;
                }
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert("Archivo demasiado grande: " + file.name + "\\nMáximo permitido: 10MB");
                    return;
                }
            }
        });

        // BOTÓN DINÁMICO CORREGIDO
        document.addEventListener("DOMContentLoaded", function() {
            const submitBtn = document.getElementById("submitBtn");
            
            function actualizarBoton() {
                const medicamentos = document.querySelectorAll(".medicamento-item");
                const analisis = document.querySelectorAll(".analisis-item");
                
                console.log("Medicamentos:", medicamentos.length, "Análisis:", analisis.length);
                
                if (medicamentos.length > 0 || analisis.length > 0) {
                    submitBtn.innerHTML = \'<i class="fas fa-save"></i> Guardar Consulta y Generar Receta\';
                    submitBtn.style.background = "var(--success)";
                } else {
                    submitBtn.innerHTML = \'<i class="fas fa-save"></i> Guardar Consulta y Documentos\';
                    submitBtn.style.background = "var(--primary)";
                }
            }

            // Observar cambios en los contenedores
            const observer = new MutationObserver(actualizarBoton);
            const medicamentosContainer = document.getElementById("medicamentosContainer");
            const analisisContainer = document.getElementById("analisisContainer");
            
            if (medicamentosContainer) {
                observer.observe(medicamentosContainer, { childList: true, subtree: true });
            }
            if (analisisContainer) {
                observer.observe(analisisContainer, { childList: true, subtree: true });
            }

            // Actualizar al cambiar tipo de receta
            document.querySelectorAll(\'input[name="tipo_receta"]\').forEach(radio => {
                radio.addEventListener("change", actualizarBoton);
            });

            // Llamar inicialmente
            actualizarBoton();
        });
        </script>
        <style>
        .cie10-item:hover {
            background: #f8f9fa !important;
            border-color: var(--primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            text-align: right;
        }
        .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }
        .close:hover {
            color: var(--text);
        }
        .input-group {
            display: flex;
            gap: 0.5rem;
        }
        .input-group .form-control {
            flex: 1;
        }
        .file-item {
            transition: all 0.3s ease;
        }
        .file-item:hover {
            background: #f8f9fa !important;
            transform: translateX(5px);
        }
        .file-item i {
            font-size: 1.2rem;
        }
        </style>
        ';
        $this->renderWithLayout($content);
    }

    private function renderSeccionRecetaCompleta()
    {
        return '
        <!-- Panel de creación de receta completa -->
        <div class="card" style="margin-top: 2rem; background: #f0f9ff; border: 2px solid #bae6fd;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="color: #0369a1; margin: 0;">
                    <i class="fas fa-prescription"></i> Crear Receta Completa para esta Consulta
                </h3>
                <span class="badge" style="background: #0369a1; color: white;">Opcional</span>
            </div>
            <p style="color: #475569; margin-bottom: 1.5rem;">
                Puedes crear una receta médica completa con múltiples medicamentos o solicitar análisis. Si prefieres crear la receta después, puedes hacerlo desde la vista de la consulta.
            </p>
            <!-- Información del Seguro -->
            <div class="card" style="margin-bottom: 2rem; background: white;">
                <h4 style="color: var(--primary); margin-bottom: 1rem;">
                    <i class="fas fa-shield-alt"></i> Información del Seguro Médico
                </h4>
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
            <!-- Selector de Tipo de Receta -->
            <div class="card" style="margin-bottom: 2rem; background: white;">
                <h4 style="color: var(--primary); margin-bottom: 1rem;">
                    <i class="fas fa-file-medical-alt"></i> Tipo de Receta
                </h4>
                <div class="form-group">
                    <div class="grid grid-2" style="gap: 1rem;">
                        <div>
                            <input type="radio" name="tipo_receta" id="tipo_medicamento" value="medicamento" checked onchange="cambiarTipoReceta()">
                            <label for="tipo_medicamento" style="margin-left: 0.5rem; font-weight: 600;">
                                <i class="fas fa-pills"></i> Medicamentos
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="tipo_receta" id="tipo_analisis" value="analisis" onchange="cambiarTipoReceta()">
                            <label for="tipo_analisis" style="margin-left: 0.5rem; font-weight: 600;">
                                <i class="fas fa-microscope"></i> Análisis
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sección de Medicamentos (Visible por defecto) -->
            <div id="seccionMedicamentos" class="card" style="background: white; display: block;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: var(--primary); margin: 0;">
                        <i class="fas fa-pills"></i> Medicamentos Recetados
                    </h4>
                    <button type="button" class="btn btn-primary" onclick="agregarMedicamento()">
                        <i class="fas fa-plus"></i> Agregar Medicamento
                    </button>
                </div>
                <div id="medicamentosContainer">
                    <div class="medicamento-item card" style="margin-bottom: 1rem; border: 2px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h5 style="color: var(--primary); margin: 0;">Medicamento #1</h5>
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMedicamento(this)" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="grid grid-2" style="gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Medicamento *</label>
                                <input type="text" name="medicamentos[0][nombre]" class="form-control" 
                                       placeholder="Ej: Paracetamol 500mg" data-required="true">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Dosis *</label>
                                <input type="text" name="medicamentos[0][dosis]" class="form-control" 
                                       placeholder="Ej: 1 tableta" data-required="true">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Frecuencia *</label>
                                <input type="text" name="medicamentos[0][frecuencia]" class="form-control" 
                                       placeholder="Ej: Cada 8 horas" data-required="true">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Duración *</label>
                                <input type="text" name="medicamentos[0][duracion]" class="form-control" 
                                       placeholder="Ej: 7 días" data-required="true">
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
            <!-- Sección de Análisis (Oculta por defecto) -->
            <div id="seccionAnalisis" class="card" style="background: white; display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: var(--info); margin: 0;">
                        <i class="fas fa-microscope"></i> Análisis Solicitados
                    </h4>
                    <button type="button" class="btn btn-info" onclick="agregarAnalisis()">
                        <i class="fas fa-plus"></i> Agregar Análisis
                    </button>
                </div>
                <div id="analisisContainer">
                    <div class="analisis-item card" style="margin-bottom: 1rem; border: 2px solid var(--info);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h5 style="color: var(--info); margin: 0;">Análisis #1</h5>
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarAnalisis(this)" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="grid grid-2" style="gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Tipo de Análisis *</label>
                                <select name="analisis[0][tipo]" class="form-control" data-required="true" onchange="toggleAnalisisOtro(this, 0)">
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="Hematología Completa">Hematología Completa</option>
                                    <option value="Perfil Lipídico">Perfil Lipídico</option>
                                    <option value="Perfil Tiroideo">Perfil Tiroideo</option>
                                    <option value="Glucosa">Glucosa</option>
                                    <option value="Creatinina">Creatinina</option>
                                    <option value="Ácido Úrico">Ácido Úrico</option>
                                    <option value="Transaminasas">Transaminasas</option>
                                    <option value="Ultrasonido Pélvico">Ultrasonido Pélvico</option>
                                    <option value="Ultrasonido Mamario">Ultrasonido Mamario</option>
                                    <option value="Papanicolaou">Papanicolaou</option>
                                    <option value="Colposcopia">Colposcopia</option>
                                    <option value="Biopsia">Biopsia</option>
                                    <option value="Cultivo Vaginal">Cultivo Vaginal</option>
                                    <option value="Prueba de Embarazo">Prueba de Embarazo</option>
                                    <option value="Pruebas de ETS">Pruebas de ETS</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group" id="analisisOtroContainer_0" style="display: none;">
                                <label class="form-label">Especificar Análisis</label>
                                <input type="text" name="analisis[0][otro_tipo]" class="form-control" placeholder="Especificar tipo de análisis">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Indicaciones Especiales</label>
                                <textarea name="analisis[0][indicaciones]" class="form-control" rows="3" 
                                          placeholder="Indicaciones para el análisis..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Instrucciones Generales -->
            <div class="card" style="background: white;">
                <h4 style="color: var(--primary); margin-bottom: 1rem;">
                    <i class="fas fa-stethoscope"></i> Instrucciones Generales
                </h4>
                <div class="form-group">
                    <textarea name="instrucciones_generales" class="form-control" rows="4" 
                              placeholder="Instrucciones generales para el paciente..."></textarea>
                </div>
            </div>
        </div>

        <script>
        let medicamentoCount = 1;
        let analisisCount = 1;
        
        function cambiarTipoReceta() {
            const tipoMedicamento = document.getElementById("tipo_medicamento").checked;
            const seccionMedicamentos = document.getElementById("seccionMedicamentos");
            const seccionAnalisis = document.getElementById("seccionAnalisis");
            if (tipoMedicamento) {
                seccionMedicamentos.style.display = "block";
                seccionAnalisis.style.display = "none";
                // Actualizar atributos required
                actualizarAtributosRequired();
            } else {
                seccionMedicamentos.style.display = "none";
                seccionAnalisis.style.display = "block";
                // Actualizar atributos required
                actualizarAtributosRequired();
            }
        }
        
        // Función para manejar dinámicamente los atributos required
        function actualizarAtributosRequired() {
            const tipoMedicamento = document.getElementById("tipo_medicamento").checked;
            const camposMedicamentos = document.querySelectorAll(\'[data-required="true"]\');
            const selectsAnalisis = document.querySelectorAll(\'select[data-required="true"]\');
            
            if (tipoMedicamento) {
                // Si es medicamento, hacer required los campos de medicamentos
                camposMedicamentos.forEach(campo => {
                    campo.setAttribute(\'required\', \'required\');
                });
                // Remover required de análisis
                selectsAnalisis.forEach(select => {
                    select.removeAttribute(\'required\');
                });
            } else {
                // Si es análisis, hacer required los selects de análisis
                selectsAnalisis.forEach(select => {
                    select.setAttribute(\'required\', \'required\');
                });
                // Remover required de medicamentos
                camposMedicamentos.forEach(campo => {
                    campo.removeAttribute(\'required\');
                });
            }
        }
        
        // Funciones para Medicamentos
        function agregarMedicamento() {
            const container = document.getElementById("medicamentosContainer");
            const newItem = document.createElement("div");
            newItem.className = "medicamento-item card";
            newItem.style.cssText = "margin-bottom: 1rem; border: 2px solid var(--success);";
            newItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h5 style="color: var(--success); margin: 0;">Medicamento #${medicamentoCount + 1}</h5>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMedicamento(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-2" style="gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Medicamento *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][nombre]" class="form-control" 
                               placeholder="Ej: Paracetamol 500mg" data-required="true">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dosis *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][dosis]" class="form-control" 
                               placeholder="Ej: 1 tableta" data-required="true">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frecuencia *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][frecuencia]" class="form-control" 
                               placeholder="Ej: Cada 8 horas" data-required="true">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración *</label>
                        <input type="text" name="medicamentos[${medicamentoCount}][duracion]" class="form-control" 
                               placeholder="Ej: 7 días" data-required="true">
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
            actualizarAtributosRequired();
        }
        
        function eliminarMedicamento(button) {
            const item = button.closest(".medicamento-item");
            item.remove();
            reordenarMedicamentos();
            actualizarAtributosRequired();
        }
        
        function reordenarMedicamentos() {
            const items = document.querySelectorAll(".medicamento-item");
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
            medicamentoCount = items.length;
        }
        
        // Funciones para Análisis
        function agregarAnalisis() {
            const container = document.getElementById("analisisContainer");
            const newItem = document.createElement("div");
            newItem.className = "analisis-item card";
            newItem.style.cssText = "margin-bottom: 1rem; border: 2px solid var(--info);";
            newItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h5 style="color: var(--info); margin: 0;">Análisis #${analisisCount + 1}</h5>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarAnalisis(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-2" style="gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Tipo de Análisis *</label>
                        <select name="analisis[${analisisCount}][tipo]" class="form-control" data-required="true" onchange="toggleAnalisisOtro(this, ${analisisCount})">
                            <option value="">Seleccionar tipo...</option>
                            <option value="Hematología Completa">Hematología Completa</option>
                            <option value="Perfil Lipídico">Perfil Lipídico</option>
                            <option value="Perfil Tiroideo">Perfil Tiroideo</option>
                            <option value="Glucosa">Glucosa</option>
                            <option value="Creatinina">Creatinina</option>
                            <option value="Ácido Úrico">Ácido Úrico</option>
                            <option value="Transaminasas">Transaminasas</option>
                            <option value="Ultrasonido Pélvico">Ultrasonido Pélvico</option>
                            <option value="Ultrasonido Mamario">Ultrasonido Mamario</option>
                            <option value="Papanicolaou">Papanicolaou</option>
                            <option value="Colposcopia">Colposcopia</option>
                            <option value="Biopsia">Biopsia</option>
                            <option value="Cultivo Vaginal">Cultivo Vaginal</option>
                            <option value="Prueba de Embarazo">Prueba de Embarazo</option>
                            <option value="Pruebas de ETS">Pruebas de ETS</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group" id="analisisOtroContainer_${analisisCount}" style="display: none;">
                        <label class="form-label">Especificar Análisis</label>
                        <input type="text" name="analisis[${analisisCount}][otro_tipo]" class="form-control" placeholder="Especificar tipo de análisis">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Indicaciones Especiales</label>
                        <textarea name="analisis[${analisisCount}][indicaciones]" class="form-control" rows="3" 
                                  placeholder="Indicaciones para el análisis..."></textarea>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
            analisisCount++;
            actualizarAtributosRequired();
        }
        
        function eliminarAnalisis(button) {
            const item = button.closest(".analisis-item");
            item.remove();
            reordenarAnalisis();
            actualizarAtributosRequired();
        }
        
        function reordenarAnalisis() {
            const items = document.querySelectorAll(".analisis-item");
            items.forEach((item, index) => {
                const title = item.querySelector("h5");
                title.textContent = "Análisis #" + (index + 1);
                title.style.color = index === 0 ? "var(--info)" : "var(--info)";
                // Actualizar los names de los inputs y IDs
                const inputs = item.querySelectorAll(\'[name^="analisis["]\');
                inputs.forEach(input => {
                    const name = input.getAttribute("name");
                    const newName = name.replace(/analisis\\[\\d+\\]/, "analisis[" + index + "]");
                    input.setAttribute("name", newName);
                    // Actualizar onchange para selects
                    if (input.tagName === "SELECT") {
                        input.setAttribute("onchange", "toggleAnalisisOtro(this, " + index + ")");
                    }
                });
                // Actualizar contenedor de "Otro"
                const otroContainer = item.querySelector(\'[id^="analisisOtroContainer_"]\');
                if (otroContainer) {
                    otroContainer.id = "analisisOtroContainer_" + index;
                }
            });
            analisisCount = items.length;
        }
        
        function toggleAnalisisOtro(select, index) {
            const otroContainer = document.getElementById("analisisOtroContainer_" + index);
            if (select.value === "Otro") {
                otroContainer.style.display = "block";
            } else {
                otroContainer.style.display = "none";
            }
        }
        
        // Manejo del seguro médico
        document.getElementById("seguroSelect").addEventListener("change", function() {
            const otroContainer = document.getElementById("otroSeguroContainer");
            otroContainer.style.display = this.value === "Otro" ? "block" : "none";
        });
        
        // Validación del formulario de receta
        document.getElementById("consultaForm").addEventListener("submit", function(e) {
            const medicamentos = document.querySelectorAll(".medicamento-item");
            const analisis = document.querySelectorAll(".analisis-item");
            const tipoReceta = document.querySelector(\'input[name="tipo_receta"]:checked\').value;
            
            // Si no hay ni medicamentos ni análisis, permitir enviar sin problemas
            if (medicamentos.length === 0 && analisis.length === 0) {
                return; // No hay validación necesaria
            }
        
            // Validar según el tipo de receta seleccionado
            if (tipoReceta === "medicamento") {
                // Solo validar medicamentos
                if (medicamentos.length === 0) {
                    e.preventDefault();
                    alert("Debe agregar al menos un medicamento cuando el tipo de receta es Medicamentos.");
                    return;
                }
        
                let todosValidos = true;
                medicamentos.forEach((item, index) => {
                    const inputs = item.querySelectorAll(\'[data-required="true"]\');
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
                    return;
                }
        
                // Si hay análisis pero el tipo es medicamento, ignorarlos
                if (analisis.length > 0) {
                    if (!confirm("Ha seleccionado tipo \'Medicamentos\' pero también ha agregado análisis. ¿Desea continuar guardando solo los medicamentos?")) {
                        e.preventDefault();
                        return;
                    }
                }
        
            } else if (tipoReceta === "analisis") {
                // Solo validar análisis
                if (analisis.length === 0) {
                    e.preventDefault();
                    alert("Debe agregar al menos un análisis cuando el tipo de receta es Análisis.");
                    return;
                }
        
                let todosValidos = true;
                analisis.forEach((item, index) => {
                    const selects = item.querySelectorAll(\'select[data-required="true"]\');
                    selects.forEach(select => {
                        if (!select.value.trim()) {
                            select.style.borderColor = "var(--error)";
                            todosValidos = false;
                        } else {
                            select.style.borderColor = "";
                        }
                    });
                });
        
                if (!todosValidos) {
                    e.preventDefault();
                    alert("Por favor complete todos los campos requeridos en los análisis");
                    return;
                }
        
                // Si hay medicamentos pero el tipo es análisis, ignorarlos
                if (medicamentos.length > 0) {
                    if (!confirm("Ha seleccionado tipo \'Análisis\' pero también ha agregado medicamentos. ¿Desea continuar guardando solo los análisis?")) {
                        e.preventDefault();
                        return;
                    }
                }
            }
        });
        
        // Inicializar función para el primer análisis
        function initAnalisis() {
            const primerSelect = document.querySelector(\'[name="analisis[0][tipo]"\');
            if (primerSelect) {
                primerSelect.addEventListener("change", function() {
                    toggleAnalisisOtro(this, 0);
                });
            }
        }
        
        // Ejecutar inicialización cuando el DOM esté listo
        document.addEventListener("DOMContentLoaded", function() {
            initAnalisis();
            actualizarAtributosRequired(); // Inicializar atributos required
        });
        </script>
        ';
    }

    private function renderModalCIE10()
    {
        return '
        <!-- Modal para búsqueda CIE-10 -->
        <div id="cie10Modal" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 800px;">
                <div class="modal-header">
                    <h3>Búsqueda de Diagnósticos CIE-10</h3>
                    <button type="button" class="close" onclick="cerrarModalCIE10()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" id="cie10BuscarInput" class="form-control" 
                               placeholder="Buscar por código o descripción..." 
                               onkeyup="buscarCIE10()">
                    </div>
                    <div class="form-group">
                        <label>Categorías:</label>
                        <select id="cie10Categoria" class="form-control" onchange="filtrarPorCategoria()">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    <div id="cie10Resultados" style="max-height: 400px; overflow-y: auto; margin-top: 1rem;">
                        <!-- Los resultados se cargarán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="cerrarModalCIE10()">Cancelar</button>
                </div>
            </div>
        </div>
        ';
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos requeridos
                if (empty($_POST['paciente_id']) || empty($_POST['fecha_consulta'])) {
                    throw new Exception("Paciente y fecha de consulta son requeridos");
                }

                // Crear la consulta
                $data = [
                    'paciente_id' => $_POST['paciente_id'],
                    'usuario_id' => $_SESSION['usuario']['id'],
                    'fecha_consulta' => $_POST['fecha_consulta'],
                    'sintomas' => $_POST['sintomas'] ?? '',
                    'diagnostico' => $_POST['diagnostico'] ?? '',
                    'tratamiento' => $_POST['tratamiento'] ?? '',
                    'indicaciones' => $_POST['indicaciones'] ?? '',
                    'notas' => $_POST['notas'] ?? '',
                    'proxima_visita' => $_POST['proxima_visita'] ?? null
                ];

                // Si se seleccionó un código CIE-10, guardarlo también
                if (!empty($_POST['cie10_codigo'])) {
                    $data['cie10_codigo'] = $_POST['cie10_codigo'];
                }

                $consultaId = $this->consultaModel->createAndGetId($data);

                if ($consultaId) {
                    // Procesar archivos subidos
                    $archivosSubidos = $this->procesarArchivos($consultaId, $_POST['paciente_id']);

                    // Procesar receta completa si se proporcionaron medicamentos o análisis
                    $tieneMedicamentos = isset($_POST['medicamentos']) && is_array($_POST['medicamentos']) && count($_POST['medicamentos']) > 0;
                    $tieneAnalisis = isset($_POST['analisis']) && is_array($_POST['analisis']) && count($_POST['analisis']) > 0;
                    $tipoReceta = $_POST['tipo_receta'] ?? 'medicamento';
                    
                    $recetaId = null;
                    
                    // DEBUG: Verificar qué datos están llegando
                    error_log("Tipo receta: " . $tipoReceta);
                    error_log("Tiene medicamentos: " . ($tieneMedicamentos ? 'Sí' : 'No'));
                    error_log("Tiene análisis: " . ($tieneAnalisis ? 'Sí' : 'No'));
                    
                    // Solo crear receta si hay elementos del tipo seleccionado
                    if (($tipoReceta === 'medicamento' && $tieneMedicamentos) || 
                        ($tipoReceta === 'analisis' && $tieneAnalisis)) {
                        $recetaId = $this->crearRecetaCompletaDesdeConsulta($consultaId, $_POST['paciente_id']);
                        error_log("Receta creada con ID: " . $recetaId);
                    }

                    $mensaje = "Consulta creada exitosamente";
                    if ($archivosSubidos > 0) {
                        $mensaje .= " y " . $archivosSubidos . " archivo" . ($archivosSubidos != 1 ? 's' : '') . " subido" . ($archivosSubidos != 1 ? 's' : '');
                    }

                    // Redirigir según si se creó receta o no
                    if ($recetaId) {
                        $_SESSION['success'] = $mensaje . ". Puede imprimir la receta ahora.";
                        header('Location: ' . BASE_URL . '/recetas/ver/' . $recetaId);
                    } else {
                        $_SESSION['success'] = $mensaje;
                        header('Location: ' . BASE_URL . '/consultas/ver/' . $consultaId);
                    }
                    exit;
                } else {
                    throw new Exception("Error al crear la consulta");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                error_log("Error en crear consulta: " . $e->getMessage());
                header('Location: ' . BASE_URL . '/consultas/nueva');
                exit;
            }
        } else {
            header('Location: ' . BASE_URL . '/consultas/nueva');
            exit;
        }
    }

    private function crearRecetaCompletaDesdeConsulta($consulta_id, $paciente_id)
    {
        try {
            $consultorio = $this->consultorioModel->getById($_SESSION['usuario']['consultorio_id']);
            $medico = $this->usuarioModel->getById($_SESSION['usuario']['id']);
            
            // Validar que el consultorio tenga exequatur
            if (empty($consultorio['medico_exequatur'])) {
                throw new Exception("El exequatur del médico no está configurado. Por favor, configure el consultorio primero.");
            }

            // Generar número de receta único
            $numero_receta = 'REC-' . date('Ymd') . '-' . str_pad($consulta_id, 4, '0', STR_PAD_LEFT);

            // Determinar el seguro médico
            $seguro_medico = $_POST['seguro_medico'] ?? '';
            if ($seguro_medico === 'Otro') {
                $seguro_medico = $_POST['seguro_otro'] ?? 'Otro';
            }

            // Determinar el tipo de receta basado en la selección del usuario
            $tipo_receta = $_POST['tipo_receta'] ?? 'medicamento';

            // Crear la receta principal
            $recetaData = [
                'consultorio_id' => $consultorio['id'],
                'medico_id' => $medico['id'],
                'paciente_id' => $paciente_id,
                'consulta_id' => $consulta_id,
                'seguro_medico' => $seguro_medico,
                'numero_receta' => $numero_receta,
                'fecha_emision' => date('Y-m-d'),
                'instrucciones' => $_POST['instrucciones_generales'] ?? '',
                'tipo_receta' => $tipo_receta
            ];

            $receta_id = $this->recetaModel->createAndGetId($recetaData);

            if ($receta_id) {
                // Guardar SOLO los medicamentos si el tipo es medicamento
                if ($tipo_receta === 'medicamento' && isset($_POST['medicamentos']) && is_array($_POST['medicamentos'])) {
                    foreach ($_POST['medicamentos'] as $medicamento) {
                        if (!empty($medicamento['nombre'])) {
                            $medData = [
                                'receta_id' => $receta_id,
                                'medicamento' => $medicamento['nombre'],
                                'dosis' => $medicamento['dosis'],
                                'frecuencia' => $medicamento['frecuencia'],
                                'duracion' => $medicamento['duracion'],
                                'instrucciones' => $medicamento['instrucciones'] ?? '',
                                'tipo_item' => 'medicamento'
                            ];
                            $this->recetaModel->createMedicamento($medData);
                        }
                    }
                }

                // Guardar SOLO los análisis si el tipo es análisis
                if ($tipo_receta === 'analisis' && isset($_POST['analisis']) && is_array($_POST['analisis'])) {
                    foreach ($_POST['analisis'] as $analisis) {
                        if (!empty($analisis['tipo'])) {
                            $tipo_analisis = $analisis['tipo'];
                            if ($tipo_analisis === 'Otro' && !empty($analisis['otro_tipo'])) {
                                $tipo_analisis = $analisis['otro_tipo'];
                            }
                            
                            $analisisData = [
                                'receta_id' => $receta_id,
                                'medicamento' => $tipo_analisis,
                                'dosis' => '', // No aplica para análisis
                                'frecuencia' => '', // No aplica para análisis
                                'duracion' => '', // No aplica para análisis
                                'instrucciones' => $analisis['indicaciones'] ?? '',
                                'tipo_item' => 'analisis'
                            ];
                            $this->recetaModel->createMedicamento($analisisData);
                        }
                    }
                }
                return $receta_id;
            } else {
                throw new Exception("Error al generar la receta");
            }
        } catch (Exception $e) {
            // Log the error but don't stop the consulta creation
            error_log("Error creando receta desde consulta: " . $e->getMessage());
            return false;
        }
    }

    public function ver($id)
    {
        $consulta = $this->consultaModel->getByIdWithPaciente($id);
        if (!$consulta) {
            $_SESSION['error'] = "Consulta no encontrada";
            header('Location: ' . BASE_URL . '/consultas');
            exit;
        }
        $documentos = $this->documentoModel->getByConsulta($id);
        $tieneReceta = $this->recetaModel->getByConsulta($id);
        $content = '
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Consulta - ' . htmlspecialchars($consulta['paciente_nombre']) . '</h1>
                <div>
                    ' . (!$tieneReceta ? '
                    <a href="' . BASE_URL . '/recetas/crear/' . $consulta['id'] . '" class="btn btn-success">
                        <i class="fas fa-prescription"></i> Generar Receta
                    </a>
                    ' : '
                    <a href="' . BASE_URL . '/recetas/ver/' . $tieneReceta['id'] . '" class="btn btn-info">
                        <i class="fas fa-file-medical"></i> Ver Receta
                    </a>
                    ') . '
                    <a href="' . BASE_URL . '/consultas" class="btn" style="background: #718096; color: white; margin-left: 0.5rem;">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="grid grid-2">
                <div>
                    <h3>Información de la Consulta</h3>
                    <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($consulta['fecha_consulta'])) . '</p>
                    <p><strong>Paciente:</strong> ' . htmlspecialchars($consulta['paciente_nombre']) . '</p>
                    <p><strong>Cédula:</strong> ' . htmlspecialchars($consulta['cedula']) . '</p>
                    <p><strong>Médico:</strong> ' . htmlspecialchars($consulta['medico_nombre']) . '</p>
                    ' . ($consulta['proxima_visita'] ? '<p><strong>Próxima visita:</strong> ' . date('d/m/Y', strtotime($consulta['proxima_visita'])) . '</p>' : '') . '
                </div>
                <div>
                    <h3>Detalles Médicos</h3>
                    ' . ($consulta['sintomas'] ? '<p><strong>Síntomas:</strong> ' . nl2br(htmlspecialchars($consulta['sintomas'])) . '</p>' : '') . '
                    ' . ($consulta['diagnostico'] ? '<p><strong>Diagnóstico:</strong> ' . nl2br(htmlspecialchars($consulta['diagnostico'])) . '</p>' : '') . '
                    ' . ($consulta['tratamiento'] ? '<p><strong>Tratamiento:</strong> ' . nl2br(htmlspecialchars($consulta['tratamiento'])) . '</p>' : '') . '
                </div>
            </div>
            ' . ($consulta['indicaciones'] ? '
            <div class="form-group" style="margin-top: 1rem;">
                <strong>Indicaciones:</strong>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-top: 0.5rem;">
                    ' . nl2br(htmlspecialchars($consulta['indicaciones'])) . '
                </div>
            </div>' : '') . '
            ' . ($consulta['notas'] ? '
            <div class="form-group" style="margin-top: 1rem;">
                <strong>Notas Adicionales:</strong>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-top: 0.5rem;">
                    ' . nl2br(htmlspecialchars($consulta['notas'])) . '
                </div>
            </div>' : '') . '
        </div>
        <!-- Documentos Adjuntos -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3><i class="fas fa-paperclip"></i> Documentos Adjuntos</h3>
                <span class="badge" style="background: var(--primary); color: white;">
                    ' . count($documentos) . ' archivo' . (count($documentos) != 1 ? 's' : '') . '
                </span>
            </div>
        ';
        if (empty($documentos)) {
            $content .= '
                <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                    <i class="fas fa-file-upload" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No hay documentos adjuntos para esta consulta</p>
                </div>
            ';
        } else {
            $content .= '
                <div class="grid grid-2">
            ';
            foreach ($documentos as $documento) {
                $icono = $this->getFileIcon($documento['nombre_archivo']);
                $content .= '
                    <div class="documento-item" style="border: 1px solid var(--border); border-radius: 0.5rem; padding: 1rem; background: white;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <i class="' . $icono . '" style="font-size: 2rem; color: var(--primary);"></i>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">' . htmlspecialchars($documento['nombre_original'] ?? $documento['nombre_archivo']) . '</div>
                                <div style="font-size: 0.8rem; color: var(--text-light);">
                                    ' . $this->getFileType($documento['nombre_archivo']) . ' • ' .
                    date('d/m/Y H:i', strtotime($documento['created_at'])) . '
                                </div>
                            </div>
                        </div>
                        ' . ($documento['descripcion'] ? '<div style="color: var(--text); font-size: 0.9rem; margin-bottom: 1rem;">' . htmlspecialchars($documento['descripcion']) . '</div>' : '') . '
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="' . BASE_URL . '/files/' . $documento['ruta_archivo'] . '" 
                               class="btn btn-primary" 
                               target="_blank" 
                               style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                            <a href="' . BASE_URL . '/files/' . $documento['ruta_archivo'] . '" 
                               class="btn" 
                               target="_blank"
                               style="background: var(--info); color: white; padding: 0.5rem 1rem; font-size: 0.8rem;">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </div>
                    </div>
                ';
            }
            $content .= '
                </div>
            ';
        }
        $content .= '
        </div>
        ';
        $this->renderWithLayout($content);
    }

    private function procesarArchivos($consultaId, $pacienteId)
    {
        $archivosSubidos = 0;
        if (isset($_FILES['documentos']) && is_array($_FILES['documentos']['name'])) {
             $uploadDir = __DIR__ . '/../../uploads/';
            // Crear directorio si no existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Crear archivo .htaccess para proteger el directorio
            $htaccessContent = "Order Deny,Allow
Deny from all
";
            file_put_contents($uploadDir . '.htaccess', $htaccessContent);
            $descripcion = $_POST['descripcion_documentos'] ?? 'Documentos de consulta';
            foreach ($_FILES['documentos']['name'] as $key => $name) {
                if ($_FILES['documentos']['error'][$key] === UPLOAD_ERR_OK) {
                    $tempFile = $_FILES['documentos']['tmp_name'][$key];
                    $fileExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
                    $targetFile = $uploadDir . $newFileName;
                    // Validar tipo de archivo
                    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx', 'txt'];
                    if (!in_array($fileExt, $allowedTypes)) {
                        continue;
                    }
                    // Validar tamaño (10MB máximo)
                    if ($_FILES['documentos']['size'][$key] > 10 * 1024 * 1024) {
                        continue;
                    }
                    if (move_uploaded_file($tempFile, $targetFile)) {
                        // Guardar en base de datos
                        $documentoData = [
                            'paciente_id' => $pacienteId,
                            'consulta_id' => $consultaId,
                            'tipo' => 'analitica',
                            'nombre_archivo' => $newFileName,
                            'ruta_archivo' => $newFileName,
                            'descripcion' => $descripcion
                        ];
                        if ($this->documentoModel->create($documentoData)) {
                            $archivosSubidos++;
                        }
                    }
                }
            }
        }
        return $archivosSubidos;
    }

    private function getFileIcon($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'pdf':
                return 'fas fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'fas fa-file-image';
            case 'txt':
                return 'fas fa-file-alt';
            default:
                return 'fas fa-file';
        }
    }

    private function getFileType($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'pdf':
                return 'Documento PDF';
            case 'doc':
            case 'docx':
                return 'Documento Word';
            case 'xls':
            case 'xlsx':
                return 'Hoja de cálculo';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'Imagen';
            case 'txt':
                return 'Archivo de texto';
            default:
                return 'Archivo';
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
            <title>Consultorio Ginecológico - Consultas</title>
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
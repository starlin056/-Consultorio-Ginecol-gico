<?php
$content = '
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
            <div class="stats-number">' . $consultasHoy . '</div>
            <div class="stats-label">Consultas Hoy</div>
        </div>
        
        <div class="stats-card">
            <div class="stats-number">' . $proximasCitas . '</div>
            <div class="stats-label">Próximas Citas</div>
        </div>
    </div>

    <div class="card">
        <h2>Acciones Rápidas</h2>
        <div class="grid grid-2">
            <a href="/pacientes/crear" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nuevo Paciente
            </a>
            <a href="/consultas/nueva" class="btn btn-primary">
                <i class="fas fa-stethoscope"></i> Nueva Consulta
            </a>
            <a href="/pacientes/buscar" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar Paciente
            </a>
            <a href="/reportes" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        </div>
    </div>
</div>
';

require_once 'layout.php';
?>
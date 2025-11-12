<?php
$current = $_SERVER['REQUEST_URI'];
$clean_path = explode('?', $current)[0];
?>

<nav class="nav-wrapper">
    <div class="nav-menu">
        <div class="nav-brand">
            <i class="fas fa-heartbeat"></i>
            <span>Consultorio Ginecológico</span>
        </div>

        <ul class="nav-links">
            <li class="nav-item <?= str_contains($clean_path, '/dashboard') || $clean_path === '/' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/dashboard" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
            </li>
            
            <li class="nav-item <?= str_contains($clean_path, '/pacientes') ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/pacientes" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Pacientes</span>
                </a>
            </li>
            
            <li class="nav-item <?= str_contains($clean_path, '/consultas') ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/consultas" class="nav-link">
                    <i class="fas fa-stethoscope"></i>
                    <span>Consultas</span>
                </a>
            </li>

            <li class="nav-item <?= str_contains($clean_path, '/recetas') ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/recetas" class="nav-link">
                    <i class="fas fa-prescription"></i>
                    <span>Recetas</span>
                </a>
            </li>
            
            <li class="nav-item <?= str_contains($clean_path, '/ajustes-receta') ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/ajustes-receta" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Ajustes Receta</span>
                </a>
            </li>

            <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'administrador'): ?>
                <li class="nav-item <?= str_contains($clean_path, '/usuarios') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/usuarios" class="nav-link">
                        <i class="fas fa-users-cog"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item <?= str_contains($clean_path, '/reportes') ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/reportes" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?= BASE_URL ?>/logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>

        <div class="active-element"></div>
    </div>
</nav>
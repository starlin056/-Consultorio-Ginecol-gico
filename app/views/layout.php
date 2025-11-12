
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio Ginecológico</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php if (isset($_SESSION['usuario'])): ?>
    <nav class="navbar">
        <div class="nav-brand">
            <i class="fas fa-heartbeat"></i>
            <span>Consultorio Ginecológico</span>
        </div>
        <div class="nav-links">
            <a href="/dashboard" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
            <a href="/pacientes" class="nav-link"><i class="fas fa-users"></i> Pacientes</a>
            <a href="/consultas" class="nav-link"><i class="fas fa-stethoscope"></i> Consultas</a>
            <a href="/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </nav>
    <?php endif; ?>

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

        <?php echo $content; ?>
    </main>

    <script src="/assets/js/main.js"></script>
</body>
</html>
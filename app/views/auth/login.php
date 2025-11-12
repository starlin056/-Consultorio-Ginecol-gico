<?php
$content = '
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-heartbeat"></i>
            <h1>Consultorio Ginecológico</h1>
            <p>Iniciar Sesión</p>
        </div>
        
        <form method="POST" action="/auth/login" class="login-form">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Ingresar
            </button>
        </form>
    </div>
</div>
';

require_once 'layout.php';
?>
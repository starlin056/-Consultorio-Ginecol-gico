<?php
// app/controllers/AuthController.php
class AuthController {
    private $usuarioModel;

    public function __construct() {
        require_once __DIR__ . '/../models/UsuarioModel.php';
        $this->usuarioModel = new UsuarioModel();
    }

    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $this->renderLogin();
    }

   public function handleLogin() {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $usuario = $this->usuarioModel->getByEmail($email);
    
    if ($usuario) {
        // Verificar si el usuario está activo
        if (!$usuario['activo']) {
            $_SESSION['error'] = "Usuario inactivo. Contacte al administrador.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Verificar fecha de expiración si existe
        if ($usuario['fecha_expiracion'] && strtotime($usuario['fecha_expiracion']) < time()) {
            $_SESSION['error'] = "La licencia de usuario ha expirado";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // ✅ VERIFICACIÓN CORREGIDA: Solo password_verify
        if (password_verify($password, $usuario['password'])) {
            // Login exitoso
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'consultorio_id' => $usuario['consultorio_id']
            ];
            
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        } else {
            $_SESSION['error'] = "Contraseña incorrecta";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    } else {
        $_SESSION['error'] = "No existe un usuario con ese email";
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    private function renderLogin() {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Consultorio Ginecológico - Login</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --primary: #8B5FBF;
                    --primary-dark: #6B46C1;
                    --secondary: #F7FAFC;
                    --accent: #ED64A6;
                    --text: #2D3748;
                    --text-light: #718096;
                    --border: #E2E8F0;
                    --success: #48BB78;
                    --error: #F56565;
                    --warning: #ECC94B;
                }

                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Inter', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    color: var(--text);
                }

                .login-container {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem;
                }
                
                .login-card {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border-radius: 1rem;
                    padding: 3rem;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    width: 100%;
                    max-width: 400px;
                }
                
                .login-header {
                    text-align: center;
                    margin-bottom: 2rem;
                }
                
                .login-header i {
                    font-size: 3rem;
                    color: #8B5FBF;
                    margin-bottom: 1rem;
                }
                
                .login-header h1 {
                    color: #2D3748;
                    margin-bottom: 0.5rem;
                    font-size: 1.5rem;
                }
                
                .login-header p {
                    color: #718096;
                    font-size: 1rem;
                }
                
                .login-form {
                    margin-top: 2rem;
                }

                .form-group {
                    margin-bottom: 1.5rem;
                }

                .form-label {
                    display: block;
                    margin-bottom: 0.5rem;
                    font-weight: 600;
                    color: var(--text);
                }

                .form-control {
                    width: 100%;
                    padding: 0.75rem 1rem;
                    border: 2px solid var(--border);
                    border-radius: 0.5rem;
                    font-size: 1rem;
                    transition: all 0.3s ease;
                }

                .form-control:focus {
                    outline: none;
                    border-color: var(--primary);
                    box-shadow: 0 0 0 3px rgba(139, 95, 191, 0.1);
                }

                .btn {
                    padding: 0.75rem 1.5rem;
                    border: none;
                    border-radius: 0.5rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .btn-primary {
                    background: var(--primary);
                    color: white;
                }

                .btn-primary:hover {
                    background: var(--primary-dark);
                    transform: translateY(-2px);
                }

                .alert {
                    padding: 1rem;
                    border-radius: 0.5rem;
                    margin-bottom: 1rem;
                    font-weight: 500;
                }

                .alert-error {
                    background: #FED7D7;
                    color: #742A2A;
                    border: 1px solid #FEB2B2;
                }

                .demo-credentials {
                    margin-top: 2rem;
                    padding: 1rem;
                    background: #F7FAFC;
                    border-radius: 0.5rem;
                    font-size: 0.9rem;
                    border: 1px solid #E2E8F0;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-heartbeat"></i>
                        <h1>Consultorio Ginecológico</h1>
                        <p>Iniciar Sesión</p>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= BASE_URL ?>/login" class="login-form">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required value="">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-sign-in-alt"></i> Ingresar
                        </button>
                    </form>
                    
                    <div class="demo-credentials">
                        <strong>Credenciales de prueba:</strong><br>
                        Email: <br>
                        Contraseña: 
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
?>
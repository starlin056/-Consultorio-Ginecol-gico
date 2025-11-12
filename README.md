
(`consultorio_ginecologico/README.md`).

---

````markdown
# 🏥 Sistema de Gestión para Consultorios Ginecológicos

[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-green.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Sistema web completo para la gestión de consultorios ginecológicos con historiales médicos, recetas digitales, gestión de pacientes, usuarios y reportes avanzados.  
Desarrollado en **PHP + MySQL** bajo arquitectura **MVC**.

---

## 🚀 Características Principales

### 👥 Gestión de Pacientes
- Registro completo de datos personales y médicos
- Historial clínico vinculado
- Búsqueda avanzada y filtros por nombre, cédula o teléfono
- Expediente digital del paciente

### 🩺 Módulo de Consultas
- Registro y seguimiento de consultas médicas
- Diagnósticos CIE-10 integrados
- Control de próximas visitas
- Relación directa con recetas y análisis

### 💊 Recetas Médicas Digitales
- Creación de recetas profesionales con logotipo y pie personalizado
- Dos tipos de receta: **Medicamentos** y **Análisis**
- Compatible con impresión en PDF
- Gestión centralizada por paciente y por consulta

### 👨‍⚕️ Configuración del Consultorio
- Personalización de datos del médico y del consultorio
- Logo, pie de página, exequátur y especialidad
- Ajustes para recetas y reportes

### 📊 Dashboard y Reportes
- Estadísticas en tiempo real de pacientes y consultas
- Reportes por fechas, médico o tipo de receta
- Gráficos interactivos y métricas clave
- Exportación a PDF o Excel

### 👩‍💻 Gestión de Usuarios y Roles
- Creación y administración de usuarios del sistema
- Roles: **Administrador**, **Médico** y **Recepcionista**
- Control de accesos por permisos
- Activación y expiración de cuentas

### ⚡ Experiencia de Usuario
- Interfaz moderna y responsive
- Menú de navegación con efecto “rayo”
- Notificaciones dinámicas
- Carga rápida y animaciones optimizadas

---

## 🛠️ Tecnologías Utilizadas

| Área | Tecnología |
|------|-------------|
| **Backend** | PHP 8+, MySQL 8 |
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **Librerías** | Bootstrap 5, Font Awesome, GSAP |
| **PDF** | DomPDF (recetas y reportes) |
| **Servidor** | Apache (XAMPP / Hostinger) |

---

## 📦 Instalación

### Requisitos
- PHP 7.4 o superior  
- MySQL 5.7 o superior  
- Extensiones: `pdo`, `mbstring`, `gd`  
- Servidor Apache con `mod_rewrite` habilitado  

---

### 🧩 Pasos de instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tuusuario/consultorio-ginecologico.git
cd consultorio-ginecologico
````

2. **Importar la base de datos**

```sql
-- En phpMyAdmin o consola MySQL
CREATE DATABASE consultorio_ginecologico;
USE consultorio_ginecologico;
SOURCE database/schema.sql;
```

3. **Configurar conexión**
   Edita el archivo `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'consultorio_ginecologico');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/consultorio_ginecologico');
```

4. **Permisos**

```bash
chmod -R 755 public/uploads
```

5. **Acceder al sistema**

```
URL: http://localhost/consultorio_ginecologico
Usuario: admin@consultorio.com
Contraseña: 12345678
```

---

## 🗂️ Estructura del Proyecto

```
consultorio_ginecologico/
├── app/
│   ├── controllers/      # Controladores MVC
│   ├── models/           # Modelos de datos
│   └── views/            # Vistas HTML/PHP
├── config/
│   ├── database.php      # Config BD
│   └── navbar.php        # Barra de navegación
├── database/
│   └── schema.sql        # Script de base de datos
├── public/
│   ├── assets/           # CSS, JS, imágenes
│   └── uploads/          # Archivos subidos
├── index.php             # Punto de entrada
└── .htaccess             # Reescritura de URLs
```

---

## 🔧 Configuración para Producción

### Hostinger o Servidor Propio

```php
define('BASE_URL', 'https://tudominio.com');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
```

Asegúrate de:

* Habilitar `mod_rewrite`
* Subir `/public` como raíz pública
* Proteger carpetas `app/` y `config/` con `.htaccess`

---

## 🎨 Personalización

### Estilos globales

`/public/assets/css/styles.css`

```css
:root {
  --primary: #8B5FBF;
  --primary-dark: #6B46C1;
  --accent: #ED64A6;
  --success: #48BB78;
}
```

### Ajustes de receta

`app/controllers/AjusteRecetaController.php`

```php
$data = [
    'medico_nombre' => 'Dra. Nombre Apellido',
    'medico_exequatur' => '12345',
    'especialidad' => 'Ginecología y Obstetricia'
];
```

---

## 📊 Base de Datos

### Tablas principales

* `usuarios` → Administración de usuarios y roles
* `pacientes` → Información del paciente
* `consultas` → Registro de consultas médicas
* `recetas` → Recetas digitales
* `consultorios` → Configuración del consultorio
* `cie10` → Diagnósticos internacionales

### Ejemplo de estructura:

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  password VARCHAR(255),
  rol ENUM('administrador','medico','recepcionista'),
  activo BOOLEAN DEFAULT 1,
  fecha_expiracion DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔒 Seguridad

* Validación de datos en servidor y cliente
* Hash de contraseñas con `password_hash()`
* Sesiones seguras con expiración controlada
* Prevención de SQL Injection y XSS
* Protección de archivos subidos

---

## 🚀 Despliegue

**1️⃣ En Hostinger**

* Subir todos los archivos
* Configurar la base de datos MySQL
* Importar `schema.sql`
* Ajustar `config/database.php`
* Verificar permisos de `uploads/`

**2️⃣ En XAMPP (local)**

* Carpeta dentro de `htdocs`
* Acceder por `http://localhost/consultorio_ginecologico`

---

## 🤝 Contribución

1. Haz un fork del repositorio
2. Crea una rama feature:

   ```bash
   git checkout -b feature/nueva-funcionalidad
   ```
3. Realiza tus cambios y haz commit
4. Envía un Pull Request con una descripción clara

---

## 📝 Licencia

Proyecto distribuido bajo licencia **MIT**.
Consulta el archivo [LICENSE](LICENSE) para más información.

---

## 🙌 Créditos y Agradecimientos

* **Bootstrap 5** — Framework de CSS
* **Font Awesome** — Iconografía profesional
* **GSAP** — Animaciones fluidas y modernas
* **DomPDF** — Generación de documentos PDF
* **CIE-10 OMS** — Base de datos de diagnósticos médicos

---

**💡 Desarrollado con pasión para consultorios modernos.**
Si te gusta este proyecto, ¡dale una ⭐ en GitHub!

```

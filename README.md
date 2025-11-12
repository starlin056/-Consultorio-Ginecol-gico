# -Consultorio-Ginecol-gico
Sistema web completo para la gestión de consultorios ginecológicos con historiales médicos, recetas digitales, gestión de pacientes y reportes avanzados.

# 📋 **Texto para Invitar a Probar la Demo**

---

## 🎯 **Invitación para Probar el Sistema**

**¡Hola! Te invito a probar nuestro Sistema de Gestión para Consultorios Ginecológicos** 🏥

Estamos desarrollando una plataforma integral para la gestión médica y queremos tu feedback. El sistema incluye historiales médicos, recetas digitales, gestión de pacientes y más.

### 🔑 **Credenciales de Prueba:**
- **URL:** https://easyturnos.com
- **Usuario:** `ejemplo@gmail.com`
- **Contraseña:** `12345678`

### 🚀 **Funcionalidades para Probar:**

1. **📊 Dashboard** - Vista general del consultorio
2. **👥 Gestión de Pacientes** - Agregar, editar y buscar pacientes
3. **🩺 Consultas Médicas** - Registrar consultas con diagnósticos CIE-10
4. **💊 Sistema de Recetas** - Generar recetas médicas personalizables
5. **⚡ Navegación Inteligente** - Efectos visuales modernos
6. **📈 Reportes** - Estadísticas y reportes del consultorio

### 🎨 **Características Destacadas:**
- Interfaz moderna y responsive
- Navegación con efectos de "rayo"
- Recetas médicas profesionales
- Base de datos CIE-10 integrada
- Sistema seguro con roles de usuario

**⏰ Tiempo de prueba:** 5-10 minutos
**📅 Disponibilidad:** 24/7

¡Tu opinión es muy valiosa para nosotros! ¿Podrías probarlo y contarnos tu experiencia?

---

# 📖 **README Completo para GitHub**

```markdown
# 🏥 Sistema de Gestión para Consultorios Ginecológicos

[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-green.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Sistema web completo para la gestión de consultorios ginecológicos con historiales médicos, recetas digitales, gestión de pacientes y reportes avanzados.

![Dashboard Preview](https://via.placeholder.com/800x400/3B82F6/FFFFFF?text=Consultorio+Ginecológico+Dashboard)

## 🚀 Características Principales

### 👥 Gestión de Pacientes
- Registro completo de información de pacientes
- Historial médico integrado
- Búsqueda avanzada y filtros
- Expedientes médicos digitales

### 🩺 Sistema de Consultas
- Registro de consultas médicas
- Diagnósticos con base de datos CIE-10
- Notas de evolución y tratamiento
- Historial de consultas por paciente

### 💊 Recetas Médicas Avanzadas
- Generación de recetas profesionales
- Dos tipos de recetas: Medicamentos y Análisis
- Plantillas personalizables
- Logotipo del consultorio
- Información del médico (exequatur, especialidad)

### 📊 Dashboard y Reportes
- Estadísticas en tiempo real
- Reportes de consultas y pacientes
- Gráficos y métricas del consultorio
- Exportación de datos

### ⚡ Experiencia de Usuario
- Navegación con efectos visuales modernos
- Interfaz responsive (móvil y escritorio)
- Carga rápida y optimizada
- Diseño intuitivo y profesional

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 8.0+, MySQL 8.0
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Librerías:** Bootstrap 5, Font Awesome, GSAP
- **PDF:** DomPDF para generación de recetas
- **Hosting:** Hostinger (optimizado)

## 📦 Instalación

### Requisitos del Sistema
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: PDO, MBString, GD
- Servidor web (Apache/Nginx)

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tuusuario/consultorio-ginecologico.git
cd consultorio-ginecologico
```

2. **Configurar base de datos**
```sql
-- Importar el archivo database/schema.sql
-- Configurar credenciales en config/database.php
```

3. **Configurar permisos**
```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 config/database.php
```

4. **Configurar variables de entorno**
```php
# En config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'consultorio');
define('DB_USER', 'usuario');
define('DB_PASS', 'contraseña');
```

5. **Acceder al sistema**
```
URL: http://tudominio.com
Usuario: ejemplo@gmail.com
Contraseña: 12345678
```

## 🎯 Demo en Vivo

Puedes probar el sistema funcionando en:

**🌐 URL:** [https://easyturnos.com](https://easyturnos.com)

**🔑 Credenciales de Demo:**
- **Usuario:** `ejemplo@gmail.com`
- **Contraseña:** `12345678`

## 📋 Funcionalidades Detalladas

### Módulo de Autenticación
- Login seguro con validación
- Control de sesiones
- Roles de usuario (Administrador/Médico)

### Gestión de Usuarios
- Creación y edición de usuarios
- Asignación de roles
- Control de accesos

### Sistema de Recetas
```php
// Tipos de receta soportados
- Receta de medicamentos
- Receta de análisis clínicos
- Personalización completa
- Impresión profesional
```

### Base de Datos CIE-10
- Búsqueda inteligente de diagnósticos
- Categorías médicas organizadas
- Actualización automática

### Configuración del Consultorio
- Personalización de logo
- Información del médico
- Pie de página personalizado
- Datos de contacto

## 🗂️ Estructura del Proyecto

```
consultorio-ginecologico/
├── app/
│   ├── controllers/         # Controladores MVC
│   ├── models/             # Modelos de datos
│   └── views/              # Vistas (opcional)
├── config/
│   ├── database.php        # Configuración BD
│   └── navbar.php          # Navegación
├── public/
│   ├── assets/
│   │   ├── css/           # Estilos
│   │   ├── js/            # JavaScript
│   │   └── img/           # Imágenes estáticas
│   └── uploads/           # Archivos subidos
├── uploads/               # Archivos (fuera de public)
├── logs/                  # Logs del sistema
├── index.php             # Punto de entrada
└── .htaccess            # Configuración Apache
```

## 🔧 Configuración para Producción

### Hostinger (Recomendado)
```php
# Configuración optimizada para Hostinger
define('BASE_URL', 'https://tudominio.com');
$uploadDir = __DIR__ . '/../../uploads/'; # Fuera de public/
```

### Variables de Entorno
```php
# Production Settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');
```

## 🎨 Personalización

### Modificar Estilos
Editar `public/assets/css/styles.css`:
```css
:root {
    --primary: #3B82F6;    /* Color principal */
    --secondary: #10B981;  /* Color secundario */
    --accent: #F59E0B;     /* Color de acento */
}
```

### Configurar Recetas
En `AjusteRecetaController.php`:
```php
$data = [
    'medico_nombre' => 'Dr. Nombre Completo',
    'medico_exequatur' => '12345',
    'medico_especialidad' => 'Ginecología'
];
```

## 📊 Base de Datos

### Tablas Principales
- `usuarios` - Usuarios del sistema
- `pacientes` - Datos de pacientes
- `consultas` - Registro de consultas
- `recetas` - Recetas médicas
- `consultorios` - Configuración del consultorio
- `cie10` - Catálogo de diagnósticos

### Esquema Principal
```sql
CREATE TABLE consultorios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    logo VARCHAR(500),
    medico_nombre VARCHAR(255),
    medico_exequatur VARCHAR(100),
    pie_pagina TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔒 Seguridad

- Validación de entrada de datos
- Protección contra SQL Injection
- Control de sesiones seguro
- Protección de archivos subidos
- Headers de seguridad HTTP

## 🚀 Despliegue

### Opción 1: Hostinger
1. Subir archivos via FTP/File Manager
2. Crear base de datos MySQL
3. Importar schema.sql
4. Configurar .htaccess
5. Verificar permisos de carpetas

### Opción 2: Servidor Propio
```bash
# Configurar virtual host
# Habilitar mod_rewrite
# Configurar PHP settings
```

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crear una rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📞 Soporte

Si encuentras algún problema o tienes preguntas:

- 📧 Email: soporte@tudominio.com
- 🐛 Issues: [GitHub Issues](https://github.com/tuusuario/consultorio-ginecologico/issues)
- 💬 Discord: [Enlace al servidor]

## 🙏 Agradecimientos

- [Bootstrap](https://getbootstrap.com) por el framework CSS
- [Font Awesome](https://fontawesome.com) por los iconos
- [GSAP](https://gsap.com) por las animaciones
- [DomPDF](https://github.com/dompdf/dompdf) por la generación de PDF

---

**¿Te gusta el proyecto? ¡Dale una ⭐ en GitHub!**
```

---

## 🎯 **Resumen de la Invitación**

**Para enviar a probar:** Usa el primer bloque de texto que incluye las credenciales y descripción breve.

**Para GitHub:** Usa el README completo que proporciona documentación técnica detallada, instrucciones de instalación, y información completa del proyecto.

¿Necesitas que ajuste algo específico en el texto de invitación o en el README?

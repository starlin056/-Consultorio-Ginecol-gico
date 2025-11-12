
# 🏥 Sistema de Gestión para Consultorios Ginecológicos

[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)  
[![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)](https://mysql.com)  
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-green.svg)](https://getbootstrap.com)  
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)  

Sistema web completo para la gestión de consultorios ginecológicos con historiales médicos, recetas digitales, gestión de pacientes, usuarios y reportes avanzados. Desarrollado en PHP + MySQL bajo arquitectura MVC.

---

## 🚀 Características Principales

### 👥 Gestión de Pacientes
- Registro completo de datos personales y médicos.  
- Historial clínico vinculado.  
- Búsqueda avanzada y filtros por nombre, cédula o teléfono.  
- Expediente digital del paciente.

### 🩺 Módulo de Consultas
- Registro y seguimiento de consultas médicas.  
- Diagnósticos CIE-10 integrados.  
- Control de próximas visitas.  
- Relación directa con recetas y análisis.

### 💊 Recetas Médicas Digitales
- Creación de recetas profesionales con logotipo y pie personalizado.  
- Dos tipos de receta: **Medicamentos** y **Análisis**.  
- Compatible con impresión en PDF.  
- Gestión centralizada por paciente y por consulta.

### 👨‍⚕️ Configuración del Consultorio
- Personalización de datos del médico y del consultorio.  
- Logo, pie de página, exequátur y especialidad.  
- Ajustes para recetas y reportes.

### 📊 Dashboard y Reportes
- Estadísticas en tiempo real de pacientes y consultas.  
- Reportes por fechas, médico o tipo de receta.  
- Gráficos interactivos y métricas clave.  
- Exportación a PDF o Excel.

### 👩‍💻 Gestión de Usuarios y Roles
- Creación y administración de usuarios del sistema.  
- Roles: **Administrador**, **Médico** y **Recepcionista**.  
- Control de accesos por permisos.  
- Activación y expiración de cuentas.

### ⚡ Experiencia de Usuario
- Interfaz moderna y responsive.  
- Menú de navegación con efecto “rayo”.  
- Notificaciones dinámicas.  
- Carga rápida y animaciones optimizadas.

---

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 8+, MySQL 8  
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)  
- **Librerías:** Bootstrap 5, Font Awesome, GSAP  
- **PDF:** DomPDF para generación de recetas  
- **Servidor:** Apache (XAMPP / Hostinger)

---

## 📦 Instalación

### Requisitos del Sistema
- PHP 7.4 o superior  
- MySQL 5.7 o superior  
- Extensiones PHP: `pdo`, `mbstring`, `gd`  
- Servidor web (Apache/Nginx) con `mod_rewrite`

### 🧩 Pasos de Instalación
1. Clonar el repositorio:  
   ```bash
   git clone https://github.com/starlin056/-Consultorio-Ginecol-gico.git
   cd -Consultorio-Ginecol-gico
````

2. Configurar base de datos: importar `database/schema.sql`.
3. Configurar `config/database.php` con tus credenciales de BD.
4. Ajustar permisos de carpeta `public/uploads` (y otras según sea necesario).
5. Editar `config/database.php` para definir `BASE_URL`, host, nombre de BD, usuario y contraseña.
6. Acceder al sistema en:
   `http://localhost/consultorio_ginecologico` (o tu dominio configurado).

---

## 🗂️ Estructura del Proyecto

```
consultorio_ginecologico/
├── app/
│   ├── controllers/       # Controladores MVC
│   ├── models/            # Modelos de datos
│   └── views/             # Vistas HTML/PHP
├── config/
│   ├── database.php       # Configuración de base de datos
│   └── navbar.php         # Configuración del menú de navegación
├── database/
│   └── schema.sql         # Script de base de datos
├── public/
│   ├── assets/            # CSS, JS, imágenes
│   └── uploads/           # Archivos subidos
├── index.php              # Punto de entrada de la aplicación
└── .htaccess              # Reglas de reescritura
```


---

## 🔒 Seguridad

* Validación de datos en servidor y cliente.
* Hash de contraseñas con `password_hash()`.
* Gestión de sesiones segura con expiración controlada.
* Prevención de SQL Injection, XSS.
* Protección de archivos subidos.

---

## 🚀 Despliegue

### En Hostinger

1. Subir todos los archivos vía FTP/File Manager.
2. Crear base de datos MySQL.
3. Importar `schema.sql`.
4. Configurar `config/database.php`.
5. Verificar permisos de `public/uploads`.

### En XAMPP (local)

1. Colocar carpeta en `htdocs/consultorio_ginecologico/`.
2. Ajustar `BASE_URL` en `config/database.php`.
3. Usar `http://localhost/consultorio_ginecologico`.

---

## 🤝 Contribución

* Haz un **fork** del proyecto.
* Crea una rama: `git checkout -b feature/NombreFuncionalidad`.
* Haz commit con tus cambios: `git commit -m "Añade nueva funcionalidad"`.
* Haz push a tu rama: `git push origin feature/NombreFuncionalidad`.
* Abre un **Pull Request (PR)** explicando tu aporte.

---

## 📝 Licencia

Este proyecto está bajo la licencia **MIT**. Consulta el archivo [LICENSE](LICENSE) para más detalles.

---

**💡 Creado con pasión para consultorios modernos.**
Si encuentras útil este proyecto, no olvides **darle una estrella ⭐ en GitHub**.

```


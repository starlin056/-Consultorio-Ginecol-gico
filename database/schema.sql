-- Crear base de datos
CREATE DATABASE IF NOT EXISTS consultorio_ginecologico;
USE consultorio_ginecologico;

-- Tabla de consultorios
CREATE TABLE consultorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    rnc VARCHAR(20),
    direccion TEXT,
    telefono VARCHAR(20),
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultorio_id INT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'medico', 'recepcionista') DEFAULT 'recepcionista',
    activo BOOLEAN DEFAULT TRUE,
    fecha_expiracion DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultorio_id) REFERENCES consultorios(id)
);

-- Tabla de pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultorio_id INT,
    cedula VARCHAR(20) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    telefono VARCHAR(20),
    email VARCHAR(255),
    direccion TEXT,
    alergias TEXT,
    antecedentes TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultorio_id) REFERENCES consultorios(id)
);

-- Tabla de consultas
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    usuario_id INT,
    fecha_consulta DATETIME DEFAULT CURRENT_TIMESTAMP,
    sintomas TEXT,
    diagnostico TEXT,
    tratamiento TEXT,
    indicaciones TEXT,
    notas TEXT,
    proxima_visita DATE,
    duracion TIME,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de recetas
CREATE TABLE recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT,
    medicamento TEXT,
    dosis TEXT,
    frecuencia TEXT,
    duracion TEXT,
    instrucciones TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id)
);

-- Tabla de documentos
CREATE TABLE documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    tipo ENUM('analitica', 'receta', 'nota', 'otros'),
    nombre_archivo VARCHAR(255),
    ruta_archivo VARCHAR(500),
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);

-- Tabla de auditoria
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100),
    modulo VARCHAR(50),
    descripcion TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar datos iniciales
INSERT INTO consultorios (nombre, rnc, direccion, telefono) VALUES 
('Consultorio Ginecológico Principal', '123456789', 'Santo Domingo, República Dominicana', '809-555-0101');

-- Contraseña: 'password' hasheada
INSERT INTO usuarios (consultorio_id, nombre, email, password, rol, fecha_expiracion) VALUES 
(1, 'Administrador Principal', 'admin@consultorio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', '2025-12-31');
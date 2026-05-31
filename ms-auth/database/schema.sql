-- =====================================================
-- ms-auth Database Schema
-- LogiTrans Express - Sistema de Autenticación
-- =====================================================

-- =====================================================
-- Tabla: users
-- Descripción: Almacena credenciales y sesiones de usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID único del usuario',
    username VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre de usuario único',
    email VARCHAR(150) NOT NULL UNIQUE COMMENT 'Email único del usuario',
    password VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada (bcrypt)',
    token VARCHAR(255) DEFAULT NULL UNIQUE COMMENT 'Token generado en login',
    logged BOOLEAN DEFAULT FALSE COMMENT 'Usuario ha iniciado sesión',
    session_active BOOLEAN DEFAULT FALSE COMMENT 'Sesión activa y válida',
    last_login DATETIME DEFAULT NULL COMMENT 'Último acceso registrado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    
    -- Índices para búsquedas rápidas
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_logged (logged),
    INDEX idx_session_active (session_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabla de usuarios y gestión de autenticación';

-- =====================================================
-- Tabla: sessions (opcional pero recomendada)
-- Descripción: Historial de sesiones para auditoría
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID única de la sesión',
    user_id INT NOT NULL COMMENT 'Referencia al usuario',
    token VARCHAR(255) NOT NULL UNIQUE COMMENT 'Token de la sesión',
    ip_address VARCHAR(45) DEFAULT NULL COMMENT 'IP del cliente',
    user_agent VARCHAR(500) DEFAULT NULL COMMENT 'User agent del navegador',
    login_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora de login',
    logout_at DATETIME DEFAULT NULL COMMENT 'Hora de logout',
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Última actividad',
    
    -- Relaciones y índices
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_login_at (login_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de sesiones para auditoría';

-- =====================================================
-- Datos de ejemplo para pruebas
-- =====================================================
INSERT INTO users (username, email, password, logged, session_active)
VALUES 
(
    'admin',
    'admin@logitrans.local',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36gZvQOa', -- password123
    FALSE,
    FALSE
);

-- =====================================================
-- Notas de Implementación
-- =====================================================
-- 1. Las contraseñas se almacenan con hash bcrypt (se genera en PHP con password_hash())
-- 2. El token es un string único generado al login (puedes usar bin2hex(random_bytes(32)))
-- 3. Los campos 'logged' y 'session_active' se usan para validar si la sesión es válida
-- 4. La tabla 'sessions' es opcional pero útil para auditoría y manejo de múltiples sesiones
-- 5. Los timestamps se actualizan automáticamente en MySQL

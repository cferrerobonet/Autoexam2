-- AUTOEXAM2 - Esquema completo de base de datos
-- Compatible con MySQL 8.x
-- Autor: Carlos Ferrero Bonet - v1.2 - 2025
-- Última actualización: 14 de junio de 2025
-- Este archivo contiene la estructura completa de tablas del sistema

SET NAMES utf8mb4;
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

-- Usuarios
CREATE TABLE `usuarios` (
  `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(150) NOT NULL,
  `correo` VARCHAR(150) NOT NULL UNIQUE,
  `contrasena` VARCHAR(255) NOT NULL,
  `pin` VARCHAR(6),
  `rol` ENUM('admin', 'profesor', 'alumno') NOT NULL,
  `curso_asignado` INT,
  `foto` VARCHAR(255),
  `ultimo_acceso` DATETIME DEFAULT NULL,
  `activo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Cursos
CREATE TABLE `cursos` (
  `id_curso` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre_curso` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `id_profesor` INT NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`id_profesor`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Módulos
CREATE TABLE `modulos` (
  `id_modulo` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `descripcion` TEXT,
  `id_profesor` INT,
  `activo` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`id_profesor`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Exámenes
CREATE TABLE `examenes` (
  `id_examen` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `id_modulo` INT,
  `fecha_inicio` DATETIME,
  `fecha_fin` DATETIME,
  `estado` ENUM('borrador', 'activo', 'finalizado') DEFAULT 'borrador',
  FOREIGN KEY (`id_modulo`) REFERENCES `modulos`(`id_modulo`)
) ENGINE=InnoDB;

-- Banco de preguntas
CREATE TABLE `preguntas_banco` (
  `id_pregunta` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo` ENUM('test', 'desarrollo'),
  `enunciado` TEXT NOT NULL,
  `media_tipo` ENUM('imagen','video','url','pdf','ninguno'),
  `media_valor` TEXT,
  `origen` ENUM('manual','pdf','ia') DEFAULT 'manual',
  `id_profesor` INT NOT NULL,
  `publica` TINYINT(1) DEFAULT 0,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_profesor`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Respuestas del banco
CREATE TABLE `respuestas_banco` (
  `id_respuesta` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pregunta` INT NOT NULL,
  `texto` TEXT,
  `correcta` TINYINT(1) DEFAULT 0,
  `media_tipo` ENUM('imagen','video','url','pdf','ninguno'),
  `media_valor` TEXT,
  FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas_banco`(`id_pregunta`)
) ENGINE=InnoDB;

-- Archivos multimedia
CREATE TABLE `archivos` (
  `id_archivo` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo` ENUM('imagen', 'pdf', 'url', 'video', 'logo', 'avatar'),
  `ruta` TEXT,
  `descripcion` TEXT,
  `fecha_subida` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `subido_por` INT,
  `visible` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`subido_por`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Calificaciones
CREATE TABLE `calificaciones` (
  `id_calificacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_examen` INT NOT NULL,
  `id_alumno` INT NOT NULL,
  `nota_final` DECIMAL(5,2) NOT NULL,
  `modo_correccion` ENUM('manual', 'auto', 'mixto'),
  `observaciones` TEXT,
  `fecha_correccion` DATETIME,
  `corregido_por` INT,
  FOREIGN KEY (`id_examen`) REFERENCES `examenes`(`id_examen`),
  FOREIGN KEY (`id_alumno`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Curso-Alumno
CREATE TABLE `curso_alumno` (
  `id_relacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_curso` INT NOT NULL,
  `id_alumno` INT NOT NULL,
  `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`),
  FOREIGN KEY (`id_alumno`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Módulo-Curso
CREATE TABLE `modulo_curso` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_modulo` INT NOT NULL,
  `id_curso` INT NOT NULL,
  FOREIGN KEY (`id_modulo`) REFERENCES `modulos`(`id_modulo`),
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`)
) ENGINE=InnoDB;

-- Notificaciones
CREATE TABLE `notificaciones` (
  `id_notificacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario` INT NOT NULL,
  `mensaje` TEXT NOT NULL,
  `fecha_envio` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `leido` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Configuración general del sistema (modo mantenimiento)
CREATE TABLE IF NOT EXISTS `config_sistema` (
  `clave` VARCHAR(100) PRIMARY KEY,
  `valor` TEXT
) ENGINE=InnoDB;

INSERT IGNORE INTO `config_sistema` (`clave`, `valor`) VALUES ('modo_mantenimiento', '0');

-- Sesiones activas
CREATE TABLE IF NOT EXISTS `sesiones_activas` (
  `id_sesion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario` INT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `php_session_id` VARCHAR(64),
  `fecha_inicio` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ultima_actividad` DATETIME,
  `fecha_fin` DATETIME,
  `ip` VARCHAR(45),
  `user_agent` TEXT,
  `activa` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Índices para mejorar el rendimiento de las sesiones activas
CREATE INDEX `idx_sesiones_token` ON `sesiones_activas` (`token`);
CREATE INDEX `idx_sesiones_php_id` ON `sesiones_activas` (`php_session_id`);

-- Tabla para tokens de recuperación de contraseña
CREATE TABLE IF NOT EXISTS `tokens_recuperacion` (
  `id_token` INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario` INT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `usado` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Registro de actividad y eventos del sistema
CREATE TABLE IF NOT EXISTS `registro_actividad` (
  `id_registro` INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario` INT,
  `accion` VARCHAR(50) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ip` VARCHAR(45),
  `user_agent` TEXT,
  `modulo` VARCHAR(50),
  `elemento_id` INT,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Versionado de configuración
CREATE TABLE IF NOT EXISTS `config_versiones` (
  `id_version` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo` ENUM('smtp', 'ftp', 'sistema', 'mantenimiento'),
  `json_config` TEXT NOT NULL,
  `fecha_guardado` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `guardado_por` INT,
  FOREIGN KEY (`guardado_por`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Protección contra fuerza bruta
CREATE TABLE IF NOT EXISTS `intentos_login` (
  `id_intento` INT AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(45) NOT NULL,
  `correo` VARCHAR(150) NOT NULL,
  `intentos` INT NOT NULL DEFAULT 1,
  `bloqueado_hasta` DATETIME,
  `ultimo_intento` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ip_correo` (`ip`, `correo`)
) ENGINE=InnoDB;

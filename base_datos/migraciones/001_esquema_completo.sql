-- AUTOEXAM2 - Esquema completo de base de datos
-- Compatible con MySQL 8.x
-- Autor: Carlos Ferrero Bonet - v1.3 - 2025
-- Última actualización: 21 de junio de 2025
-- Este archivo contiene la estructura completa de tablas del sistema
-- v1.3: Agregado módulo completo de exámenes con versionado y control de intentos

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
  `id_curso` INT,
  `tiempo_limite` INT,
  `aleatorio_preg` TINYINT(1) DEFAULT 0,
  `aleatorio_resp` TINYINT(1) DEFAULT 0,
  `fecha_inicio` DATETIME,
  `fecha_fin` DATETIME,
  `visible` TINYINT(1) DEFAULT 1,
  `activo` TINYINT(1) DEFAULT 1,
  `id_examen_origen` INT DEFAULT NULL,
  `estado` ENUM('borrador', 'activo', 'finalizado') DEFAULT 'borrador',
  FOREIGN KEY (`id_modulo`) REFERENCES `modulos`(`id_modulo`),
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`),
  FOREIGN KEY (`id_examen_origen`) REFERENCES `examenes`(`id_examen`)
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

-- Preguntas de exámenes específicos
CREATE TABLE `preguntas` (
  `id_pregunta` INT AUTO_INCREMENT PRIMARY KEY,
  `id_examen` INT NOT NULL,
  `tipo` ENUM('test', 'desarrollo') NOT NULL,
  `enunciado` TEXT NOT NULL,
  `media_tipo` ENUM('imagen', 'video', 'url', 'pdf', 'ninguno') DEFAULT 'ninguno',
  `media_valor` TEXT,
  `habilitada` TINYINT(1) DEFAULT 1,
  `orden` INT DEFAULT 0,
  FOREIGN KEY (`id_examen`) REFERENCES `examenes`(`id_examen`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Respuestas de preguntas tipo test
CREATE TABLE `respuestas` (
  `id_respuesta` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pregunta` INT NOT NULL,
  `texto` TEXT NOT NULL,
  `correcta` TINYINT(1) DEFAULT 0,
  `media_tipo` ENUM('imagen', 'video', 'url', 'pdf', 'ninguno') DEFAULT 'ninguno',
  `media_valor` TEXT,
  `orden` INT DEFAULT 0,
  FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas`(`id_pregunta`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Versionado de exámenes
CREATE TABLE `examenes_versiones` (
  `id_version` INT AUTO_INCREMENT PRIMARY KEY,
  `id_examen_original` INT NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `preguntas_json` TEXT NOT NULL,
  `activo` TINYINT(1) DEFAULT 0,
  `autor` INT NOT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `comentario` TEXT,
  FOREIGN KEY (`id_examen_original`) REFERENCES `examenes`(`id_examen`) ON DELETE CASCADE,
  FOREIGN KEY (`autor`) REFERENCES `usuarios`(`id_usuario`)
) ENGINE=InnoDB;

-- Respuestas de estudiantes
CREATE TABLE `respuestas_estudiante` (
  `id_respuesta_estudiante` INT AUTO_INCREMENT PRIMARY KEY,
  `id_examen` INT NOT NULL,
  `id_alumno` INT NOT NULL,
  `id_pregunta` INT NOT NULL,
  `id_respuesta` INT DEFAULT NULL,
  `texto_respuesta` TEXT,
  `fecha_respuesta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `puntuacion` DECIMAL(5,2) DEFAULT NULL,
  `corregida` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`id_examen`) REFERENCES `examenes`(`id_examen`) ON DELETE CASCADE,
  FOREIGN KEY (`id_alumno`) REFERENCES `usuarios`(`id_usuario`),
  FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas`(`id_pregunta`) ON DELETE CASCADE,
  FOREIGN KEY (`id_respuesta`) REFERENCES `respuestas`(`id_respuesta`) ON DELETE CASCADE,
  UNIQUE KEY `unique_alumno_pregunta` (`id_alumno`, `id_pregunta`)
) ENGINE=InnoDB;

-- Control de intentos de examen
CREATE TABLE `intentos_examen` (
  `id_intento` INT AUTO_INCREMENT PRIMARY KEY,
  `id_examen` INT NOT NULL,
  `id_alumno` INT NOT NULL,
  `fecha_inicio` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` DATETIME DEFAULT NULL,
  `tiempo_usado` INT DEFAULT 0,
  `finalizado` TINYINT(1) DEFAULT 0,
  `ip` VARCHAR(45),
  `user_agent` TEXT,
  FOREIGN KEY (`id_examen`) REFERENCES `examenes`(`id_examen`) ON DELETE CASCADE,
  FOREIGN KEY (`id_alumno`) REFERENCES `usuarios`(`id_usuario`)
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

-- Configuraciones específicas para el módulo de exámenes
INSERT IGNORE INTO `config_sistema` (`clave`, `valor`) VALUES 
('examenes_tiempo_maximo', '240'),
('examenes_intentos_maximos', '3'),
('examenes_autoguardado_intervalo', '30'),
('examenes_modo_seguro', '1'),
('examenes_bloquear_copia', '1'),
('examenes_mostrar_resultados', '1');

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

-- Índices para módulo de exámenes
CREATE INDEX `idx_preguntas_examen` ON `preguntas` (`id_examen`);
CREATE INDEX `idx_preguntas_tipo` ON `preguntas` (`tipo`);
CREATE INDEX `idx_preguntas_habilitada` ON `preguntas` (`habilitada`);
CREATE INDEX `idx_respuestas_pregunta` ON `respuestas` (`id_pregunta`);
CREATE INDEX `idx_respuestas_correcta` ON `respuestas` (`correcta`);
CREATE INDEX `idx_resp_est_examen` ON `respuestas_estudiante` (`id_examen`);
CREATE INDEX `idx_resp_est_alumno` ON `respuestas_estudiante` (`id_alumno`);
CREATE INDEX `idx_resp_est_corregida` ON `respuestas_estudiante` (`corregida`);
CREATE INDEX `idx_intentos_examen` ON `intentos_examen` (`id_examen`);
CREATE INDEX `idx_intentos_alumno` ON `intentos_examen` (`id_alumno`);
CREATE INDEX `idx_intentos_finalizado` ON `intentos_examen` (`finalizado`);
CREATE INDEX `idx_versiones_original` ON `examenes_versiones` (`id_examen_original`);
CREATE INDEX `idx_versiones_activo` ON `examenes_versiones` (`activo`);
CREATE INDEX `idx_versiones_autor` ON `examenes_versiones` (`autor`);

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

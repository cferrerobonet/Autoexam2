-- Script para vaciar todas las tablas AUTOEXAM2 sin errores de dependencia
-- CUIDADO: Este script elimina todos los datos pero mantiene la estructura
-- Autor: Carlos Ferrero Bonet - AUTOEXAM2
-- Fecha: 14 de junio de 2025

SET FOREIGN_KEY_CHECKS = 0;

-- Vaciar tablas dependientes primero
TRUNCATE TABLE `respuestas_banco`;
TRUNCATE TABLE `preguntas_banco`;
TRUNCATE TABLE `archivos`;
TRUNCATE TABLE `calificaciones`;
TRUNCATE TABLE `curso_alumno`;
TRUNCATE TABLE `modulo_curso`;
TRUNCATE TABLE `notificaciones`;
TRUNCATE TABLE `sesiones_activas`;
TRUNCATE TABLE `tokens_recuperacion`;
TRUNCATE TABLE `registro_actividad`;
TRUNCATE TABLE `intentos_login`;
TRUNCATE TABLE `config_versiones`;

-- Vaciar tablas principales
TRUNCATE TABLE `examenes`;
TRUNCATE TABLE `modulos`;
TRUNCATE TABLE `cursos`;
TRUNCATE TABLE `usuarios`;

-- NOTA: config_sistema se mantiene para preservar configuración del sistema
-- Si necesitas resetear una configuración específica, usa:
-- UPDATE `config_sistema` SET `valor` = '0' WHERE `clave` = 'modo_mantenimiento';

SET FOREIGN_KEY_CHECKS = 1;

-- TRUNCATE TABLE resetea automáticamente los AUTO_INCREMENT en InnoDB
-- Este script preserva la estructura de las tablas y solo elimina los datos

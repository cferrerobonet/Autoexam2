-- Script para eliminar completamente todas las tablas AUTOEXAM2
-- PELIGRO: Este script elimina toda la estructura y datos
-- Usar solo para reinstalación completa del sistema
-- Autor: Carlos Ferrero Bonet - AUTOEXAM2
-- Fecha: 14 de junio de 2025

SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tablas dependientes primero
DROP TABLE IF EXISTS `respuestas_banco`;
DROP TABLE IF EXISTS `preguntas_banco`;
DROP TABLE IF EXISTS `archivos`;
DROP TABLE IF EXISTS `calificaciones`;
DROP TABLE IF EXISTS `curso_alumno`;
DROP TABLE IF EXISTS `modulo_curso`;
DROP TABLE IF EXISTS `notificaciones`;
DROP TABLE IF EXISTS `sesiones_activas`;
DROP TABLE IF EXISTS `tokens_recuperacion`;
DROP TABLE IF EXISTS `registro_actividad`;
DROP TABLE IF EXISTS `intentos_login`;
DROP TABLE IF EXISTS `config_versiones`;

-- Eliminar tablas principales
DROP TABLE IF EXISTS `examenes`;
DROP TABLE IF EXISTS `modulos`;
DROP TABLE IF EXISTS `cursos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `config_sistema`;

SET FOREIGN_KEY_CHECKS = 1;

-- ADVERTENCIA: Después de ejecutar este script necesitarás:
-- 1. Ejecutar el script de creación de tablas
-- 2. Recrear el usuario administrador
-- 3. Reconfigurar el sistema completamente

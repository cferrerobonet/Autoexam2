-- Insertar datos de ejemplo en registro_actividad
-- Para probar el dashboard de actividad reciente

INSERT INTO registro_actividad (id_usuario, accion, descripcion, modulo, fecha, ip, user_agent) VALUES
(1, 'usuario_creado', 'Nuevo usuario creado: María López (alumno)', 'usuarios', DATE_SUB(NOW(), INTERVAL 30 MINUTE), '127.0.0.1', 'AUTOEXAM2-System'),
(1, 'curso_modificado', 'Curso Matemáticas 3º ESO actualizado - Añadido nuevo módulo', 'cursos', DATE_SUB(NOW(), INTERVAL 2 HOUR), '127.0.0.1', 'AUTOEXAM2-System'),
(NULL, 'backup_sistema', 'Backup automático completo: BD y archivos del sistema', 'sistema', DATE_SUB(NOW(), INTERVAL 1 DAY), '127.0.0.1', 'AUTOEXAM2-System'),
(1, 'configuracion_actualizada', 'Configuración de correo SMTP actualizada', 'configuracion', DATE_SUB(NOW(), INTERVAL 3 DAY), '127.0.0.1', 'AUTOEXAM2-System');

# 24 – Control horario de acceso

Este módulo permite restringir el acceso de los usuarios a determinadas franjas horarias definidas por día de la semana, ya sea usando una tabla específica o directamente campos simplificados en la tabla `usuarios`.

---

## 🎯 Objetivos clave del sistema

- Evitar accesos fuera de horario escolar o en fines de semana si el centro lo desea  
- Permitir al administrador configurar rangos de acceso individuales  
- Registrar todos los intentos denegados por control horario  
- Implementar control básico desde `usuarios` o modo ampliado con `control_horario`

---

## 🔗 Dependencias funcionales

- `05_autenticacion.md`  
- `11_modulo_autenticacion.md`  
- `23_sesiones_activas.md`  
- `41_registro_actividad.md`  

---

## 🗃️ Tablas utilizadas o requeridas

### Opción 1: Extensión en tabla `usuarios`

| Campo           | Tipo         | Descripción                                  |
|------------------|--------------|----------------------------------------------|
| hora_inicio      | TIME         | Hora permitida mínima para acceder            |
| hora_fin         | TIME         | Hora máxima de conexión permitida            |
| dias_autorizados | VARCHAR(20)  | Días permitidos (ej: '1,2,3,4,5')             |

### Opción 2: Tabla `control_horario`

| Campo          | Tipo         | Descripción                                     |
|----------------|--------------|-------------------------------------------------|
| id_control     | INT PK AI    | ID del rango horario                           |
| id_usuario     | INT (FK)     | Usuario al que se aplica                       |
| dia_semana     | ENUM         | 'lunes' a 'domingo'                            |
| hora_inicio    | TIME         | Hora de entrada permitida                      |
| hora_fin       | TIME         | Hora máxima de conexión permitida              |
| activo         | TINYINT(1)   | 1 = regla activa, 0 = desactivada              |

---

## 🧩 Funcionamiento

- En el login se llama a `verificarAccesoHorario()` tras validar usuario/contraseña  
- Si el usuario tiene control horario definido y se encuentra fuera de rango:
  - Acceso denegado
  - Registro en `registro_actividad`
  - Redirección a login con mensaje adecuado  
- Administradores quedan excluidos del control (salvo si se fuerza en config)

---

## 🧑‍💻 UI/UX

- Gestión desde panel admin (modo extendido): `admin/control_horario.php`  
- En modo simplificado: edición directa en `admin/usuarios_editar.php`  
- Selector de días y rangos por usuario  
- Botón para activar/desactivar control por usuario

---

## 🔐 Seguridad y control

- Solo administradores pueden modificar la configuración horaria  
- Validación de solapamientos en modo extendido  
- Escape de horas y valores día para prevenir errores o inyecciones

---

## ✅ Validación de datos

- Validar que `hora_inicio < hora_fin`  
- Validar días válidos del 1 al 7 (`lunes=1`, `domingo=7`)  
- Evitar duplicación de rangos para mismo usuario en misma franja  

---

## 🪵 Manejo de errores y logs

- Errores técnicos → `/almacenamiento/logs/control_horario.log`  
- Accesos fuera de franja → registrados en `registro_actividad` como `denegado_horario`  
- Todo cambio por admin se registra para trazabilidad

---

## 🧪 Casos límite esperados

- Usuario sin reglas → acceso permitido completo  
- Regla inactiva → ignorada  
- Usuario con varias reglas activas → aplica la coincidencia del día y hora actual  
- Admin fuera de franja → acceso permitido por omisión  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación exhaustiva de entradas, permisos y sesiones
- Uso de token CSRF en formularios críticos
- Registro detallado de acciones en `registro_actividad`
- Logs técnicos separados por módulo en `/almacenamiento/logs/`
- Acceso restringido por rol y curso donde aplique
- Control de errores con feedback claro para el usuario
- Sanitización de entradas y protección contra manipulación
- Integración segura con otros módulos relacionados


## ✅ Checklist para Copilot

- [ ] Añadir opción de control horario con campos en `usuarios` o tabla separada  
- [ ] Implementar función `verificarAccesoHorario()`  
- [ ] Registrar accesos denegados por horario en `registro_actividad`  
- [ ] Mostrar mensaje genérico de restricción horaria  
- [ ] Gestionar control desde panel admin según método elegido  
- [ ] Permitir activar/desactivar la opción desde configuración  

---

📌 A continuación, Copilot debe leer e implementar: `46_proteccion_fuerza_bruta.md`

# 21 – Gestión de permisos por rol y vista

Este módulo documenta cómo se gestiona el acceso y visibilidad de cada parte del sistema AUTOEXAM2 en función del rol del usuario y la ruta visitada. Está relacionado con `03_roles_entidades.md`.

---

## 🎯 Objetivos clave del sistema

- Controlar el acceso a controladores, rutas y vistas en función del rol activo del usuario  
- Limitar botones, formularios y opciones de acción según permisos reales  
- Registrar cada intento de acceso no autorizado para trazabilidad  
- Separar la lógica de visibilidad (UI) de la lógica de control (backend)  
- Permitir escalabilidad futura (permisos por grupo, curso, etc.)

---

## 👥 Roles definidos

| Rol           | Permisos generales                                             |
|---------------|----------------------------------------------------------------|
| Administrador | Accede a todo                                                  |
| Profesor      | Accede a su contenido, crea y edita alumnos y exámenes propios |
| Alumno        | Solo accede a exámenes asignados y a sus calificaciones        |

---

## 🧱 Controlador `verificarPermiso()`

Función disponible para cada vista protegida, que valida si el rol tiene acceso a:

1. El controlador solicitado  
2. La vista específica  
3. La acción deseada (ver, crear, editar, borrar)

---

## 📋 Estructura recomendada

- Cada controlador comienza con `verificarSesion()` y luego `verificarRol('rol_requerido')`
- En las vistas, se usan condicionales PHP o JS para ocultar botones o rutas no permitidas

```php
<?php if ($_SESSION['rol'] === 'admin'): ?>
  <a href="configuracion.php">Configuración</a>
<?php endif; ?>
```

---

## 🔒 Acciones sensibles con protección reforzada

| Acción                     | Protección requerida                     |
|----------------------------|------------------------------------------|
| Editar o borrar usuarios   | Solo admin, con token CSRF               |
| Crear exámenes             | Solo profesor con módulo válido          |
| Ver calificaciones         | Solo alumno autenticado y su propia info |
| Exportar datos             | Solo admin o profesor con filtros activos|

---

## 🪵 Manejo de errores y logs

- Acceso denegado a una URL → log en `/almacenamiento/logs/permisos_error.log`
- Intentos de modificación no autorizados → registrado en `registro_actividad`
- Fallos en token CSRF o rol no válido → redirección + log

---

## 🛡️ Fortalezas que Copilot debe implementar

- Comprobación estricta de permisos antes de cada acción  
- Protección por token CSRF y rol activo  
- Separación de botones y rutas visibles por rol  
- Logs diferenciados para accesos, acciones y bloqueos  
- Estructura escalable para posibles permisos por entidad  
- Documentación inline en controladores para cada restricción aplicada

---

## ✅ Checklist Copilot

- [ ] Añadir `verificarRol()` en cada controlador protegido  
- [ ] Ocultar botones y enlaces según el rol activo  
- [ ] Aplicar CSRF a formularios críticos  
- [ ] Registrar en log cada acceso denegado  
- [ ] Proteger vistas sensibles con validación de rol  

---

📌 A continuación, Copilot debe leer e implementar: 41_registro_actividad.md

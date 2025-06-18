# 03 – Roles, entidades y estructura de permisos

---

## 🎯 Objetivos clave del sistema

- Definir los tres roles funcionales principales del sistema AUTOEXAM2  
- Establecer qué acciones puede realizar cada rol sobre las entidades del sistema  
- Garantizar la separación de responsabilidades y seguridad de acceso  
- Servir como base para la asignación de permisos en los controladores y vistas  
- Permitir validación cruzada de sesiones, accesos y uso del sistema en cada punto

---

## 🧭 Objetivo

Estructurar de forma clara los roles funcionales (administrador, profesor, alumno) y su relación con las entidades clave del sistema para un control preciso de acceso, edición y visualización.

---

## 👥 Roles definidos

| Rol           | Descripción                                                             |
|---------------|--------------------------------------------------------------------------|
| Administrador | Accede y gestiona todo el sistema, usuarios, configuración, y backups   |
| Profesor      | Crea y gestiona cursos, módulos, exámenes y alumnos de sus cursos       |
| Alumno        | Solo puede ver y realizar exámenes asignados a su curso                 |

---

## 🧩 Entidades clave del sistema

| Entidad       | CRUD por admin | CRUD por profesor | Acceso por alumno | Notas               |
|---------------|----------------|--------------------|--------------------|----------------------|
| Usuarios      | ✔️              | Crear alumnos solo | No                 |                      |
| Cursos        | ✔️              | ✔️                  | Ver propios        |                      |
| Módulos       | ✔️              | ✔️                  | No                 |                      |
| Exámenes      | ✔️              | ✔️                  | Ver/realizar       | Solo si activo       |
| Calificaciones| ✔️              | ✔️                  | Ver propias        |                      |
| Multimedia    | ✔️              | ✔️                  | No                 | Avatares, PDFs       |
| Estadísticas  | ✔️              | Ver propias        | Ver personales     | Filtradas            |
| Configuración | ✔️              | No                 | No                 | SMTP, Backups, etc.  |

---

## 🔐 Seguridad general por rol

- Cada sesión activa guarda el rol en variable de sesión (`$_SESSION['rol']`)
- Todos los controladores deben verificar `verificarRol('profesor')` o similar
- El alumno no tiene acceso a rutas de gestión o configuración
- Los permisos también se reflejan en la UI: menús, botones y vistas

---

## 🪵 Manejo de errores y logs

- Intentos de acceso no autorizado → log en `/almacenamiento/logs/roles_acceso_error.log`  
- Acciones realizadas fuera de rol permitido → registradas en `registro_actividad`  
- Visualizaciones no permitidas → fallback y mensaje de seguridad

---

## 🛡️ Fortalezas que Copilot debe implementar

- Verificación estricta del rol activo en cada controlador  
- Separación de vistas y rutas por rol (admin, profesor, alumno)  
- Menús, botones y acciones visibles según permisos reales  
- Control de acceso cruzado entre cursos y módulos solo asignados  
- Logs detallados de intentos de acceso indebido  
- Protección de zonas críticas solo para administrador  

---

## ✅ Checklist Copilot

- [ ] Crear constantes o variables de rol globales  
- [ ] Aplicar `verificarRol()` en todos los controladores protegidos  
- [ ] Ajustar menú lateral según permisos  
- [ ] Bloquear visualizaciones y acciones no autorizadas  
- [ ] Log de actividad por acción diferenciada por rol  
- [ ] Reflejar permisos también en vistas y botones

---

📌 A continuación, Copilot debe leer e implementar: 04_flujos_funcionales.md

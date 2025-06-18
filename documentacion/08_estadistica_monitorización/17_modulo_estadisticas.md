# 17 – Modulo de Estadisticas en AUTOEXAM2

## 🔗 Dependencias

- `10_modulo_usuarios.md`
- `14_modulo_examenes.md`
- `16_modulo_calificaciones.md`

Este documento define la estructura, roles, indicadores y visualizacion del modulo de estadisticas del sistema, con paneles diferenciados por tipo de usuario.

---

## 🎯 Objetivos clave del sistema

- Proporcionar visualizaciones claras, dinámicas y filtrables para cada rol del sistema  
- Detectar patrones de rendimiento, evolución, participación y actividad  
- Ofrecer paneles personalizados según el usuario (admin, profesor, alumno)  
- Permitir exportación e integración con log de auditoría  
- Servir como base para informes internos o externos  

---

## 🎯 Objetivo

Proporcionar visualizaciones claras y filtrables del rendimiento de los cursos, modulos, examenes y alumnos, accesibles segun el rol.

---

## 👥 Accesos por rol

| Rol           | Acceso a estadisticas                               |
|---------------|------------------------------------------------------|
| Administrador | Panel global (todos los cursos, modulos, usuarios)  |
| Profesor      | Estadisticas solo de sus modulos y cursos           |
| Alumno        | Solo ve su rendimiento personal                     |

---

## 📊 Indicadores clave para ADMIN/PROFESOR

### Por curso
- Numero de alumnos activos
- Numero de modulos asignados
- Promedio de notas
- Numero de examenes realizados / pendientes

### Por modulo
- Total de examenes creados
- Promedio de notas
- Porcentaje de participacion

### Por examen
- Nota media, maxima y minima
- Distribucion de notas (grafico de barras o pastel)
- Tiempo promedio de finalizacion

### Por alumno
- Historial de examenes
- Promedio personal por modulo
- Grafico comparativo con la media del curso

---

## 🕵️ Panel historico para ADMIN

- Accesos diarios por rol
- Acciones recientes (registro_actividad)
- Total de respuestas por fecha
- Usuarios con mayor actividad

---

## 📈 Visualizacion

- Libreria de graficos: Chart.js o ApexCharts
- Paneles responsive y filtrables
- Contadores numericos (cards)
- Botones para filtrar por curso, modulo, fecha, usuario
- Posible exportacion a CSV (futuro)

---

## 📂 Archivos y estructura MVC

| Componente                  | Ruta                                              |
|-----------------------------|---------------------------------------------------|
| Controlador                 | `app/controladores/estadisticas.php`              |
| Modelo general              | `app/modelos/estadistica_general.php`             |
| Modelo por profesor         | `app/modelos/estadistica_profesor.php`            |
| Modelo por alumno           | `app/modelos/estadistica_alumno.php`              |
| Vista admin                 | `app/vistas/administrador/estadisticas.php`       |
| Vista profesor              | `app/vistas/profesor/estadisticas.php`            |
| Vista alumno                | `app/vistas/alumno/estadisticas.php`              |
| Script de graficos          | `publico/scripts/chart_estadisticas.js`           |

---

## 📋 Estandar de tabla interactiva

- Acciones fuera de la tabla (crear, borrar, desactivar…)
- Seleccion multiple por checkbox
- Edicion directa al hacer clic sobre fila
- Fila tipo “pijama”
- Separacion clara entre filtros y botones de accion
- Orden asc/desc en columnas clave
- Paginacion (5/10/15/20/Todos), por defecto 15

---

## 🪵 Manejo de errores y logs

- Errores en generación de gráficos → log en `/almacenamiento/logs/estadisticas_error.log`
- Fallos al cargar KPIs por curso o módulo → feedback visual
- Consultas administrativas → registradas en `registro_actividad`

---

## 🛡️ Fortalezas que Copilot debe implementar

- Separación estricta de vistas y datos por rol
- Validación de acceso a los datos según rol activo y curso asignado
- Sanitización de filtros por curso, módulo y fecha
- Control de errores en la generación de gráficas
- Logs diferenciados por tipo de error
- Registro de accesos y visualizaciones en `registro_actividad`
- Diseño responsive y compatible con todos los navegadores modernos

---

## ✅ Checklist para Copilot

- [ ] Implementar vistas diferenciadas por rol
- [ ] Mostrar KPIs por curso, modulo, alumno
- [ ] Incluir graficos visuales con Chart.js
- [ ] Permitir filtros por fecha, usuario, modulo
- [ ] Enlazar con log de actividad para consultas administrativas

---

📌 A continuación, Copilot debe leer e implementar: 36_informe_global_curso.md

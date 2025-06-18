# Sistema de Revisiones Periódicas de Documentación

**Última actualización:** 17 de junio de 2025

Este documento establece el proceso oficial para mantener la documentación de AUTOEXAM2 actualizada mediante revisiones periódicas planificadas.

## 1. Propósito

El objetivo del sistema de revisiones periódicas es garantizar que la documentación:
- Se mantenga actualizada con la implementación real del código
- Sea coherente entre diferentes módulos y componentes
- Refleje con precisión el estado actual del proyecto
- Sirva como referencia fiable para el desarrollo

## 2. Calendario de Revisiones

### 2.1. Revisiones Programadas
| Tipo de Revisión | Frecuencia | Alcance |
|-----------------|-----------|---------|
| Completa | Trimestral | Toda la documentación |
| Por módulo | Mensual | Documentación de un módulo específico |
| Por cambio | Inmediata | Documentación afectada por cambios de código |

### 2.2. Revisiones Especiales
- **Antes de cada release**: Enfocada en nuevas funcionalidades y cambios importantes
- **Después de refactorizaciones**: Completa revisión de los módulos afectados
- **Bajo petición**: Cuando se detecten inconsistencias o necesidad urgente

## 3. Procedimiento de Revisión

### 3.1. Preparación
1. Identificar los documentos a revisar según el calendario
2. Asignar responsables para cada sección
3. Preparar una lista de verificación específica

### 3.2. Ejecución
1. Comparar la documentación con el código implementado
2. Verificar coherencia con otros documentos relacionados
3. Actualizar contenido obsoleto o incorrecto
4. Añadir nueva documentación para funcionalidades no documentadas
5. Unificar documentos duplicados o solapados

### 3.3. Validación
1. Revisión cruzada por otro miembro del equipo
2. Pruebas de los ejemplos y procedimientos descritos
3. Verificación de enlaces y referencias

## 4. Registro de Revisiones

Cada revisión debe registrarse en el archivo `/documentacion/registro_revisiones.md` con:
- Fecha de la revisión
- Alcance (qué documentos se revisaron)
- Cambios realizados
- Responsables de la revisión
- Hallazgos importantes

## 5. Estado Actual del Sistema

El sistema de revisiones periódicas se implementará a partir de julio de 2025, con el siguiente calendario inicial:

| Fecha | Tipo | Módulos a revisar |
|------|------|------------------|
| 15/07/2025 | Por módulo | Autenticación y Seguridad |
| 30/07/2025 | Por módulo | Usuarios y Perfiles |
| 15/08/2025 | Por módulo | Cursos y Exámenes |
| 30/08/2025 | Por módulo | Calificaciones |
| 15/09/2025 | Completa | Todo el sistema |

## 6. Responsabilidades

- **Coordinador de Documentación**: Supervisa el calendario y proceso de revisión
- **Líderes de Módulo**: Responsables de la documentación específica de su área
- **Desarrolladores**: Deben actualizar la documentación al implementar cambios
- **Control de Calidad**: Verifica la precisión de la documentación durante las pruebas

## 7. Plan de Migración Documental

### 7.1. Estado Actual
Actualmente, la documentación del proyecto AUTOEXAM2 se encuentra en un estado de transición, con:
- Directorios numerados (`/01_estructura_presentacion/`, etc.) - Documentación original
- Directorio `/actual/` - Nueva estructura unificada por módulos
- Directorio `/historial/` - Versiones históricas preservadas

### 7.2. Proceso de Migración
Para completar la transición a la estructura unificada, se seguirá este plan:

| Fase | Directorio | Prioridad | Fecha objetivo |
|------|------------|-----------|----------------|
| 1 | 03_autenticacion_seguridad | Alta | Julio 2025 |
| 2 | 04_usuarios_dashboard | Alta | Julio 2025 |
| 3 | 01_estructura_presentacion | Media | Agosto 2025 |
| 4 | 09_configuracion_mantenimiento | Media | Agosto 2025 |
| 5 | 05_cursos_modulos_examenes | Media | Septiembre 2025 |
| 6 | Resto de directorios | Baja | Octubre 2025 |

### 7.3. Proceso para cada Documento
1. **Revisión:** Contrastar contenido con implementación actual
2. **Unificación:** Fusionar documentos relacionados eliminando duplicidades
3. **Migración:** Crear nuevo documento en estructura `/actual/`
4. **Archivo:** Mover originales a `/historial/versiones/`
5. **Actualización:** Modificar enlaces en índice y referencias cruzadas

### 7.4. Criterios de Priorización
- Módulos con uso frecuente
- Documentos con más referencias cruzadas
- Áreas con mayor cantidad de cambios recientes
- Componentes centrales del sistema

---

Este sistema garantizará que la documentación del proyecto AUTOEXAM2 permanezca útil, precisa y actualizada a lo largo del ciclo de vida del proyecto.

# 64 – Pruebas unitarias internas para validación técnica

---

## 🎯 Objetivos clave del sistema

- Permitir al administrador ejecutar pruebas internas del sistema para verificar que todo funciona correctamente  
- Comprobar conectividad, rutas, sesiones, dependencias críticas y configuraciones mínimas  
- Asegurar que cada módulo responde correctamente antes de ir a producción  
- Servir como herramienta para Copilot y testers para automatizar revisiones  
- Documentar resultados y registrar logs de errores silenciosos

---

## 🧭 Objetivo

Habilitar un conjunto de pruebas internas técnicas ejecutables desde el panel del administrador para confirmar que todos los módulos, configuraciones y rutas críticas están disponibles y operativas.

---

## 🔗 Dependencias

- `06_configuracion.md`  
- `45_verificacion_integridad_sistema.md`  
- `57_pruebas_validacion_qa.md`

---

## 🧪 Tipos de pruebas automáticas

| Prueba                         | Resultado esperado                                     |
|-------------------------------|--------------------------------------------------------|
| Acceso a base de datos        | Conexión exitosa y tablas disponibles                  |
| Estructura de carpetas        | Existencia de `/publico`, `/config`, `/subidas`        |
| Sesión activa                 | Variable `$_SESSION` válida y con rol definido         |
| Archivos `.env` y config      | Cargados correctamente, sin errores sintácticos        |
| Rutas críticas (`login.php`)  | HTTP 200 y respuesta esperada                          |
| Verificación de logs          | Accesibilidad y permisos de escritura en `/almacenamiento/logs/`  |

---

## 🧱 Componentes

| Elemento                  | Ruta                                      |
|---------------------------|-------------------------------------------|
| Vista principal           | `vistas/admin/pruebas_unitarias.php`      |
| Ejecutores internos       | `utilidades/test_db.php`, `test_env.php`  |
| Consolidación de reporte  | `utilidades/resumen_pruebas.php`          |

---

## 🧪 UI/UX

- Lista de pruebas con estado por color (✔️ / ❌)  
- Posibilidad de ejecutar todas o de forma individual  
- Botón “Reintentar” en cada test fallido  
- Registro visual en `registro_actividad` si alguna prueba falla

---

## 🛡️ Fortalezas que Copilot debe implementar

- Proteger con rol administrador  
- Mostrar resultado inmediato en tabla visual  
- Generar log técnico en `/almacenamiento/logs/test_unidades.log`  
- Mostrar resumen con fecha y hora de última ejecución  
- Fallback visual si algún script intermedio falla  

---

## ✅ Checklist Copilot

- [ ] Crear `pruebas_unitarias.php` en admin  
- [ ] Ejecutar funciones básicas: DB, rutas, sesión  
- [ ] Consolidar resultados en pantalla y log  
- [ ] Permitir reintentar cada test  
- [ ] Registrar evento en log y actividad  

---

📌 A continuación, Copilot debe leer e implementar: 45_verificacion_integridad_sistema.md

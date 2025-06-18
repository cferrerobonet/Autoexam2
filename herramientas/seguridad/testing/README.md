# Testing y Pruebas de Seguridad

Este directorio contiene las herramientas de testing y pruebas de seguridad para AUTOEXAM2.

## Scripts Disponibles

### Tests de Funcionalidad
- **`test_env.php`** - Prueba la biblioteca de variables de entorno
- **`test_deteccion_instalacion.php`** - Verifica la lógica de detección de instalación previa
- **`test_autocompletado.php`** - Prueba el autocompletado de campos del instalador

### Tests de Integración
- **`tests_integracion.php`** - Suite completa de tests de integración del sistema

## Uso

### Ejecutar tests individuales
```bash
# Test de la biblioteca Env
php test_env.php

# Test de detección de instalación
php test_deteccion_instalacion.php

# Test de autocompletado del instalador
php test_autocompletado.php

# Tests de integración
php tests_integracion.php
```

### Desde el gestor principal
```bash
# Ejecutar desde la raíz del proyecto
./herramientas/gestor.sh

# Seleccionar: 1 - Herramientas de Seguridad
# Luego elegir el test específico (opciones 6-8) o suite completa (opción 9)
```

## Descripción de Tests

### test_env.php
- **Propósito**: Verificar el funcionamiento de la biblioteca de variables de entorno
- **Verifica**:
  - Carga de archivo .env
  - Obtención de variables existentes
  - Verificación de existencia de variables
  - Establecimiento de nuevas variables
- **Salida**: Estado de carga y valores de variables de prueba

### test_deteccion_instalacion.php
- **Propósito**: Validar la lógica de detección de instalación previa
- **Verifica**:
  - Existencia de archivo .env
  - Existencia de archivo .lock del instalador
  - Existencia de archivo config.php
  - Lógica de redirección según estado de instalación
- **Salida**: Estado de archivos y acción recomendada

### test_autocompletado.php
- **Propósito**: Probar la funcionalidad de autocompletado del instalador
- **Verifica**:
  - Carga de configuración existente desde .env
  - Recuperación de variables de configuración
  - Simulación de autocompletado de campos del formulario
- **Salida**: Variables detectadas y simulación de autocompletado

### tests_integracion.php
- **Propósito**: Suite completa de tests de integración
- **Verifica**: Múltiples aspectos del sistema de forma integrada
- **Salida**: Resultados detallados de todos los tests

## Ejecución Automática

Estos tests pueden ejecutarse automáticamente como parte de:
- Procesos de CI/CD
- Validaciones pre-despliegue
- Monitorización regular del sistema
- Suite completa de seguridad

## Notas Técnicas

- Todos los tests detectan automáticamente la raíz del proyecto
- Los tests son independientes entre sí
- Cada test proporciona salida detallada para debugging
- Compatible con ejecución desde cualquier ubicación

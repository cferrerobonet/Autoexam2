# 15 – Sistema de Vistas Parciales por Rol

**Implementado y funcional** ✅  
**Ubicación:** `app/vistas/parciales/`  
**Arquitectura:** Componentes reutilizables por rol  

---

## 🎯 Objetivos del sistema

- Proporcionar componentes de UI reutilizables y consistentes
- Separar recursos y configuraciones por rol (admin, profesor, alumno)
- Centralizar configuración de bibliotecas externas
- Mantener coherencia visual en toda la aplicación
- Optimizar carga de recursos según rol del usuario

---

## 🧱 Arquitectura de Componentes

### Estructura por Rol
```
app/vistas/parciales/
├── head_admin.php          # Headers y meta para admin
├── head_profesor.php       # Headers y meta para profesor  
├── head_alumno.php         # Headers y meta para alumno
├── navbar_admin.php        # Navegación admin
├── navbar_profesor.php     # Navegación profesor
├── navbar_alumno.php       # Navegación alumno
├── footer_admin.php        # Footer admin
├── footer_profesor.php     # Footer profesor
├── footer_alumno.php       # Footer alumno
├── scripts_admin.php       # Scripts específicos admin
├── scripts_profesor.php    # Scripts específicos profesor
└── scripts_alumno.php      # Scripts específicos alumno
```

---

## 📋 Componentes Head por Rol

### Configuración Común
Todos los heads incluyen:
- Meta tags responsive
- Configuración UTF-8
- Título dinámico con `SYSTEM_NAME`
- Bootstrap 5 CSS
- Font Awesome Icons
- FullCalendar CSS
- Estilos personalizados por rol

### Head Profesor (`head_profesor.php`) ✅
```php
<title><?= isset($datos['titulo']) ? $datos['titulo'] . ' - ' . SYSTEM_NAME : SYSTEM_NAME ?></title>

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<!-- Estilos personalizados -->
<link href="<?= BASE_URL ?>/publico/recursos/css/profesor.css" rel="stylesheet">
```

### Head Alumno (`head_alumno.php`) ✅
- Misma estructura que profesor
- CSS específico: `/publico/recursos/css/alumno.css`
- Configuración optimizada para vistas de alumno

### Head Admin (`head_admin.php`) 🔄
- Estructura similar con recursos administrativos
- CSS específico: `/publico/recursos/css/admin.css`
- Bibliotecas adicionales para gestión

---

## 🚀 Scripts por Rol

### Scripts Profesor (`scripts_profesor.php`) ✅

#### Bibliotecas Cargadas
```javascript
// Bootstrap 5 JS
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

// Chart.js para gráficos
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

// FullCalendar con localización
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

// Scripts personalizados
<script src="<?= BASE_URL ?>/publico/recursos/js/profesor.js"></script>
```

#### Funcionalidades JavaScript
1. **Inicialización de tooltips**
   ```javascript
   const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
   const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
       return new bootstrap.Tooltip(tooltipTriggerEl)
   });
   ```

2. **Manejo de errores en componentes**
   ```javascript
   function manejarErrorComponente(elementoId, mensaje) {
       const elemento = document.getElementById(elementoId);
       if (elemento) {
           elemento.innerHTML = `
               <div class="alert alert-warning" role="alert">
                   <i class="fas fa-exclamation-triangle me-2"></i>
                   ${mensaje}
                   <button onclick="recargarComponente('${elementoId}')">
                       <i class="fas fa-sync"></i> Reintentar
                   </button>
               </div>
           `;
       }
   }
   ```

3. **Recarga de componentes**
   ```javascript
   function recargarComponente(elementoId) {
       console.log(`Recargando componente: ${elementoId}`);
       // Implementación basada en AJAX
   }
   ```

### Scripts Alumno (`scripts_alumno.php`) ✅
- Misma estructura base que profesor
- Scripts personalizados: `/publico/recursos/js/alumno.js`
- Funcionalidades específicas para vistas de alumno

### Scripts Admin (`scripts_admin.php`) 🔄
- Bibliotecas de gestión administrativa
- Scripts para tablas dinámicas
- Funciones de administración del sistema

---

## 🎨 Sistema de Navegación (Navbars)

### Características Comunes
- Logo dinámico del sistema
- Menú desplegable de usuario
- Navegación responsiva
- Enlaces específicos por rol
- Indicadores de estado

### Estructura Navbar
```php
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Logo y nombre del sistema -->
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>/publico/recursos/logo.png" alt="Logo" height="32">
            <?= SYSTEM_NAME ?>
        </a>
        
        <!-- Menú principal por rol -->
        <div class="navbar-nav">
            <!-- Enlaces específicos según rol -->
        </div>
        
        <!-- Menú de usuario -->
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i> <?= $_SESSION['nombre'] ?? 'Usuario' ?>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil">Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil/sesiones">Mis Sesiones</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/autenticacion/logout">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
```

---

## 🦶 Sistema de Footers

### Características
- Información del sistema
- Enlaces legales
- Versión del sistema
- Información de contacto
- Responsive design

### Footer Estándar
```php
<footer class="bg-light text-center text-muted py-3 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p>&copy; 2025 <?= SYSTEM_NAME ?>. Todos los derechos reservados.</p>
            </div>
            <div class="col-md-6">
                <p>Versión <?= SYSTEM_VERSION ?? '1.0' ?> | <a href="<?= BASE_URL ?>/ayuda">Ayuda</a></p>
            </div>
        </div>
    </div>
</footer>
```

---

## 🔧 Configuración de Recursos Externos

### Bootstrap 5.3.0
- **CSS:** CDN jsdelivr
- **JS:** Bundle completo con Popper
- **Personalización:** Archivos CSS propios por rol

### Font Awesome 6.4.0
- **Iconografía:** Completa vía CDN
- **Versión:** Última estable
- **Uso:** Iconos en toda la interfaz

### FullCalendar 5.11.3
- **Funcionalidad:** Calendarios interactivos
- **Localización:** Español incluido
- **Integración:** Dashboards de profesor y alumno

### Chart.js 3.9.1
- **Gráficos:** Estadísticas y visualizaciones
- **Tipos:** Barras, líneas, círculos
- **Responsive:** Adaptativo automático

---

## 🛡️ Seguridad en Vistas Parciales

### Validación de Sesión
```php
<?php if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor'): ?>
    <?php header('Location: ' . BASE_URL . '/error/acceso'); exit; ?>
<?php endif; ?>
```

### Protección XSS
```php
<?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?>
```

### Tokens CSRF
```php
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
```

---

## 📱 Responsive Design

### Breakpoints Bootstrap
- **xs:** <576px - Móviles
- **sm:** 576px - Móviles grandes  
- **md:** 768px - Tablets
- **lg:** 992px - Escritorio
- **xl:** 1200px - Escritorio grande

### Adaptaciones por Dispositivo
- Menús colapsables en móvil
- Tablas responsive con scroll horizontal
- Botones adaptados a touch
- Espaciado optimizado por pantalla

---

## 🔄 Uso en Controladores

### Inclusión en Vistas
```php
// En dashboard de profesor
require_once APP_PATH . '/vistas/parciales/head_profesor.php';
// Contenido específico
require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
```

### Datos Dinámicos
```php
$datos = [
    'titulo' => 'Panel de Profesor',
    'css_adicional' => ['/publico/recursos/css/modulo_especifico.css'],
    'js_adicional' => ['/publico/recursos/js/funcionalidad_extra.js']
];
```

---

## ✅ Estado de Implementación

### Completamente Implementado ✅
- [x] Head profesor con todas las bibliotecas
- [x] Head alumno con configuración específica
- [x] Scripts profesor con funciones avanzadas
- [x] Scripts alumno con funcionalidades básicas
- [x] Sistema de títulos dinámicos
- [x] Configuración de recursos externos
- [x] Manejo de errores JavaScript
- [x] Inicialización de componentes

### Implementado Parcialmente ⚠️
- [~] Head admin (estimado 90%)
- [~] Navbar admin (estimado 90%)
- [~] Footer admin (estimado 90%)
- [~] Scripts admin (estimado 80%)
- [~] Navbar profesor (estimado 95%)
- [~] Navbar alumno (estimado 95%)

### Por Verificar 🔍
- [ ] Footer profesor
- [ ] Footer alumno
- [ ] CSS específicos por rol en `/publico/recursos/css/`
- [ ] JS específicos por rol en `/publico/recursos/js/`

---

## 🚀 Ventajas del Sistema

1. **Reutilización de código** - Componentes centralizados
2. **Mantenibilidad** - Cambios centralizados se propagan
3. **Consistencia visual** - Mismos componentes, misma apariencia
4. **Optimización por rol** - Recursos específicos según necesidades
5. **Responsive automático** - Bootstrap 5 garantiza adaptabilidad
6. **Carga optimizada** - Solo recursos necesarios por rol

---

📌 **Nota:** Este sistema está mayormente implementado y funcional. La documentación refleja el análisis del código existente y identifica las áreas que requieren verificación o complemento.

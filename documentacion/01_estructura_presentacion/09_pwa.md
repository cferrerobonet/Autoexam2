# 09 – Manual de Implementación de Compatibilidad PWA en AUTOEXAM2

Este manual detalla la implementación exhaustiva de la compatibilidad PWA (Progressive Web App) para AUTOEXAM2, cubriendo estructura, archivos, lógica, checklist y fortalezas para Copilot.

---

## 1. Objetivo y Alcance
- Permitir que AUTOEXAM2 funcione como app instalable desde navegador.
- Soportar funcionamiento offline básico y carga rápida en redes lentas.
- Optimizar la experiencia en móviles y tablets.

---

## 2. Estructura de Archivos PWA
```
/publico/
├── manifest.json               # Descripción de la app (nombre, icono, color, etc.)
├── service-worker.js           # Lógica offline y caché
├── iconos/pwa/                 # Iconos de diferentes resoluciones
└── index.php                   # Página principal servida en modo app
```

---

## 3. Configuración de manifest.json
- name: AUTOEXAM2
- short_name: AUTOEXAM
- start_url: “/”
- display: standalone
- background_color: “#ffffff”
- theme_color: “#0d6efd”
- icons: 192px, 512px en PNG o WEBP

---

## 4. Funcionalidades del service-worker.js
- Precargar archivos estáticos esenciales
- Permitir carga básica sin conexión (pantalla offline)
- Cachear recursos (CSS, JS, iconos)
- Detectar nuevas versiones y forzar recarga
- No cachear rutas dinámicas (ej: /realizar_examen.php)

---

## 5. Requisitos Técnicos para PWA
- Dominio HTTPS obligatorio
- Punto de entrada: `/publico/` con manifest.json y service-worker.js
- Todos los assets deben ser accesibles públicamente

---

## 6. Recomendaciones de UI/UX
- Formularios y páginas completamente responsive
- Menú lateral colapsable en vista móvil
- Encabezado fijo con acceso a secciones clave
- Opción de instalar la app desde el navegador

---

## 7. Fortalezas que Copilot debe Implementar
- No cachear datos sensibles en service-worker.js
- Detectar cambios en archivos y actualizar caché
- Mostrar página de error si no hay conexión
- Formularios no deben depender de recursos cacheados para validaciones

---

## 8. Checklist Copilot
- [ ] Crear manifest.json correctamente estructurado
- [ ] Crear service-worker.js con lógica de caché mínima segura
- [ ] Añadir iconos PWA en /publico/iconos/pwa/
- [ ] Añadir etiquetas <link rel="manifest"> y script del service worker en index.php
- [ ] Comprobar compatibilidad desde Chrome/Edge/Safari móvil
- [ ] Incluir lógica para detectar e instalar como app móvil

---

## 9. Observaciones
Este módulo hace que AUTOEXAM2 sea usable como aplicación instalada en móviles y tablets, especialmente útil para alumnos.
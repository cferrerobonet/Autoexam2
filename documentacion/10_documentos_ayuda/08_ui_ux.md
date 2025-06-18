# 08 – Interfaz de Usuario y Experiencia de Usuario (UI/UX) de AUTOEXAM2

Este documento establece las normas visuales y de interacción que deben aplicarse en toda la plataforma AUTOEXAM2. El diseño debe ser limpio, intuitivo, accesible y adaptable para funcionar en múltiples dispositivos.

---

## 🎯 Objetivos clave del sistema

- Unificar la estética y experiencia visual en todos los módulos  
- Garantizar accesibilidad, legibilidad y adaptación a móviles  
- Facilitar la comprensión de formularios y datos  
- Reforzar la identidad del usuario en toda navegación  

---

## 🔗 Dependencias funcionales

- `00_estructura_proyecto.md`  
- `33_exportacion_datos.md`  
- `05_autenticacion.md`  
- `publico/estilos/formulario.css`  

---

## 🗃️ Tablas utilizadas o requeridas

Este módulo **no requiere la creación de nuevas tablas**. Toda la lógica se basa en:

- Estilos visuales
- Archivos estáticos
- Representación del contenido ya existente (usuarios, módulos, cursos, etc.)

---

## 🎨 Estilo visual base

- Framework: **Bootstrap 5**  
- Iconografía: **FontAwesome 6**  
- Paleta adaptable según rol:
  - Administrador: gris oscuro o azul institucional
  - Profesor: verde
  - Alumno: naranja o azul claro

---

## 🧩 Diseño de componentes

- Cabecera + Sidebar lateral fijo para navegación  
- Layout compartido entre vistas (`maquetacion`)  
- Layout adaptable a móviles (responsive)  
- Logo mostrado arriba a la izquierda  
- Foto del usuario mostrada en la parte superior del menú lateral  

---

## 🧠 Formularios e interacción

### Buenas prácticas:
- Etiquetas visibles arriba de los campos  
- Iconos representativos a la izquierda del campo  
- Validación en tiempo real:
  - Campo obligatorio  
  - Formato incorrecto  
  - Confirmación de contraseñas  
- Mensajes `valid-feedback` y `invalid-feedback`

### Tipos de entrada clave:
- Correo: Validado al escribir (ejemplo visual: `fa-envelope`)  
- Contraseña: Doble campo + barra de seguridad (rojo > amarillo > verde)  
- Subida de imagen:  
  - Vista previa  
  - Drag & Drop o selector tradicional  
  - Solo formatos válidos

---

## 🔎 Tooltips y ayudas visuales

- Icono `fa-info-circle` con `data-bs-toggle="tooltip"`  
- Mostrar explicación corta al pasar el ratón sobre campos complejos  

---

## 🖼️ Componentes reutilizables

- Tarjetas de resumen con icono + valor  
- Tablas con cabecera fija, responsive y botones de acción  
- Botones con texto + icono (`fa-plus`, `fa-save`, etc.)  
- Formulario estandarizado reutilizable por módulo  

---

## 🧪 Estados visuales y accesibilidad

- Borde verde: válido | Borde rojo: error  
- Colores neutros como base; nunca color como único indicador de estado  
- Compatible con teclado (tabulador, accesibilidad base de Bootstrap)  
- Legibilidad en pantallas pequeñas y alto contraste como opción futura  

---

## 🛠️ Archivos visuales

| Elemento                  | Ruta                                |
|---------------------------|-------------------------------------|
| CSS personalizado         | `publico/estilos/formulario.css`   |
| JS de validaciones        | `publico/scripts/validaciones.js`  |
| Iconos                    | `publico/iconos/`                   |
| Logo principal            | `publico/iconos/logo.png`          |
| Imagen usuario por defecto| `publico/iconos/user_image_default.png` |

---

## 👤 Identidad del usuario en cabecera

### Elementos mostrados:
- 🖼️ Foto de perfil o avatar:
  - Si tiene `foto`, se muestra
  - Si no tiene, se usa `user_image_default.png`
- 👤 Nombre y apellidos
- 🏷️ Rol del usuario en etiqueta de color (badge)

### Colores de etiqueta por rol:
| Rol           | Clase CSS sugerida        |
|---------------|----------------------------|
| Administrador | `badge bg-dark`           |
| Profesor      | `badge bg-success`        |
| Alumno        | `badge bg-warning text-dark` |

### Ejemplo HTML:
```html
<div class="d-flex align-items-center gap-2">
  <img src="/publico/subidas/usuarios/{foto}" alt="Avatar" class="rounded-circle" width="36" height="36" />
  <div class="d-flex flex-column">
    <strong>{nombre} {apellidos}</strong>
    <span class="badge bg-success text-capitalize">{rol}</span>
  </div>
</div>
```

---


---

## 🛡️ Fortalezas que Copilot debe implementar

- Unificación completa de estilos con clases Bootstrap centralizadas
- Validación visual inmediata para todos los formularios
- Inclusión de tooltips accesibles en campos técnicos
- Separación visual clara de elementos interactivos (botones, inputs, iconos)
- Accesibilidad total vía teclado y contraste para todos los usuarios
- Verificación visual de errores y estructura responsive desde el inicio


---

## 🪵 Manejo de errores y logs

- Errores de carga de estilos o fuentes → log en `/almacenamiento/logs/ui_error.log`  
- Incidencias de renderizado detectadas en frontend → visibles vía consola para depuración  
- Acciones de cambio de avatar o imagen institucional → registrar en `registro_actividad`  


## ✅ Checklist para Copilot

- [ ] Aplicar Bootstrap 5 en toda la interfaz  
- [ ] Usar FontAwesome en botones, formularios y menús  
- [ ] Añadir validación visual en tiempo real  
- [ ] Preparar diseño responsive adaptable a móviles/tablets  
- [ ] Implementar tooltips en campos críticos  
- [ ] Unificar formularios y estructuras visuales reutilizables  

---

📌 A continuación, Copilot debe leer e implementar: `10_modulo_usuarios.md`

# 63 – Buscador global contextual

---

## 🎯 Objetivos clave del sistema

- Permitir búsquedas rápidas desde cualquier panel según el rol del usuario  
- Acceder de forma inmediata a alumnos, cursos, módulos, exámenes o usuarios por nombre o código  
- Integrar el buscador en la interfaz principal como componente universal  
- Ofrecer resultados filtrados y categorizados según entidad  
- Mejorar la eficiencia en entornos con gran volumen de datos

---

## 🧭 Objetivo

Incorporar un buscador global en la interfaz superior que permita a los usuarios con rol `admin` o `profesor` encontrar cualquier entidad del sistema desde cualquier parte del panel.

---

## 🔗 Dependencias

- `10_modulo_usuarios.md`  
- `12_modulo_cursos.md`  
- `13_modulo_modulos.md`  
- `14_modulo_examenes.md`

---

## 🧪 UI/UX

- Barra de búsqueda accesible desde la cabecera (`navbar`)  
- Campo de texto con autocompletado y agrupación por tipo  
- Resultados en dropdown tipo:

```plaintext
🔍 “Carlos”
— Alumnos
Carlos Pérez (Ver ficha)
Carlos Gómez (Ver ficha)
— Profesores
Carlos Ramos (Editar)
— Exámenes
Repaso Circuitos (Abrir)
```

- Permite usar tecla ENTER o clic para redirigir  
- Icono fijo `fa-search` + atajo teclado (`Ctrl + K`)

---

## 🧱 Rutas implicadas

| Componente        | Ruta                            |
|-------------------|---------------------------------|
| JS búsqueda       | `publico/scripts/buscador.js`   |
| Backend API       | `controladores/api/buscar.php`  |
| Componentes UI    | `componentes/input_busqueda.php`|

---

## 🛡️ Fortalezas que Copilot debe implementar

- Restringir resultados según rol activo y permisos  
- Sanitizar entrada y limitar longitud de búsqueda  
- Priorizar resultados por relevancia (nombre exacto > parcial)  
- Proteger consultas contra inyección o acceso cruzado  
- Registrar en log de uso (`/almacenamiento/logs/buscador.log`) búsquedas relevantes  
- Mostrar mensaje si no hay resultados o rol no tiene acceso

---

## ✅ Checklist Copilot

- [ ] Crear input de búsqueda global (`input_busqueda.php`)  
- [ ] Implementar autocompletado por AJAX  
- [ ] Limitar resultados según entidad y rol  
- [ ] Conectar con tablas: usuarios, cursos, módulos, exámenes  
- [ ] Enlazar cada resultado a su vista correspondiente  
- [ ] Registrar búsquedas en `registro_actividad` y log  

---

📌 A continuación, Copilot debe leer e implementar: 10_modulo_usuarios.md

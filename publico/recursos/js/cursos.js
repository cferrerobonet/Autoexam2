/**
 * Script para aplicar estilos a las descripciones de cursos - AUTOEXAM2
 */

document.addEventListener('DOMContentLoaded', function() {
    // Aplicar estilo a las descripciones de cursos
    aplicarEstiloDescripcionesCursos();
});

/**
 * Aplica estilos específicos a las descripciones de cursos
 */
function aplicarEstiloDescripcionesCursos() {
    // Buscar elementos que contienen códigos de curso como "1 ARI (2025-2026)"
    const textosDescripcion = document.querySelectorAll('small.text-muted');
    
    textosDescripcion.forEach(elemento => {
        // Verificar si el texto tiene el formato de código de curso
        if (elemento.textContent.match(/\d+\s+[A-Z]{2,3}\s+\(\d{4}-\d{4}\)/) ||
            elemento.textContent.match(/Curso\s+\d{4}-\d{4}/)) {
            elemento.classList.add('curso-codigo');
        }
    });
}

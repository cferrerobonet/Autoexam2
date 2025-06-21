<?php
/**
 * Vista para realizar examen (estudiantes)
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

// Verificar sesión y permisos de alumno
if (!isset($_SESSION['usuario_logueado']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: " . BASE_URL . "/autenticacion/login");
    exit;
}

// Verificar que se ha proporcionado el examen
if (!isset($examen) || empty($examen)) {
    header("Location: " . BASE_URL . "/inicio");
    exit;
}

$titulo_pagina = 'Realizar Examen: ' . $examen['titulo'];
require_once __DIR__ . '/../comunes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Información del examen -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> <?= htmlspecialchars($examen['titulo']) ?>
                    </h4>
                    <small><?= htmlspecialchars($examen['nombre_curso']) ?> - <?= htmlspecialchars($examen['nombre_modulo']) ?></small>
                </div>
                <div class="col-auto">
                    <div id="cronometro" class="badge bg-warning fs-6">
                        <i class="fas fa-clock"></i> <span id="tiempo-restante">--:--:--</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-1"><strong>Descripción:</strong> <?= htmlspecialchars($examen['descripcion']) ?></p>
                    <p class="mb-1"><strong>Total de preguntas:</strong> <?= count($preguntas) ?></p>
                    <p class="mb-1"><strong>Duración:</strong> <?= $examen['duracion_minutos'] ? $examen['duracion_minutos'] . ' minutos' : 'Sin límite' ?></p>
                </div>
                <div class="col-md-4">
                    <div class="text-end">
                        <p class="mb-1"><strong>Intentos realizados:</strong> <?= $intento_actual ?> de <?= $examen['intentos_permitidos'] ?? 'Ilimitados' ?></p>
                        <?php if ($examen['fecha_inicio']): ?>
                            <p class="mb-1"><strong>Disponible desde:</strong> <?= date('d/m/Y H:i', strtotime($examen['fecha_inicio'])) ?></p>
                        <?php endif; ?>
                        <?php if ($examen['fecha_fin']): ?>
                            <p class="mb-1"><strong>Disponible hasta:</strong> <?= date('d/m/Y H:i', strtotime($examen['fecha_fin'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario del examen -->
    <form id="formulario-examen" method="POST" action="<?= BASE_URL ?>/examenes/enviar/<?= $examen['id_examen'] ?>">
        <input type="hidden" name="id_examen" value="<?= $examen['id_examen'] ?>">
        <input type="hidden" name="inicio_examen" value="<?= date('Y-m-d H:i:s') ?>">
        
        <!-- Navegación de preguntas -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Navegación rápida:</h6>
                <div class="row" id="navegacion-preguntas">
                    <?php foreach ($preguntas as $index => $pregunta): ?>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm pregunta-nav" 
                                    data-pregunta="<?= $index + 1 ?>" onclick="irAPregunta(<?= $index + 1 ?>)">
                                <?= $index + 1 ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Preguntas del examen -->
        <?php foreach ($preguntas as $index => $pregunta): ?>
            <div class="card mb-4 pregunta-container" id="pregunta-<?= $index + 1 ?>" 
                 style="<?= $index == 0 ? 'display: block;' : 'display: none;' ?>">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Pregunta <?= $index + 1 ?> de <?= count($preguntas) ?>
                            <span class="badge bg-<?= $pregunta['tipo'] == 'test' ? 'primary' : 'info' ?>">
                                <?= ucfirst($pregunta['tipo']) ?>
                            </span>
                        </h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   id="marcar-<?= $index + 1 ?>" onchange="marcarPregunta(<?= $index + 1 ?>)">
                            <label class="form-check-label" for="marcar-<?= $index + 1 ?>">
                                <small>Marcar para revisar</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Enunciado -->
                    <div class="mb-4">
                        <div class="fs-5"><?= $pregunta['enunciado'] ?></div>
                        
                        <!-- Multimedia si existe -->
                        <?php if ($pregunta['media_tipo'] != 'ninguno' && !empty($pregunta['media_valor'])): ?>
                            <div class="mt-3">
                                <?php switch($pregunta['media_tipo']): 
                                    case 'imagen': ?>
                                        <img src="<?= $pregunta['media_valor'] ?>" class="img-fluid rounded" 
                                             style="max-height: 400px;" alt="Imagen de la pregunta">
                                        <?php break;
                                    case 'video': ?>
                                        <div class="ratio ratio-16x9" style="max-width: 600px;">
                                            <?php 
                                            $video_url = $pregunta['media_valor'];
                                            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                                // Convertir URL de YouTube a embed
                                                preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video_url, $matches);
                                                $video_id = $matches[1] ?? '';
                                                $embed_url = "https://www.youtube.com/embed/$video_id";
                                            } else {
                                                $embed_url = $video_url;
                                            }
                                            ?>
                                            <iframe src="<?= $embed_url ?>" allowfullscreen></iframe>
                                        </div>
                                        <?php break;
                                    case 'url': ?>
                                        <a href="<?= $pregunta['media_valor'] ?>" target="_blank" class="btn btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Ver recurso
                                        </a>
                                        <?php break;
                                    case 'pdf': ?>
                                        <a href="<?= $pregunta['media_valor'] ?>" target="_blank" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf"></i> Ver documento PDF
                                        </a>
                                        <?php break;
                                endswitch; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Respuestas según tipo -->
                    <?php if ($pregunta['tipo'] == 'test'): ?>
                        <!-- Pregunta tipo test -->
                        <div class="respuestas-test">
                            <?php foreach ($pregunta['respuestas'] as $resp_index => $respuesta): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="<?= $pregunta['multiple'] ? 'checkbox' : 'radio' ?>" 
                                           name="respuesta_<?= $pregunta['id_pregunta'] ?><?= $pregunta['multiple'] ? '[]' : '' ?>" 
                                           value="<?= $respuesta['id_respuesta'] ?>"
                                           id="resp_<?= $pregunta['id_pregunta'] ?>_<?= $resp_index ?>"
                                           onchange="guardarRespuesta(<?= $pregunta['id_pregunta'] ?>, <?= $index + 1 ?>)">
                                    <label class="form-check-label" for="resp_<?= $pregunta['id_pregunta'] ?>_<?= $resp_index ?>">
                                        <?= htmlspecialchars($respuesta['texto']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Pregunta de desarrollo -->
                        <div class="respuesta-desarrollo">
                            <label for="desarrollo_<?= $pregunta['id_pregunta'] ?>" class="form-label">
                                Tu respuesta:
                            </label>
                            <textarea class="form-control" id="desarrollo_<?= $pregunta['id_pregunta'] ?>" 
                                      name="respuesta_<?= $pregunta['id_pregunta'] ?>" rows="8"
                                      placeholder="Escribe aquí tu respuesta..."
                                      onchange="guardarRespuesta(<?= $pregunta['id_pregunta'] ?>, <?= $index + 1 ?>)"></textarea>
                            <small class="form-text text-muted">
                                Puedes usar tantas líneas como necesites para explicar tu respuesta.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Navegación entre preguntas -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" 
                                onclick="irAPregunta(<?= $index ?>)" <?= $index == 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        
                        <div>
                            <button type="button" class="btn btn-warning me-2" onclick="marcarPregunta(<?= $index + 1 ?>)">
                                <i class="fas fa-flag"></i> Marcar para revisar
                            </button>
                            <button type="button" class="btn btn-info" onclick="mostrarResumen()">
                                <i class="fas fa-list"></i> Ver resumen
                            </button>
                        </div>
                        
                        <?php if ($index < count($preguntas) - 1): ?>
                            <button type="button" class="btn btn-outline-primary" onclick="irAPregunta(<?= $index + 2 ?>)">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success" onclick="mostrarConfirmacionEnvio()">
                                <i class="fas fa-paper-plane"></i> Finalizar Examen
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </form>
</div>

<!-- Modal de resumen -->
<div class="modal fade" id="modalResumen" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resumen del Examen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Estado de las preguntas:</h6>
                        <div id="resumen-preguntas"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>Estadísticas:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Respondidas:</strong> <span id="stats-respondidas">0</span> de <?= count($preguntas) ?></li>
                            <li><strong>Sin responder:</strong> <span id="stats-sin-responder"><?= count($preguntas) ?></span></li>
                            <li><strong>Marcadas:</strong> <span id="stats-marcadas">0</span></li>
                            <li><strong>Tiempo transcurrido:</strong> <span id="stats-tiempo">--:--:--</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continuar</button>
                <button type="button" class="btn btn-success" onclick="confirmarEnvio()">
                    <i class="fas fa-paper-plane"></i> Finalizar Examen
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Envío
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>¿Estás seguro de que quieres enviar el examen?</strong></p>
                <p>Una vez enviado, no podrás modificar tus respuestas.</p>
                <div id="resumen-final"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="enviarExamen()">
                    <i class="fas fa-paper-plane"></i> Sí, enviar examen
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let preguntaActual = 1;
let totalPreguntas = <?= count($preguntas) ?>;
let tiempoInicio = new Date();
let duracionMinutos = <?= $examen['duracion_minutos'] ? $examen['duracion_minutos'] : 0 ?>;
let respuestasGuardadas = {};
let preguntasMarcadas = new Set();
let cronometroInterval;

// Inicializar examen
document.addEventListener('DOMContentLoaded', function() {
    inicializarCronometro();
    cargarRespuestasGuardadas();
    actualizarNavegacion();
    
    // Advertencia al salir
    window.addEventListener('beforeunload', function(e) {
        e.preventDefault();
        e.returnValue = '¿Estás seguro de que quieres salir? Perderás tu progreso.';
    });
});

// Cronómetro
function inicializarCronometro() {
    if (duracionMinutos > 0) {
        // Cronómetro descendente
        let tiempoRestante = duracionMinutos * 60;
        
        cronometroInterval = setInterval(function() {
            tiempoRestante--;
            
            if (tiempoRestante <= 0) {
                alert('Se ha agotado el tiempo del examen. Se enviará automáticamente.');
                enviarExamen();
                return;
            }
            
            let horas = Math.floor(tiempoRestante / 3600);
            let minutos = Math.floor((tiempoRestante % 3600) / 60);
            let segundos = tiempoRestante % 60;
            
            document.getElementById('tiempo-restante').textContent = 
                `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
            
            // Cambiar color si queda poco tiempo
            if (tiempoRestante <= 300) { // 5 minutos
                document.getElementById('cronometro').className = 'badge bg-danger fs-6';
            } else if (tiempoRestante <= 900) { // 15 minutos
                document.getElementById('cronometro').className = 'badge bg-warning fs-6';
            }
        }, 1000);
    } else {
        // Cronómetro ascendente
        cronometroInterval = setInterval(function() {
            let tiempoTranscurrido = Math.floor((new Date() - tiempoInicio) / 1000);
            let horas = Math.floor(tiempoTranscurrido / 3600);
            let minutos = Math.floor((tiempoTranscurrido % 3600) / 60);
            let segundos = tiempoTranscurrido % 60;
            
            document.getElementById('tiempo-restante').textContent = 
                `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
        }, 1000);
    }
}

// Navegación entre preguntas
function irAPregunta(numero) {
    if (numero < 1 || numero > totalPreguntas) return;
    
    // Ocultar pregunta actual
    document.getElementById(`pregunta-${preguntaActual}`).style.display = 'none';
    
    // Mostrar nueva pregunta
    document.getElementById(`pregunta-${numero}`).style.display = 'block';
    
    preguntaActual = numero;
    actualizarNavegacion();
}

// Actualizar navegación
function actualizarNavegacion() {
    document.querySelectorAll('.pregunta-nav').forEach(btn => {
        btn.classList.remove('btn-primary', 'btn-success', 'btn-warning');
        btn.classList.add('btn-outline-secondary');
    });
    
    // Marcar pregunta actual
    const btnActual = document.querySelector(`[data-pregunta="${preguntaActual}"]`);
    if (btnActual) {
        btnActual.classList.remove('btn-outline-secondary');
        btnActual.classList.add('btn-primary');
    }
    
    // Marcar preguntas respondidas y marcadas
    Object.keys(respuestasGuardadas).forEach(idPregunta => {
        const numeroPregunta = parseInt(idPregunta.split('_')[1]);
        const btn = document.querySelector(`[data-pregunta="${numeroPregunta}"]`);
        if (btn && !btn.classList.contains('btn-primary')) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
        }
    });
    
    preguntasMarcadas.forEach(numero => {
        const btn = document.querySelector(`[data-pregunta="${numero}"]`);
        if (btn && !btn.classList.contains('btn-primary')) {
            btn.classList.remove('btn-outline-secondary', 'btn-success');
            btn.classList.add('btn-warning');
        }
    });
}

// Guardar respuesta
function guardarRespuesta(idPregunta, numeroPregunta) {
    const elementos = document.querySelectorAll(`[name="respuesta_${idPregunta}"], [name="respuesta_${idPregunta}[]"]`);
    let respuesta = [];
    
    elementos.forEach(elemento => {
        if (elemento.type === 'radio' || elemento.type === 'checkbox') {
            if (elemento.checked) {
                respuesta.push(elemento.value);
            }
        } else {
            if (elemento.value.trim() !== '') {
                respuesta.push(elemento.value);
            }
        }
    });
    
    if (respuesta.length > 0) {
        respuestasGuardadas[`pregunta_${idPregunta}`] = respuesta;
    } else {
        delete respuestasGuardadas[`pregunta_${idPregunta}`];
    }
    
    actualizarNavegacion();
    guardarEnLocalStorage();
}

// Marcar pregunta para revisar
function marcarPregunta(numero) {
    if (preguntasMarcadas.has(numero)) {
        preguntasMarcadas.delete(numero);
    } else {
        preguntasMarcadas.add(numero);
    }
    
    actualizarNavegacion();
    guardarEnLocalStorage();
}

// Mostrar resumen
function mostrarResumen() {
    actualizarEstadisticas();
    const modal = new bootstrap.Modal(document.getElementById('modalResumen'));
    modal.show();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const respondidas = Object.keys(respuestasGuardadas).length;
    const sinResponder = totalPreguntas - respondidas;
    const marcadas = preguntasMarcadas.size;
    
    document.getElementById('stats-respondidas').textContent = respondidas;
    document.getElementById('stats-sin-responder').textContent = sinResponder;
    document.getElementById('stats-marcadas').textContent = marcadas;
    
    // Tiempo transcurrido
    let tiempoTranscurrido = Math.floor((new Date() - tiempoInicio) / 1000);
    let horas = Math.floor(tiempoTranscurrido / 3600);
    let minutos = Math.floor((tiempoTranscurrido % 3600) / 60);
    let segundos = tiempoTranscurrido % 60;
    
    document.getElementById('stats-tiempo').textContent = 
        `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
    
    // Resumen de preguntas
    let resumenHTML = '';
    for (let i = 1; i <= totalPreguntas; i++) {
        let estado = 'Sin responder';
        let clase = 'text-muted';
        
        if (respuestasGuardadas[`pregunta_${i}`]) {
            estado = 'Respondida';
            clase = 'text-success';
        }
        
        if (preguntasMarcadas.has(i)) {
            estado += ' (Marcada)';
            clase = 'text-warning';
        }
        
        resumenHTML += `<div class="${clase}">Pregunta ${i}: ${estado}</div>`;
    }
    
    document.getElementById('resumen-preguntas').innerHTML = resumenHTML;
}

// Mostrar confirmación de envío
function mostrarConfirmacionEnvio() {
    actualizarEstadisticas();
    
    const respondidas = Object.keys(respuestasGuardadas).length;
    const sinResponder = totalPreguntas - respondidas;
    
    let resumenFinal = `
        <div class="alert alert-info">
            <strong>Preguntas respondidas:</strong> ${respondidas} de ${totalPreguntas}<br>
            <strong>Preguntas sin responder:</strong> ${sinResponder}
        </div>
    `;
    
    if (sinResponder > 0) {
        resumenFinal += `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Tienes ${sinResponder} pregunta(s) sin responder.
            </div>
        `;
    }
    
    document.getElementById('resumen-final').innerHTML = resumenFinal;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    modal.show();
}

// Confirmar envío desde resumen
function confirmarEnvio() {
    bootstrap.Modal.getInstance(document.getElementById('modalResumen')).hide();
    mostrarConfirmacionEnvio();
}

// Enviar examen
function enviarExamen() {
    clearInterval(cronometroInterval);
    
    // Remover advertencia de salida
    window.removeEventListener('beforeunload', function() {});
    
    // Agregar tiempo de finalización
    const ahora = new Date();
    const tiempoTranscurrido = Math.floor((ahora - tiempoInicio) / 1000);
    
    const inputTiempo = document.createElement('input');
    inputTiempo.type = 'hidden';
    inputTiempo.name = 'tiempo_transcurrido';
    inputTiempo.value = tiempoTranscurrido;
    document.getElementById('formulario-examen').appendChild(inputTiempo);
    
    // Enviar formulario
    document.getElementById('formulario-examen').submit();
}

// Guardar/cargar en localStorage
function guardarEnLocalStorage() {
    const datos = {
        respuestas: respuestasGuardadas,
        marcadas: Array.from(preguntasMarcadas),
        tiempoInicio: tiempoInicio.getTime()
    };
    
    localStorage.setItem(`examen_${<?= $examen['id_examen'] ?>}`, JSON.stringify(datos));
}

function cargarRespuestasGuardadas() {
    const datos = localStorage.getItem(`examen_${<?= $examen['id_examen'] ?>}`);
    
    if (datos) {
        const parsed = JSON.parse(datos);
        respuestasGuardadas = parsed.respuestas || {};
        preguntasMarcadas = new Set(parsed.marcadas || []);
        
        // Restaurar respuestas en la interfaz
        Object.keys(respuestasGuardadas).forEach(key => {
            const idPregunta = key.split('_')[1];
            const respuestas = respuestasGuardadas[key];
            
            respuestas.forEach(valor => {
                const elemento = document.querySelector(`[name="respuesta_${idPregunta}"][value="${valor}"], [name="respuesta_${idPregunta}[]"][value="${valor}"]`);
                if (elemento) {
                    elemento.checked = true;
                } else {
                    // Para campos de texto (desarrollo)
                    const campoTexto = document.querySelector(`[name="respuesta_${idPregunta}"]`);
                    if (campoTexto && campoTexto.type === 'textarea') {
                        campoTexto.value = valor;
                    }
                }
            });
        });
        
        // Restaurar marcadores
        preguntasMarcadas.forEach(numero => {
            const checkbox = document.getElementById(`marcar-${numero}`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
}

// Limpiar localStorage al enviar
window.addEventListener('beforeunload', function() {
    if (document.getElementById('formulario-examen').classList.contains('enviado')) {
        localStorage.removeItem(`examen_${<?= $examen['id_examen'] ?>}`);
    }
});
</script>

<?php require_once __DIR__ . '/../comunes/footer.php'; ?>

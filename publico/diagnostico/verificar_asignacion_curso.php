<?php
/**
 * Script para verificar la asignación de cursos a alumnos
 * AUTOEXAM2 - 18/06/2025
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/modelos/curso_modelo.php';

// Imprimir cabecera
echo "<h1>Verificación de Asignación de Curso a Alumno</h1>";

// ID del alumno a verificar (modificar según se necesite)
$id_alumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : 0;
$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

// Crear instancia del modelo
$cursoModelo = new Curso();

// Mostrar formulario
echo "<div style='margin: 20px 0; padding: 20px; border: 1px solid #ccc;'>";
echo "<h2>Formulario de Prueba</h2>";
echo "<form method='get'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>ID Alumno: </label>";
echo "<input type='number' name='id_alumno' value='{$id_alumno}' required>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>ID Curso: </label>";
echo "<input type='number' name='id_curso' value='{$id_curso}' required>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Acción: </label>";
echo "<select name='accion'>";
echo "<option value=''>Seleccionar...</option>";
echo "<option value='asignar'" . ($accion == 'asignar' ? " selected" : "") . ">Asignar</option>";
echo "<option value='desasignar'" . ($accion == 'desasignar' ? " selected" : "") . ">Desasignar</option>";
echo "<option value='verificar'" . ($accion == 'verificar' ? " selected" : "") . ">Verificar</option>";
echo "</select>";
echo "</div>";
echo "<button type='submit'>Ejecutar</button>";
echo "</form>";
echo "</div>";

// Ejecutar acción seleccionada
if ($id_alumno > 0) {
    // Verificar curso actual
    $cursoActual = $cursoModelo->obtenerCursoDeAlumno($id_alumno);
    echo "<h2>Información actual</h2>";
    echo "<p>Alumno ID: {$id_alumno}</p>";
    echo "<p>Curso asignado actualmente: " . ($cursoActual ? $cursoActual : "Ninguno") . "</p>";
    
    // Ejecutar acción específica
    if ($accion == 'asignar' && $id_curso > 0) {
        $resultado = $cursoModelo->asignarAlumno($id_curso, $id_alumno);
        echo "<h2>Resultado de la asignación</h2>";
        echo "<p>Asignando curso {$id_curso} a alumno {$id_alumno}: " . 
             ($resultado ? "ÉXITO" : "FALLO") . "</p>";
    } else if ($accion == 'desasignar' && $cursoActual) {
        $resultado = $cursoModelo->desasignarAlumno($cursoActual, $id_alumno);
        echo "<h2>Resultado de la desasignación</h2>";
        echo "<p>Desasignando curso {$cursoActual} de alumno {$id_alumno}: " . 
             ($resultado ? "ÉXITO" : "FALLO") . "</p>";
    }
    
    // Verificar de nuevo después de la acción
    if ($accion == 'asignar' || $accion == 'desasignar') {
        $cursoNuevo = $cursoModelo->obtenerCursoDeAlumno($id_alumno);
        echo "<h2>Estado después de la acción</h2>";
        echo "<p>Curso asignado ahora: " . ($cursoNuevo ? $cursoNuevo : "Ninguno") . "</p>";
    }
}

// Mostrar contenido de la tabla curso_alumno
echo "<h2>Contenido de la tabla curso_alumno</h2>";
try {
    $query = "SELECT * FROM curso_alumno LIMIT 100";
    $resultado = $GLOBALS['db']->query($query);
    
    if ($resultado->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID Relación</th><th>ID Curso</th><th>ID Alumno</th><th>Fecha Asignación</th></tr>";
        
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $fila['id_relacion'] . "</td>";
            echo "<td>" . $fila['id_curso'] . "</td>";
            echo "<td>" . $fila['id_alumno'] . "</td>";
            echo "<td>" . $fila['fecha_asignacion'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay registros en la tabla curso_alumno.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error al consultar la tabla: " . $e->getMessage() . "</p>";
}

// Información de depuración adicional
echo "<h2>Información de depuración</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Conexión a base de datos disponible: " . (isset($GLOBALS['db']) ? "Sí" : "No") . "\n";
if (isset($GLOBALS['db'])) {
    echo "Conexión a base de datos tipo: " . get_class($GLOBALS['db']) . "\n";
}
echo "</pre>";

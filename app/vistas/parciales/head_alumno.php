<?php
/**
 * Head para vistas del alumno - AUTOEXAM2
 * 
 * Contiene los metadatos y enlaces a recursos para las vistas del alumno
 * 
 * @author GitHub Copilot
 * @version 1.0
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= isset($datos['titulo']) ? $datos['titulo'] . ' - ' . SYSTEM_NAME : SYSTEM_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    
    <!-- Estilos para navbar comunes -->
    <link href="<?= BASE_URL ?>/recursos/css/navbar.css?v=<?= time() ?>" rel="stylesheet">
    
    <!-- Estilos personalizados unificados -->
    <link href="<?= BASE_URL ?>/recursos/css/estilos.css?v=<?= time() ?>" rel="stylesheet">
    <link href="<?= BASE_URL ?>/recursos/css/alumno.css?v=<?= time() ?>" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/publico/recursos/imagenes/favicon.ico" type="image/x-icon">
    
    <!-- Tema color para dispositivos móviles -->
    <meta name="theme-color" content="#5CB85C">
</head>
<body class="alumno-dashboard">

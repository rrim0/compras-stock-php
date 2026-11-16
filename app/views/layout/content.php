<?php
// Área de contenido
defined('APP') or exit;

if (!isset($rutas[$module])) {
    echo '<section class="content pt-3"><div class="alert alert-warning">'
       . '<i class="fas fa-exclamation-triangle"></i> La página solicitada no existe. '
       . '<a href="main.php?module=start">Volver al inicio</a></div></section>';
    return;
}

[$archivo, $permiso] = $rutas[$module];

if (!can($permiso)) {
    echo '<section class="content pt-3"><div class="alert alert-danger">'
       . '<i class="fas fa-ban"></i> No tiene permiso para acceder a esta sección. '
       . '<a href="main.php?module=start">Volver al inicio</a></div></section>';
    return;
}

include __DIR__ . '/../' . $archivo;

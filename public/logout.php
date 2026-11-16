<?php
// Cierra la sesión
require_once __DIR__ . '/../app/core/session.php';

$token = $_POST['csrf'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_string($token) && hash_equals(csrf_token(), $token)) {
    cerrar_sesion();
    redirect('index.php?alert=salir');
}
redirect(is_logged() ? 'main.php' : 'index.php');

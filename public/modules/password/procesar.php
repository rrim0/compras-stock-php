<?php
// Procesa el cambio de contraseña
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
csrf_check();

$volver  = '../../main.php?module=password';
$actual  = $_POST['old_pass'] ?? '';
$nueva   = $_POST['new_pass'] ?? '';
$repetir = $_POST['retype_pass'] ?? '';

if (!is_string($actual) || !is_string($nueva) || !is_string($repetir) || $actual === '' || $nueva === '') {
    flash('warning', 'Complete todos los campos.');
    redirect($volver);
}
if (strlen($nueva) < 8 || strlen($nueva) > 72) {
    flash('warning', 'La contraseña nueva debe tener entre 8 y 72 caracteres.');
    redirect($volver);
}
if ($nueva !== $repetir) {
    flash('warning', 'La contraseña nueva y su repetición no coinciden.');
    redirect($volver);
}

$pdo = db();
$st  = $pdo->prepare('SELECT password FROM usuarios WHERE id_usuario = ?');
$st->execute([$_SESSION['id_usuario']]);
$hash = (string) $st->fetchColumn();

if (!password_verify($actual, $hash)) {
    flash('danger', 'La contraseña actual es incorrecta.');
    redirect($volver);
}
if (password_verify($nueva, $hash)) {
    flash('warning', 'La contraseña nueva debe ser distinta de la actual.');
    redirect($volver);
}

$pdo->prepare('UPDATE usuarios SET password = ? WHERE id_usuario = ?')
    ->execute([password_hash($nueva, PASSWORD_DEFAULT), $_SESSION['id_usuario']]);

session_regenerate_id(true);
flash('success', 'La contraseña se cambió correctamente.');
redirect($volver);

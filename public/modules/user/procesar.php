<?php
// Procesa los formularios de usuarios
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('user');
csrf_check();

$act    = $_GET['act'] ?? '';
$lista  = '../../main.php?module=user';
$pdo    = db();
$id     = int_param($_POST, 'id');
$propio = $id === (int) $_SESSION['id_usuario'];

// Activar / bloquear
if ($act === 'on' || $act === 'off') {
    if ($id === 0 || $propio) {
        flash('warning', 'No puede cambiar el estado de su propio usuario.');
        redirect($lista);
    }
    $estado = $act === 'on' ? 'activo' : 'bloqueado';
    // Al activar se reinician los intentos fallidos
    $st = $pdo->prepare('UPDATE usuarios SET estado = ?, intentos = 0 WHERE id_usuario = ?');
    $st->execute([$estado, $id]);
    flash($st->rowCount() ? 'success' : 'warning',
        $st->rowCount() ? 'Usuario ' . ($act === 'on' ? 'desbloqueado/activado' : 'bloqueado') . ' correctamente.' : 'El usuario no existe o no hubo cambios.');
    redirect($lista);
}

if ($act !== 'insert' && $act !== 'update') {
    redirect($lista);
}

// Agregar / modificar: validación en el servidor
$usuario  = post_str('usuario');
$nombre   = post_str('nombre');
$email    = post_str('email');
$telefono = post_str('telefono');
$id_rol   = int_param($_POST, 'id_rol');
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

$volver = $act === 'insert'
    ? '../../main.php?module=form_user&form=add'
    : '../../main.php?module=form_user&form=edit&id=' . $id;

$errores = [];
if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $usuario)) {
    $errores[] = 'El usuario debe tener de 3 a 50 caracteres (letras, números, punto o guion bajo).';
}
if ($nombre === '' || largo($nombre) > 100) {
    $errores[] = 'El nombre es obligatorio (máximo 100 caracteres).';
}
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || largo($email) > 100)) {
    $errores[] = 'El email no es válido.';
}
if ($telefono !== '' && !preg_match('/^[0-9+\-() ]{6,20}$/', $telefono)) {
    $errores[] = 'El teléfono no es válido.';
}
if ($act === 'insert' || $password !== '') {
    if (strlen($password) < 8 || strlen($password) > 72) {
        $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres.';
    }
}
if ($act === 'update' && $propio) {
    $id_rol = (int) $_SESSION['id_rol']; // el usuario no cambia su propio rol
}
$st = $pdo->prepare('SELECT 1 FROM roles WHERE id_rol = ?');
$st->execute([$id_rol]);
if (!$st->fetchColumn()) {
    $errores[] = 'Seleccione un rol válido.';
}
if ($act === 'update' && $id === 0) {
    $errores[] = 'Usuario no válido.';
}

if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($volver);
}

$email    = $email !== '' ? $email : null;
$telefono = $telefono !== '' ? $telefono : null;

try {
    if ($act === 'insert') {
        $st = $pdo->prepare('INSERT INTO usuarios (usuario, nombre, password, email, telefono, id_rol, estado)
                             VALUES (?, ?, ?, ?, ?, ?, \'activo\')');
        $st->execute([$usuario, $nombre, password_hash($password, PASSWORD_DEFAULT), $email, $telefono, $id_rol]);
        flash('success', 'Usuario agregado correctamente.');
    } else {
        if ($password !== '') {
            $st = $pdo->prepare('UPDATE usuarios SET usuario = ?, nombre = ?, email = ?, telefono = ?, id_rol = ?, password = ?
                                 WHERE id_usuario = ?');
            $st->execute([$usuario, $nombre, $email, $telefono, $id_rol, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $st = $pdo->prepare('UPDATE usuarios SET usuario = ?, nombre = ?, email = ?, telefono = ?, id_rol = ?
                                 WHERE id_usuario = ?');
            $st->execute([$usuario, $nombre, $email, $telefono, $id_rol, $id]);
        }
        if ($propio) {
            $_SESSION['usuario'] = $usuario;
        }
        flash('success', 'Usuario modificado correctamente.');
    }
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'El nombre de usuario "' . $usuario . '" ya existe. Elija otro.');
        redirect($volver);
    }
    throw $ex;
}

redirect($lista);

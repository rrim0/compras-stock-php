<?php
// Procesa la modificación del perfil y la foto del usuario
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
csrf_check();

$volver = '../../main.php?module=form_perfil';
$id     = (int) $_SESSION['id_usuario'];

$nombre   = post_str('nombre');
$email    = post_str('email');
$telefono = post_str('telefono');

$errores = [];
if ($nombre === '' || largo($nombre) > 100) {
    $errores[] = 'El nombre es obligatorio (máximo 100 caracteres).';
}
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || largo($email) > 100)) {
    $errores[] = 'El email no es válido.';
}
if ($telefono !== '' && !preg_match('/^[0-9+\-() ]{6,20}$/', $telefono)) {
    $errores[] = 'El teléfono no es válido.';
}

// Foto: se valida que sea una imagen real
$foto_nueva = null;
$archivo    = $_FILES['foto'] ?? null;

if (is_array($archivo) && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if (!is_int($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'No se pudo subir la foto (máximo 1 MB).';
    } elseif ($archivo['size'] > FOTO_MAX_BYTES) {
        $errores[] = 'La foto supera el tamaño máximo de 1 MB.';
    } else {
        $tipos  = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $imagen = @getimagesize($archivo['tmp_name']); // lee el contenido real del archivo
        // Tipo de archivo con finfo o getimagesize
        $mime   = class_exists('finfo')
            ? (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name'])
            : ($imagen['mime'] ?? '');
        if (!isset($tipos[$mime]) || $imagen === false || ($imagen['mime'] ?? '') !== $mime) {
            $errores[] = 'La foto debe ser una imagen JPG o PNG.';
        } else {
            $foto_nueva = 'u' . $id . '_' . bin2hex(random_bytes(8)) . '.' . $tipos[$mime];
        }
    }
}

if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($volver);
}

$pdo = db();

if ($foto_nueva !== null) {
    if (!is_dir(FOTO_DIR) || !move_uploaded_file($archivo['tmp_name'], FOTO_DIR . $foto_nueva)) {
        flash('danger', 'No se pudo guardar la foto en el servidor.');
        redirect($volver);
    }
    // se elimina la foto anterior
    $st = $pdo->prepare('SELECT foto FROM usuarios WHERE id_usuario = ?');
    $st->execute([$id]);
    $anterior = $st->fetchColumn();
    if ($anterior && is_file(FOTO_DIR . basename($anterior))) {
        unlink(FOTO_DIR . basename($anterior));
    }
    $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, foto = ? WHERE id_usuario = ?')
        ->execute([$nombre, $email ?: null, $telefono ?: null, $foto_nueva, $id]);
    $_SESSION['foto'] = $foto_nueva;
} else {
    $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, telefono = ? WHERE id_usuario = ?')
        ->execute([$nombre, $email ?: null, $telefono ?: null, $id]);
}

$_SESSION['nombre'] = $nombre;
flash('success', 'Perfil actualizado correctamente.');
redirect('../../main.php?module=perfil');

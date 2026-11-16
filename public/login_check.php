<?php
// Login: verifica usuario y contraseña
require_once __DIR__ . '/../app/core/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Token CSRF del formulario de login
$token = $_POST['csrf'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    redirect('index.php?alert=token');
}

$usuario  = post_str('usuario');
$password = $_POST['password'] ?? '';

if ($usuario === '' || !is_string($password) || $password === '') {
    redirect('index.php?alert=vacio');
}

$pdo = db();
$st  = $pdo->prepare('SELECT u.id_usuario, u.usuario, u.nombre, u.password, u.id_rol, r.rol_nombre,
                             u.estado, u.foto, u.intentos
                      FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
                      WHERE u.usuario = ?');
$st->execute([$usuario]);
$u = $st->fetch();

// Si el usuario no existe se responde igual que con clave incorrecta
if (!$u) {
    password_verify($password, '$2y$10$/jLGWrNOhV8l05Xn0lMD0OO7XzqSofCy.w/wGctJZEcmwg7jmpFJy');
    redirect('index.php?alert=error');
}

$clave_ok = password_verify($password, $u['password']);

// Usuario bloqueado: solo el administrador lo desbloquea
if ($u['estado'] !== 'activo') {
    redirect('index.php?alert=' . ($clave_ok ? 'bloqueado' : 'error'));
}

if (!$clave_ok) {
    // Clave incorrecta: suma un intento y bloquea al llegar al máximo
    $up = $pdo->prepare("UPDATE usuarios
                         SET estado = IF(intentos + 1 >= ?, 'bloqueado', estado),
                             intentos = intentos + 1
                         WHERE id_usuario = ?");
    $up->execute([LOGIN_MAX_INTENTOS, $u['id_usuario']]);

    $st = $pdo->prepare('SELECT estado FROM usuarios WHERE id_usuario = ?');
    $st->execute([$u['id_usuario']]);
    redirect('index.php?alert=' . ($st->fetchColumn() === 'bloqueado' ? 'intentos' : 'error'));
}

// Acceso correcto: reinicia los intentos y crea la sesión
$pdo->prepare('UPDATE usuarios SET intentos = 0 WHERE id_usuario = ?')->execute([$u['id_usuario']]);

if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
    $pdo->prepare('UPDATE usuarios SET password = ? WHERE id_usuario = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $u['id_usuario']]);
}

session_regenerate_id(true); // nuevo ID de sesión
$_SESSION['id_usuario']    = (int) $u['id_usuario'];
$_SESSION['usuario']       = $u['usuario'];
$_SESSION['nombre']        = $u['nombre'];
$_SESSION['id_rol']        = (int) $u['id_rol'];
$_SESSION['rol']           = $u['rol_nombre'];
$_SESSION['foto']          = $u['foto'];
$_SESSION['ultimo_acceso'] = time();
unset($_SESSION['csrf']); // token nuevo

redirect('main.php?module=start');

<?php
// Interfaz de acceso al sistema (login)
require_once __DIR__ . '/../app/core/session.php';

if (is_logged()) {
    redirect('main.php');
}

$alertas = [
    'error'     => ['danger',  'Usuario o contraseña incorrectos. Después de ' . LOGIN_MAX_INTENTOS . ' intentos fallidos el usuario se bloquea.'],
    'vacio'     => ['warning', 'Ingrese su usuario y contraseña.'],
    'bloqueado' => ['danger',  'Su usuario está bloqueado. Contacte con el administrador.'],
    'intentos'  => ['danger',  'Superó los ' . LOGIN_MAX_INTENTOS . ' intentos permitidos: su usuario fue bloqueado. Solicite el desbloqueo al administrador.'],
    'salir'     => ['success', 'Ha cerrado la sesión correctamente.'],
    'sesion'    => ['warning', 'Debe iniciar sesión para acceder al sistema.'],
    'expirada'  => ['warning', 'Su sesión expiró por inactividad. Ingrese nuevamente.'],
    'token'     => ['warning', 'La página de ingreso expiró. Intente nuevamente.'],
];
$alerta = $alertas[$_GET['alert'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingreso | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <img src="assets/img/logo.png" alt="Logo" class="login-logo-img">
            <h1 class="h4 mt-2 mb-0"><b><?= e(APP_SHORT) ?></b> Compras y Stock</h1>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Ingrese sus credenciales para iniciar sesión</p>

            <?php if ($alerta): ?>
                <div class="alert alert-<?= $alerta[0] ?> alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= e($alerta[1]) ?>
                </div>
            <?php endif; ?>

            <form action="login_check.php" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <div class="input-group mb-3">
                    <input type="text" name="usuario" class="form-control" placeholder="Usuario"
                           maxlength="50" required autofocus>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña"
                           maxlength="255" required>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
            </form>
        </div>
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/adminlte/adminlte.min.js"></script>
</body>
</html>

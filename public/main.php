<?php
// Página principal: menús, contenido y footer
require_once __DIR__ . '/../app/core/session.php';
require_login();

$rutas  = require __DIR__ . '/../app/core/rutas.php';
$module = is_string($_GET['module'] ?? null) ? $_GET['module'] : 'start';
// Módulo de la página actual (marca la opción activa del menú)
$modulo_actual = $rutas[$module][1] ?? '';
$vistas = __DIR__ . '/../app/views/layout/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="assets/plugins/select2/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include $vistas . 'top_menu.php'; ?>
    <?php include $vistas . 'side_menu.php'; ?>

    <div class="content-wrapper">
        <?php include $vistas . 'content.php'; ?>
    </div>

    <?php include $vistas . 'footer.php'; ?>
</div>

<!-- Modal: confirmar salida del sistema -->
<div class="modal fade" id="modalSalir" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sign-out-alt"></i> Salir</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">¿Desea salir del sistema?</div>
            <div class="modal-footer">
                <form action="logout.php" method="post">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, salir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/adminlte/adminlte.min.js"></script>
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
<script src="assets/plugins/select2/select2.min.js"></script>
<script src="assets/plugins/select2/es.js"></script>
<script src="assets/js/app.js"></script>
<?php if ($module === 'form_compras'): ?>
<script src="assets/js/compras.js"></script>
<?php elseif ($module === 'form_ventas'): ?>
<script src="assets/js/ventas.js"></script>
<?php endif; ?>
</body>
</html>

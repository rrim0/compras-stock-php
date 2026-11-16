<?php
// Alta de timbrados y activar / desactivar
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('timbrado');
csrf_check();
require_once __DIR__ . '/../../../app/core/reporte_compras.php'; // fecha_valida()

$act   = $_GET['act'] ?? '';
$lista = '../../main.php?module=timbrado';
$pdo   = db();

if ($act === 'estado') {
    $st = $pdo->prepare("UPDATE timbrado SET estado = IF(estado = 'activo', 'inactivo', 'activo') WHERE id_timbrado = ?");
    $st->execute([int_param($_POST, 'id')]);
    flash($st->rowCount() ? 'success' : 'warning', $st->rowCount() ? 'Estado del timbrado actualizado.' : 'El timbrado no existe.');
    redirect($lista);
}

if ($act !== 'insert') {
    redirect($lista);
}

$nro    = post_str('nro_timbrado');
$est    = post_str('establecimiento');
$pto    = post_str('punto_expedicion');
$desde  = post_str('vigencia_desde');
$hasta  = post_str('vigencia_hasta');
$n_min  = filter_var($_POST['nro_desde'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9999999]]);
$n_max  = filter_var($_POST['nro_hasta'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9999999]]);

$errores = [];
if (!preg_match('/^[0-9]{8}$/', $nro)) {
    $errores[] = 'El número de timbrado debe tener 8 dígitos.';
}
if (!preg_match('/^[0-9]{3}$/', $est) || !preg_match('/^[0-9]{3}$/', $pto)) {
    $errores[] = 'Establecimiento y punto de expedición deben tener 3 dígitos.';
}
if (!fecha_valida($desde) || !fecha_valida($hasta)) {
    $errores[] = 'Ingrese fechas de vigencia válidas.';
} elseif ($desde > $hasta) {
    $errores[] = 'La vigencia "desde" no puede ser posterior a la vigencia "hasta".';
}
if ($n_min === false || $n_max === false) {
    $errores[] = 'La numeración debe estar entre 1 y 9999999.';
} elseif ($n_min > $n_max) {
    $errores[] = 'La numeración "desde" no puede ser mayor que la numeración "hasta".';
}
if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($lista);
}

try {
    $pdo->prepare("INSERT INTO timbrado (nro_timbrado, establecimiento, punto_expedicion, vigencia_desde, vigencia_hasta,
                                         nro_desde, nro_hasta, nro_actual, estado)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo')")
        ->execute([$nro, $est, $pto, $desde, $hasta, $n_min, $n_max, $n_min - 1]);
    flash('success', 'Timbrado registrado correctamente.');
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'Ya existe el timbrado ' . $nro . ' para el establecimiento ' . $est . ' y punto ' . $pto . '.');
        redirect($lista);
    }
    throw $ex;
}
redirect($lista);

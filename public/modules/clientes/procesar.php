<?php
// Procesa clientes (act=insert | update | delete)
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('clientes');
csrf_check();

$act   = $_GET['act'] ?? '';
$lista = '../../main.php?module=clientes';
$id    = int_param($_POST, 'id');
$pdo   = db();

if ($act === 'delete') {
    try {
        $st = $pdo->prepare('DELETE FROM clientes WHERE id_cliente = ?');
        $st->execute([$id]);
        flash($st->rowCount() ? 'success' : 'warning',
            $st->rowCount() ? 'Cliente eliminado correctamente.' : 'El cliente no existe.');
    } catch (PDOException $ex) {
        if (es_error_integridad($ex)) {
            flash('danger', 'No se puede eliminar el cliente porque tiene registros relacionados.');
            redirect($lista);
        }
        throw $ex;
    }
    redirect($lista);
}

if ($act !== 'insert' && $act !== 'update') {
    redirect($lista);
}

$ci_ruc    = post_str('ci_ruc');
$nombre    = post_str('nombre');
$apellido  = post_str('apellido');
$direccion = post_str('direccion');
$telefono  = post_str('telefono');
$id_ciudad = int_param($_POST, 'id_ciudad');
$volver    = $act === 'update'
    ? '../../main.php?module=form_clientes&form=edit&id=' . $id
    : '../../main.php?module=form_clientes&form=add';

$errores = [];
if (!preg_match('/^[0-9]{3,15}(-[0-9])?$/', $ci_ruc)) {
    $errores[] = 'El C.I./RUC no es válido (solo números y, si es RUC, guion y dígito verificador).';
}
if ($nombre === '' || largo($nombre) > 60) {
    $errores[] = 'El nombre es obligatorio (máximo 60 caracteres).';
}
if ($apellido === '' || largo($apellido) > 60) {
    $errores[] = 'El apellido es obligatorio (máximo 60 caracteres).';
}
if (largo($direccion) > 120) {
    $errores[] = 'La dirección admite hasta 120 caracteres.';
}
if ($telefono !== '' && !preg_match('/^[0-9+\-() ]{6,20}$/', $telefono)) {
    $errores[] = 'El teléfono no es válido.';
}
$st = $pdo->prepare('SELECT 1 FROM ciudad WHERE id_ciudad = ?');
$st->execute([$id_ciudad]);
if (!$st->fetchColumn()) {
    $errores[] = 'Seleccione una ciudad válida.';
}
if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($volver);
}

$datos = [$ci_ruc, $nombre, $apellido, $direccion ?: null, $telefono ?: null, $id_ciudad];
try {
    if ($act === 'insert') {
        $pdo->prepare('INSERT INTO clientes (ci_ruc, cli_nombre, cli_apellido, cli_direccion, cli_telefono, id_ciudad)
                       VALUES (?, ?, ?, ?, ?, ?)')->execute($datos);
        flash('success', 'Cliente agregado correctamente.');
    } else {
        $datos[] = $id;
        $pdo->prepare('UPDATE clientes SET ci_ruc = ?, cli_nombre = ?, cli_apellido = ?, cli_direccion = ?,
                       cli_telefono = ?, id_ciudad = ? WHERE id_cliente = ?')->execute($datos);
        flash('success', 'Cliente modificado correctamente.');
    }
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'Ya existe un cliente con el C.I./RUC "' . $ci_ruc . '".');
        redirect($volver);
    }
    throw $ex;
}
redirect($lista);

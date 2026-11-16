<?php
// ABM de proveedores (act=insert | update | delete)
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('proveedores');
csrf_check();

$act   = $_GET['act'] ?? '';
$lista = '../../main.php?module=proveedores';
$id    = int_param($_POST, 'id');
$pdo   = db();

if ($act === 'delete') {
    try {
        $st = $pdo->prepare('DELETE FROM proveedor WHERE id_proveedor = ?');
        $st->execute([$id]);
        flash($st->rowCount() ? 'success' : 'warning',
            $st->rowCount() ? 'Proveedor eliminado correctamente.' : 'El proveedor no existe.');
    } catch (PDOException $ex) {
        if (es_error_integridad($ex)) {
            flash('danger', 'No se puede eliminar el proveedor porque tiene compras registradas.');
            redirect($lista);
        }
        throw $ex;
    }
    redirect($lista);
}

if ($act !== 'insert' && $act !== 'update') {
    redirect($lista);
}

$ruc       = post_str('ruc');
$razon     = post_str('razon_social');
$direccion = post_str('direccion');
$telefono  = post_str('telefono');
$id_ciudad = int_param($_POST, 'id_ciudad');
$volver    = $act === 'update'
    ? '../../main.php?module=form_proveedores&form=edit&id=' . $id
    : '../../main.php?module=form_proveedores&form=add';

$errores = [];
if (!preg_match('/^[0-9]{5,15}-[0-9]$/', $ruc)) {
    $errores[] = 'El RUC no es válido (números, guion y dígito verificador. Ej.: 80012345-6).';
}
if ($razon === '' || largo($razon) > 100) {
    $errores[] = 'La razón social es obligatoria (máximo 100 caracteres).';
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
if ($act === 'update') {
    $st = $pdo->prepare('SELECT 1 FROM proveedor WHERE id_proveedor = ?');
    $st->execute([$id]);
    if (!$st->fetchColumn()) {
        $errores[] = 'El proveedor no existe.';
    }
}
if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($volver);
}

$datos = [$ruc, $razon, $direccion ?: null, $telefono ?: null, $id_ciudad];
try {
    if ($act === 'insert') {
        $pdo->prepare('INSERT INTO proveedor (ruc, razon_social, direccion, telefono, id_ciudad) VALUES (?, ?, ?, ?, ?)')->execute($datos);
        flash('success', 'Proveedor agregado correctamente.');
    } else {
        $datos[] = $id;
        $pdo->prepare('UPDATE proveedor SET ruc = ?, razon_social = ?, direccion = ?, telefono = ?, id_ciudad = ? WHERE id_proveedor = ?')->execute($datos);
        flash('success', 'Proveedor modificado correctamente.');
    }
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'Ya existe un proveedor con el RUC "' . $ruc . '".');
        redirect($volver);
    }
    throw $ex;
}
redirect($lista);

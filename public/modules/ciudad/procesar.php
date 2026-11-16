<?php
// Procesa el referencial Ciudad (act=insert | update | delete)
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('ciudad');
csrf_check();

$act   = $_GET['act'] ?? '';
$lista = '../../main.php?module=ciudad';
$id    = int_param($_POST, 'id');
$pdo   = db();

// Eliminar: solo si no está en uso
if ($act === 'delete') {
    try {
        $st = $pdo->prepare('DELETE FROM ciudad WHERE id_ciudad = ?');
        $st->execute([$id]);
        flash($st->rowCount() ? 'success' : 'warning',
            $st->rowCount() ? 'Ciudad eliminada correctamente.' : 'La ciudad no existe.');
    } catch (PDOException $ex) {
        if (es_error_integridad($ex)) {
            flash('danger', 'No se puede eliminar la ciudad porque está asignada a clientes o proveedores.');
            redirect($lista);
        }
        throw $ex;
    }
    redirect($lista);
}

if ($act !== 'insert' && $act !== 'update') {
    redirect($lista);
}

$descripcion = post_str('descripcion');
$id_dep      = int_param($_POST, 'id_departamento');
$volver      = $act === 'update'
    ? '../../main.php?module=form_ciudad&form=edit&id=' . $id
    : '../../main.php?module=form_ciudad&form=add';

$errores = [];
if ($descripcion === '' || largo($descripcion) > 60) {
    $errores[] = 'La descripción es obligatoria (máximo 60 caracteres).';
}
$st = $pdo->prepare('SELECT 1 FROM departamento WHERE id_departamento = ?');
$st->execute([$id_dep]);
if (!$st->fetchColumn()) {
    $errores[] = 'Seleccione un departamento válido.';
}
if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($volver);
}

try {
    if ($act === 'insert') {
        $pdo->prepare('INSERT INTO ciudad (descrip_ciudad, id_departamento) VALUES (?, ?)')->execute([$descripcion, $id_dep]);
        flash('success', 'Ciudad agregada correctamente.');
    } else {
        $pdo->prepare('UPDATE ciudad SET descrip_ciudad = ?, id_departamento = ? WHERE id_ciudad = ?')
            ->execute([$descripcion, $id_dep, $id]);
        flash('success', 'Ciudad modificada correctamente.');
    }
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'Ya existe la ciudad "' . $descripcion . '" en ese departamento.');
        redirect($volver);
    }
    throw $ex;
}
redirect($lista);

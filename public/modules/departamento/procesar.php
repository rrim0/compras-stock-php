<?php
// Procesa los datos del referencial Departamento (act=insert | update)
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('departamento');
csrf_check();

$act         = $_GET['act'] ?? '';
$lista       = '../../main.php?module=departamento';
$id          = int_param($_POST, 'id');
$descripcion = post_str('descripcion');
$volver      = $act === 'update'
    ? '../../main.php?module=form_departamento&form=edit&id=' . $id
    : '../../main.php?module=form_departamento&form=add';

if ($act !== 'insert' && $act !== 'update') {
    redirect($lista);
}
if ($descripcion === '' || largo($descripcion) > 60) {
    flash('warning', 'La descripción es obligatoria (máximo 60 caracteres).');
    redirect($volver);
}

$pdo = db();
try {
    if ($act === 'insert') {
        $pdo->prepare('INSERT INTO departamento (dep_descripcion) VALUES (?)')->execute([$descripcion]);
        flash('success', 'Departamento agregado correctamente.');
    } else {
        $st = $pdo->prepare('UPDATE departamento SET dep_descripcion = ? WHERE id_departamento = ?');
        $st->execute([$descripcion, $id]);
        flash('success', 'Departamento modificado correctamente.');
    }
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'Ya existe un departamento con la descripción "' . $descripcion . '".');
        redirect($volver);
    }
    throw $ex;
}
redirect($lista);

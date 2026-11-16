<?php
// Procesa los referenciales de un solo campo
require_once __DIR__ . '/../../../app/core/session.php';
require_once __DIR__ . '/../../../app/core/simples.php';
require_login();

$ref = is_string($_GET['ref'] ?? null) ? $_GET['ref'] : '';
if (!isset(SIMPLES[$ref])) {
    redirect('../../main.php?module=start');
}
require_area($ref);
csrf_check();

$c     = SIMPLES[$ref];
$act   = $_GET['act'] ?? '';
$lista = '../../main.php?module=' . $ref;
$id    = int_param($_POST, 'id');
$pdo   = db();

// Baja: no se elimina si el registro está en uso
if ($act === 'delete') {
    try {
        $st = $pdo->prepare("DELETE FROM {$c['tabla']} WHERE {$c['pk']} = ?");
        $st->execute([$id]);
        flash($st->rowCount() ? 'success' : 'warning', $st->rowCount() ? $c['ok_baja'] : $c['no_existe']);
    } catch (PDOException $ex) {
        if (es_error_integridad($ex)) {
            flash('danger', $c['en_uso']);
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
$volver      = '../../main.php?module=form_' . $ref . ($act === 'update' ? '&form=edit&id=' . $id : '&form=add');

if ($descripcion === '' || largo($descripcion) > $c['max']) {
    flash('warning', 'La descripción es obligatoria (máximo ' . $c['max'] . ' caracteres).');
    redirect($volver);
}

try {
    if ($act === 'insert') {
        $pdo->prepare("INSERT INTO {$c['tabla']} ({$c['campo']}) VALUES (?)")->execute([$descripcion]);
        flash('success', $c['ok_alta']);
    } else {
        $st = $pdo->prepare("SELECT 1 FROM {$c['tabla']} WHERE {$c['pk']} = ?");
        $st->execute([$id]);
        if (!$st->fetchColumn()) {
            flash('warning', $c['no_existe']);
            redirect($lista);
        }
        $pdo->prepare("UPDATE {$c['tabla']} SET {$c['campo']} = ? WHERE {$c['pk']} = ?")->execute([$descripcion, $id]);
        flash('success', $c['ok_mod']);
    }
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', $c['duplicado'] . ' "' . $descripcion . '".');
        redirect($volver);
    }
    throw $ex;
}
redirect($lista);

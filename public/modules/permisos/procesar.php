<?php
// Guarda los permisos de un rol
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('permisos');
csrf_check();

$pdo    = db();
$id_rol = int_param($_POST, 'id_rol');
$volver = '../../main.php?module=permisos&rol=' . $id_rol;

$st = $pdo->prepare('SELECT 1 FROM roles WHERE id_rol = ?');
$st->execute([$id_rol]);
if (!$st->fetchColumn()) {
    flash('warning', 'El rol no existe.');
    redirect('../../main.php?module=permisos');
}
if ($id_rol === ROL_SUPER_ADMIN) {
    flash('warning', 'Los permisos del rol Super Admin no se pueden modificar.');
    redirect($volver);
}

// Solo se aceptan opciones válidas del menú
$validos = $pdo->query("SELECT id_menu, modulo FROM menu WHERE modulo IS NOT NULL")->fetchAll(PDO::FETCH_KEY_PAIR);
$elegidos = [];
foreach ((array) ($_POST['menus'] ?? []) as $v) {
    $id = filter_var($v, FILTER_VALIDATE_INT);
    if ($id !== false && isset($validos[$id])) {
        $elegidos[$id] = true;
    }
}
// Inicio es obligatorio para todos los roles
$inicio = array_search('start', $validos, true);
if ($inicio !== false) {
    $elegidos[(int) $inicio] = true;
}

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM permisos WHERE id_rol = ?')->execute([$id_rol]);
    $ins = $pdo->prepare('INSERT INTO permisos (id_rol, id_menu) VALUES (?, ?)');
    foreach (array_keys($elegidos) as $id_menu) {
        $ins->execute([$id_rol, $id_menu]);
    }
    $pdo->commit();
    flash('success', 'Permisos guardados: ' . count($elegidos) . ' opción(es) asignada(s). Los usuarios de este rol verán el cambio en su próximo clic.');
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al guardar permisos: ' . $ex->getMessage());
    flash('danger', 'No se pudieron guardar los permisos.');
}
redirect($volver);

<?php
// ABM de productos (act=insert | update | delete)
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('productos');
csrf_check();

$act   = $_GET['act'] ?? '';
$lista = '../../main.php?module=productos';
$id    = int_param($_POST, 'id');
$pdo   = db();

// Baja: no se elimina un producto con compras o stock
if ($act === 'delete') {
    try {
        $st = $pdo->prepare('DELETE FROM producto WHERE cod_producto = ?');
        $st->execute([$id]);
        flash($st->rowCount() ? 'success' : 'warning',
            $st->rowCount() ? 'Producto eliminado correctamente.' : 'El producto no existe.');
    } catch (PDOException $ex) {
        if (es_error_integridad($ex)) {
            flash('danger', 'No se puede eliminar el producto porque tiene compras, ventas o stock registrados.');
            redirect($lista);
        }
        throw $ex;
    }
    redirect($lista);
}

if ($act !== 'insert' && $act !== 'update') {
    redirect($lista);
}

// Alta / modificación: validación en el servidor
$descripcion = post_str('descripcion');
$id_tipo     = int_param($_POST, 'id_tipo_producto');
$id_medida   = int_param($_POST, 'id_u_medida');
$precio      = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999999999]]);
$precio_venta = filter_var($_POST['precio_venta'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999999999]]);
$iva          = filter_var($_POST['iva'] ?? null, FILTER_VALIDATE_INT);
$volver      = $act === 'update'
    ? '../../main.php?module=form_productos&form=edit&id=' . $id
    : '../../main.php?module=form_productos&form=add';

$errores = [];
if (largo($descripcion) < 3 || largo($descripcion) > 100) {
    $errores[] = 'La descripción es obligatoria (de 3 a 100 caracteres).';
}
if ($precio === false) {
    $errores[] = 'El precio de compra debe ser un número entero mayor a cero.';
}
if ($precio_venta === false) {
    $errores[] = 'El precio de venta debe ser un número entero mayor a cero.';
}
if (!in_array($iva, [10, 5, 0], true)) {
    $errores[] = 'Seleccione una tasa de IVA válida (10 %, 5 % o exento).';
}
$st = $pdo->prepare('SELECT 1 FROM tipo_producto WHERE id_tipo_producto = ?');
$st->execute([$id_tipo]);
if (!$st->fetchColumn()) {
    $errores[] = 'Seleccione un tipo de producto válido.';
}
$st = $pdo->prepare('SELECT 1 FROM u_medida WHERE id_u_medida = ?');
$st->execute([$id_medida]);
if (!$st->fetchColumn()) {
    $errores[] = 'Seleccione una unidad de medida válida.';
}
if ($act === 'update') {
    $st = $pdo->prepare('SELECT 1 FROM producto WHERE cod_producto = ?');
    $st->execute([$id]);
    if (!$st->fetchColumn()) {
        $errores[] = 'El producto no existe.';
    }
}
if ($errores) {
    flash('warning', implode(' ', $errores));
    redirect($volver);
}

try {
    $st = $pdo->prepare('CALL sp_producto_guardar(?, ?, ?, ?, ?, ?, ?)');
    $st->execute([$act === 'insert' ? 0 : $id, $descripcion, $id_tipo, $id_medida, $precio, $precio_venta, $iva]);
    $st->closeCursor();
    flash('success', $act === 'insert' ? 'Producto agregado correctamente.' : 'Producto modificado correctamente.');
} catch (PDOException $ex) {
    if (es_error_integridad($ex)) {
        flash('warning', 'Ya existe un producto con la descripción "' . $descripcion . '".');
        redirect($volver);
    }
    throw $ex;
}
redirect($lista);

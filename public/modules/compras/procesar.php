<?php
// Agregar y anular compras
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('compras');
csrf_check();

$act     = $_GET['act'] ?? '';
$lista   = '../../main.php?module=compras';
$usuario = (int) $_SESSION['id_usuario'];
$pdo     = db();

// Agregar compra (en una transacción)
if ($act === 'insert') {
    $volver       = '../../main.php?module=form_compras';
    $fecha        = post_str('fecha');
    $nro_factura  = post_str('nro_factura');
    $id_proveedor = int_param($_POST, 'id_proveedor');
    $id_deposito  = int_param($_POST, 'id_deposito');

    $errores = [];
    $f = DateTime::createFromFormat('!Y-m-d', $fecha);
    if (!$f || $f->format('Y-m-d') !== $fecha) {
        $errores[] = 'La fecha no es válida.';
    } elseif ($fecha > date('Y-m-d')) {
        $errores[] = 'La fecha no puede ser posterior a hoy.';
    }
    if ($nro_factura === '' || largo($nro_factura) > 30) {
        $errores[] = 'El número de factura es obligatorio (máximo 30 caracteres).';
    }
    $st = $pdo->prepare('SELECT 1 FROM proveedor WHERE id_proveedor = ?');
    $st->execute([$id_proveedor]);
    if (!$st->fetchColumn()) {
        $errores[] = 'Seleccione un proveedor válido.';
    }
    $st = $pdo->prepare('SELECT 1 FROM deposito WHERE id_deposito = ?');
    $st->execute([$id_deposito]);
    if (!$st->fetchColumn()) {
        $errores[] = 'Seleccione un depósito válido.';
    }
    if ($errores) {
        flash('warning', implode(' ', $errores));
        redirect($volver);
    }

    try {
        $pdo->beginTransaction();

        // 1. Productos cargados por el usuario
        $st = $pdo->prepare('SELECT cod_producto, cantidad, precio FROM tmp WHERE id_usuario = ? ORDER BY cod_producto FOR UPDATE');
        $st->execute([$usuario]);
        $items = $st->fetchAll();

        if (!$items) {
            $pdo->rollBack();
            flash('warning', 'Debe agregar al menos un producto a la compra.');
            redirect($volver);
        }

        // El total se calcula en el servidor
        $total = 0;
        foreach ($items as $it) {
            $total += (int) $it['cantidad'] * (int) $it['precio'];
        }

        // 2. Cabecera (el trigger vacía la tabla temporal)
        $st = $pdo->prepare('INSERT INTO compra (fecha, hora, nro_factura, id_proveedor, id_deposito, id_usuario, total, estado)
                             VALUES (?, CURTIME(), ?, ?, ?, ?, ?, \'activo\')');
        $st->execute([$fecha, $nro_factura, $id_proveedor, $id_deposito, $usuario, $total]);
        $id_compra = (int) $pdo->lastInsertId();

        // 3. Detalle y aumento de stock
        $ins_det   = $pdo->prepare('INSERT INTO detalle_compra (id_compra, cod_producto, cantidad, precio) VALUES (?, ?, ?, ?)');
        $ins_stock = $pdo->prepare('INSERT INTO stock (id_deposito, cod_producto, cantidad) VALUES (?, ?, ?)
                                    ON DUPLICATE KEY UPDATE cantidad = cantidad + ?');
        foreach ($items as $it) {
            $ins_det->execute([$id_compra, $it['cod_producto'], $it['cantidad'], $it['precio']]);
            $ins_stock->execute([$id_deposito, $it['cod_producto'], $it['cantidad'], $it['cantidad']]);
        }

        $pdo->commit();
        flash('success', 'Compra Nº ' . $id_compra . ' registrada correctamente. Total: Gs. ' . gs($total));
        redirect('../../main.php?module=detalle_compras&id=' . $id_compra);
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // si falla, se deshace todo
        }
        if (es_error_integridad($ex)) {
            flash('warning', 'Ya existe una compra registrada con la factura "' . $nro_factura . '" de ese proveedor.');
            redirect($volver);
        }
        error_log('Error al registrar compra: ' . $ex->getMessage());
        flash('danger', 'No se pudo registrar la compra. Intente nuevamente.');
        redirect($volver);
    }
}

// Anular compra (en una transacción)
if ($act === 'anular') {
    $id = int_param($_POST, 'id');

    try {
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT id_deposito, estado FROM compra WHERE id_compra = ? FOR UPDATE');
        $st->execute([$id]);
        $compra = $st->fetch();

        if (!$compra || $compra['estado'] !== 'activo') {
            $pdo->rollBack();
            flash('warning', 'La compra no existe o ya fue anulada.');
            redirect($lista);
        }

        $st = $pdo->prepare('SELECT dc.cod_producto, dc.cantidad, p.p_descrip
                             FROM detalle_compra dc JOIN producto p ON p.cod_producto = dc.cod_producto
                             WHERE dc.id_compra = ? ORDER BY dc.cod_producto');
        $st->execute([$id]);
        $detalle = $st->fetchAll();

        // No se anula si parte de la compra ya se vendió: el stock no puede quedar negativo
        $sel_stock = $pdo->prepare('SELECT cantidad FROM stock WHERE id_deposito = ? AND cod_producto = ? FOR UPDATE');
        $upd_stock = $pdo->prepare('UPDATE stock SET cantidad = cantidad - ?
                                    WHERE id_deposito = ? AND cod_producto = ? AND cantidad >= ?');

        foreach ($detalle as $d) {
            $sel_stock->execute([$compra['id_deposito'], $d['cod_producto']]);
            $actual = (int) $sel_stock->fetchColumn();
            $upd_stock->execute([$d['cantidad'], $compra['id_deposito'], $d['cod_producto'], $d['cantidad']]);
            if ($upd_stock->rowCount() !== 1) {
                $pdo->rollBack();
                flash('danger', 'No se puede anular la compra: de "' . $d['p_descrip'] . '" quedan ' . gs($actual)
                    . ' en el depósito y la compra ingresó ' . gs($d['cantidad'])
                    . ' (parte ya fue vendida). No se modificó nada.');
                redirect($lista);
            }
        }

        $pdo->prepare("UPDATE compra SET estado = 'anulado', anulado_por = ?, fecha_anulacion = NOW() WHERE id_compra = ?")
            ->execute([$usuario, $id]);

        $pdo->commit();
        flash('success', 'Compra Nº ' . $id . ' anulada correctamente. El stock fue actualizado.');
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al anular compra: ' . $ex->getMessage());
        flash('danger', 'No se pudo anular la compra. Intente nuevamente.');
    }
    redirect($lista);
}

redirect($lista);

<?php
// Registrar y anular ventas
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('ventas');
csrf_check();

$act     = $_GET['act'] ?? '';
$lista   = '../../main.php?module=ventas';
$usuario = (int) $_SESSION['id_usuario'];
$pdo     = db();

// Registrar venta (en una transacción)
if ($act === 'insert') {
    $volver      = '../../main.php?module=form_ventas';
    $id_cliente  = int_param($_POST, 'id_cliente');
    $id_deposito = int_param($_POST, 'id_deposito');
    $condicion   = post_str('condicion');

    $errores = [];
    $st = $pdo->prepare('SELECT 1 FROM clientes WHERE id_cliente = ?');
    $st->execute([$id_cliente]);
    if (!$st->fetchColumn()) {
        $errores[] = 'Seleccione un cliente válido.';
    }
    $st = $pdo->prepare('SELECT 1 FROM deposito WHERE id_deposito = ?');
    $st->execute([$id_deposito]);
    if (!$st->fetchColumn()) {
        $errores[] = 'Seleccione un depósito válido.';
    }
    if (!in_array($condicion, ['contado', 'credito'], true)) {
        $errores[] = 'Seleccione la condición de venta.';
    }
    if ($errores) {
        flash('warning', implode(' ', $errores));
        redirect($volver);
    }

    try {
        $pdo->beginTransaction();

        // 1. Timbrado vigente y número de factura
        $t = timbrado_vigente($pdo, true);
        if ($t === null) {
            $pdo->rollBack();
            flash('danger', 'No se registró la venta: no hay un timbrado activo, vigente y con numeración disponible. Registre uno en Referenciales de Ventas > Timbrados.');
            redirect($volver);
        }
        $numero  = max((int) $t['nro_actual'], (int) $t['nro_desde'] - 1) + 1;
        $factura = nro_factura($t['establecimiento'], $t['punto_expedicion'], $numero);

        // 2. Productos cargados por el usuario
        $st = $pdo->prepare('SELECT t.cod_producto, t.cantidad, t.precio, p.iva, p.p_descrip
                             FROM tmp_venta t JOIN producto p ON p.cod_producto = t.cod_producto
                             WHERE t.id_usuario = ? ORDER BY t.cod_producto FOR UPDATE');
        $st->execute([$usuario]);
        $items = $st->fetchAll();
        if (!$items) {
            $pdo->rollBack();
            flash('warning', 'Debe agregar al menos un producto a la venta.');
            redirect($volver);
        }

        // 3. Descuenta el stock solo si alcanza
        $sel_stock = $pdo->prepare('SELECT cantidad FROM stock WHERE id_deposito = ? AND cod_producto = ? FOR UPDATE');
        $upd_stock = $pdo->prepare('UPDATE stock SET cantidad = cantidad - ?
                                    WHERE id_deposito = ? AND cod_producto = ? AND cantidad >= ?');
        $total = 0;
        $base  = [10 => 0, 5 => 0, 0 => 0]; // importes por tasa de IVA
        foreach ($items as $it) {
            $sel_stock->execute([$id_deposito, $it['cod_producto']]);
            $disponible = (int) $sel_stock->fetchColumn();
            $upd_stock->execute([$it['cantidad'], $id_deposito, $it['cod_producto'], $it['cantidad']]);
            if ($upd_stock->rowCount() !== 1) {
                $pdo->rollBack(); // si falta stock, se deshace todo
                flash('danger', 'No se registró la venta: stock insuficiente de "' . $it['p_descrip'] . '" en el depósito (disponible: '
                    . gs($disponible) . ', solicitado: ' . gs($it['cantidad']) . '). No se modificó nada.');
                redirect($volver);
            }
            $subtotal = (int) $it['cantidad'] * (int) $it['precio'];
            $total   += $subtotal;
            $base[(int) $it['iva']] += $subtotal;
        }
        // IVA incluido: 10 % = importe / 11; 5 % = importe / 21
        $iva10 = (int) round($base[10] / 11);
        $iva5  = (int) round($base[5] / 21);

        // 4. Guarda el número, la cabecera y el detalle
        $pdo->prepare('UPDATE timbrado SET nro_actual = ? WHERE id_timbrado = ?')->execute([$numero, $t['id_timbrado']]);

        $st = $pdo->prepare('INSERT INTO venta (fecha, hora, id_timbrado, nro_factura, factura, condicion, id_cliente, id_deposito,
                                                id_usuario, total, gravada_10, gravada_5, exenta, iva_10, iva_5, estado)
                             VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'activo\')');
        $st->execute([$t['id_timbrado'], $numero, $factura, $condicion, $id_cliente, $id_deposito, $usuario,
                      $total, $base[10], $base[5], $base[0], $iva10, $iva5]);
        $id_venta = (int) $pdo->lastInsertId();

        $ins = $pdo->prepare('INSERT INTO detalle_venta (id_venta, cod_producto, cantidad, precio, iva) VALUES (?, ?, ?, ?, ?)');
        foreach ($items as $it) {
            $ins->execute([$id_venta, $it['cod_producto'], $it['cantidad'], $it['precio'], $it['iva']]);
        }

        $pdo->commit();
        flash('success', 'Venta registrada correctamente. Factura Nº ' . $factura . '. Total: Gs. ' . gs($total));
        redirect('../../main.php?module=detalle_ventas&id=' . $id_venta);
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al registrar venta: ' . $ex->getMessage());
        flash('danger', 'No se pudo registrar la venta. No se modificó nada. Intente nuevamente.');
        redirect($volver);
    }
}

// Anular venta (en una transacción)
if ($act === 'anular') {
    $id = int_param($_POST, 'id');

    try {
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT id_deposito, estado, factura FROM venta WHERE id_venta = ? FOR UPDATE');
        $st->execute([$id]);
        $venta = $st->fetch();

        if (!$venta || $venta['estado'] !== 'activo') {
            $pdo->rollBack();
            flash('warning', 'La venta no existe o ya fue anulada.');
            redirect($lista);
        }

        // Devuelve al stock lo vendido
        $st = $pdo->prepare('SELECT cod_producto, cantidad FROM detalle_venta WHERE id_venta = ? ORDER BY cod_producto');
        $st->execute([$id]);
        $dev = $pdo->prepare('INSERT INTO stock (id_deposito, cod_producto, cantidad) VALUES (?, ?, ?)
                              ON DUPLICATE KEY UPDATE cantidad = cantidad + ?');
        foreach ($st->fetchAll() as $d) {
            $dev->execute([$venta['id_deposito'], $d['cod_producto'], $d['cantidad'], $d['cantidad']]);
        }

        $pdo->prepare("UPDATE venta SET estado = 'anulado', anulado_por = ?, fecha_anulacion = NOW() WHERE id_venta = ?")
            ->execute([$usuario, $id]);

        $pdo->commit();
        flash('success', 'Factura Nº ' . $venta['factura'] . ' anulada correctamente. El stock fue restablecido.');
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al anular venta: ' . $ex->getMessage());
        flash('danger', 'No se pudo anular la venta. No se modificó nada. Intente nuevamente.');
    }
    redirect($lista);
}

redirect($lista);

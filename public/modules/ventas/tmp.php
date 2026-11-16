<?php
// Lista temporal de la venta: agrega, quita y calcula total e IVA
require_once __DIR__ . '/../../../app/core/session.php';
require_login(true);
require_area('ventas', true);

$act      = $_GET['act'] ?? 'list';
$usuario  = (int) $_SESSION['id_usuario'];
$deposito = int_param($act === 'list' ? $_GET : $_POST, 'deposito');
$pdo      = db();
$aviso    = '';

if ($act !== 'list') {
    csrf_check(true);
}

if ($act === 'add') {
    $cod      = int_param($_POST, 'cod');
    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);

    $st = $pdo->prepare('SELECT p.p_descrip, p.precio_venta, COALESCE(s.cantidad, 0) AS stock,
                                COALESCE((SELECT cantidad FROM tmp_venta WHERE id_usuario = ? AND cod_producto = p.cod_producto), 0) AS cargado
                         FROM producto p
                         LEFT JOIN stock s ON s.cod_producto = p.cod_producto AND s.id_deposito = ?
                         WHERE p.cod_producto = ?');
    $st->execute([$usuario, $deposito, $cod]);
    $p = $st->fetch();

    if (!$p) {
        $aviso = 'El producto seleccionado no existe.';
    } elseif ($deposito === 0) {
        $aviso = 'Seleccione primero el depósito.';
    } elseif ($cantidad === false) {
        $aviso = 'La cantidad debe ser un número entero mayor a cero.';
    } elseif ((int) $p['precio_venta'] <= 0) {
        $aviso = 'El producto "' . $p['p_descrip'] . '" no tiene precio de venta.';
    } elseif ((int) $p['cargado'] + $cantidad > (int) $p['stock']) {
        $aviso = 'Stock insuficiente de "' . $p['p_descrip'] . '": hay ' . gs($p['stock']) . ' en el depósito'
               . ((int) $p['cargado'] > 0 ? ' y ya cargó ' . gs($p['cargado']) : '') . '.';
    } else {
        // Se usa el precio de venta del producto
        $st = $pdo->prepare('INSERT INTO tmp_venta (cod_producto, cantidad, precio, id_usuario) VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE cantidad = cantidad + ?, precio = ?');
        $st->execute([$cod, $cantidad, $p['precio_venta'], $usuario, $cantidad, $p['precio_venta']]);
    }
} elseif ($act === 'del') {
    $pdo->prepare('DELETE FROM tmp_venta WHERE id_tmp = ? AND id_usuario = ?')->execute([int_param($_POST, 'id_tmp'), $usuario]);
} elseif ($act === 'vaciar') {
    $pdo->prepare('DELETE FROM tmp_venta WHERE id_usuario = ?')->execute([$usuario]);
}

// Detalle con subtotales, total e IVA
$st = $pdo->prepare('SELECT t.id_tmp, t.cod_producto, p.p_descrip, m.u_descrip, p.iva, t.cantidad, t.precio,
                            t.cantidad * t.precio AS subtotal, COALESCE(s.cantidad, 0) AS stock
                     FROM tmp_venta t
                     JOIN producto p ON p.cod_producto = t.cod_producto
                     JOIN u_medida m ON m.id_u_medida = p.id_u_medida
                     LEFT JOIN stock s ON s.cod_producto = t.cod_producto AND s.id_deposito = ?
                     WHERE t.id_usuario = ?
                     ORDER BY t.id_tmp');
$st->execute([$deposito, $usuario]);
$items = $st->fetchAll();

$total = 0;
$base  = [10 => 0, 5 => 0, 0 => 0];
$falta = false;
foreach ($items as $i) {
    $total += (int) $i['subtotal'];
    $base[(int) $i['iva']] += (int) $i['subtotal'];
    $falta = $falta || ($deposito > 0 && (int) $i['cantidad'] > (int) $i['stock']);
}
$iva10 = (int) round($base[10] / 11);
$iva5  = (int) round($base[5] / 21);

if ($aviso !== '') {
    echo '<div class="alert alert-warning py-2">' . e($aviso) . '</div>';
}
if ($falta) {
    echo '<div class="alert alert-danger py-2">Hay productos con cantidad mayor al stock del depósito seleccionado (en rojo). La venta no se podrá registrar.</div>';
}
?>
<table class="table table-bordered table-sm" id="tabla-detalle" data-items="<?= count($items) ?>">
    <thead class="thead-light">
    <tr>
        <th class="text-center">Código</th>
        <th>Producto</th>
        <th>U. medida</th>
        <th class="text-center">IVA</th>
        <th class="text-right">Cantidad</th>
        <th class="text-right">Precio (Gs.)</th>
        <th class="text-right">Subtotal (Gs.)</th>
        <th class="text-center">Quitar</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!$items): ?>
        <tr><td colspan="8" class="text-center text-muted">No hay productos cargados. Use el botón "Agregar productos".</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $i): $rojo = $deposito > 0 && (int) $i['cantidad'] > (int) $i['stock']; ?>
        <tr class="<?= $rojo ? 'text-danger' : '' ?>">
            <td class="text-center"><?= (int) $i['cod_producto'] ?></td>
            <td><?= e($i['p_descrip']) ?><?= $rojo ? ' <small>(stock: ' . gs($i['stock']) . ')</small>' : '' ?></td>
            <td><?= e($i['u_descrip']) ?></td>
            <td class="text-center"><?= e(iva_texto($i['iva'])) ?></td>
            <td class="text-right"><?= gs($i['cantidad']) ?></td>
            <td class="text-right"><?= gs($i['precio']) ?></td>
            <td class="text-right"><?= gs($i['subtotal']) ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs" onclick="eliminar(<?= (int) $i['id_tmp'] ?>)" title="Quitar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="6" class="text-right">TOTAL Gs.</th>
        <th class="text-right"><?= gs($total) ?></th>
        <th></th>
    </tr>
    <tr>
        <td colspan="8" class="text-right text-muted" id="liquidacion-iva">
            Liquidación del IVA: (10 %) <?= gs($iva10) ?> &nbsp;|&nbsp; (5 %) <?= gs($iva5) ?> &nbsp;|&nbsp; Total IVA: <?= gs($iva10 + $iva5) ?>
        </td>
    </tr>
    </tfoot>
</table>

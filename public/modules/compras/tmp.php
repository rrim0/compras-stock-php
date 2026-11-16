<?php
// Tabla temporal de la compra: agrega, quita y calcula subtotal y total
require_once __DIR__ . '/../../../app/core/session.php';
require_login(true);
require_area('compras', true);

$act     = $_GET['act'] ?? 'list';
$usuario = (int) $_SESSION['id_usuario'];
$pdo     = db();
$aviso   = '';

if ($act !== 'list') {
    csrf_check(true);
}

if ($act === 'add') {
    $cod      = int_param($_POST, 'cod');
    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
    $precio   = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999999999]]);

    $st = $pdo->prepare('SELECT 1 FROM producto WHERE cod_producto = ?');
    $st->execute([$cod]);

    if (!$st->fetchColumn()) {
        $aviso = 'El producto seleccionado no existe.';
    } elseif ($cantidad === false || $precio === false) {
        $aviso = 'La cantidad y el precio deben ser números enteros mayores a cero.';
    } else {
        // Si el producto ya está en la lista, se suma la cantidad
        $st = $pdo->prepare('INSERT INTO tmp (cod_producto, cantidad, precio, id_usuario) VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE cantidad = LEAST(cantidad + ?, 1000000), precio = ?');
        $st->execute([$cod, $cantidad, $precio, $usuario, $cantidad, $precio]);
    }
} elseif ($act === 'del') {
    $pdo->prepare('DELETE FROM tmp WHERE id_tmp = ? AND id_usuario = ?')->execute([int_param($_POST, 'id_tmp'), $usuario]);
} elseif ($act === 'vaciar') {
    $pdo->prepare('DELETE FROM tmp WHERE id_usuario = ?')->execute([$usuario]);
}

// Detalle con subtotal y total
$st = $pdo->prepare('SELECT t.id_tmp, t.cod_producto, p.p_descrip, m.u_descrip, t.cantidad, t.precio,
                            t.cantidad * t.precio AS subtotal
                     FROM tmp t
                     JOIN producto p ON p.cod_producto = t.cod_producto
                     JOIN u_medida m ON m.id_u_medida = p.id_u_medida
                     WHERE t.id_usuario = ?
                     ORDER BY t.id_tmp');
$st->execute([$usuario]);
$items = $st->fetchAll();
$total = array_sum(array_column($items, 'subtotal'));

if ($aviso !== '') {
    echo '<div class="alert alert-warning py-2">' . e($aviso) . '</div>';
}
?>
<table class="table table-bordered table-sm" id="tabla-detalle" data-items="<?= count($items) ?>">
    <thead class="thead-light">
    <tr>
        <th class="text-center">Código</th>
        <th>Producto</th>
        <th>U. medida</th>
        <th class="text-right">Cantidad</th>
        <th class="text-right">Precio (Gs.)</th>
        <th class="text-right">Subtotal (Gs.)</th>
        <th class="text-center">Quitar</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!$items): ?>
        <tr><td colspan="7" class="text-center text-muted">No hay productos cargados. Use el botón "Agregar productos".</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $i): ?>
        <tr>
            <td class="text-center"><?= (int) $i['cod_producto'] ?></td>
            <td><?= e($i['p_descrip']) ?></td>
            <td><?= e($i['u_descrip']) ?></td>
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
        <th colspan="5" class="text-right">TOTAL Gs.</th>
        <th class="text-right"><?= gs($total) ?></th>
        <th></th>
    </tr>
    </tfoot>
</table>

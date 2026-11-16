<?php
// Vista detalle de compra
defined('APP') or exit;

$id = int_param($_GET, 'id');
$st = db()->prepare('SELECT c.*, p.razon_social, p.ruc, d.descrip AS deposito, u.nombre AS usuario, a.nombre AS anulado_nombre
                     FROM compra c
                     JOIN proveedor p ON p.id_proveedor = c.id_proveedor
                     JOIN deposito  d ON d.id_deposito  = c.id_deposito
                     JOIN usuarios  u ON u.id_usuario   = c.id_usuario
                     LEFT JOIN usuarios a ON a.id_usuario = c.anulado_por
                     WHERE c.id_compra = ?');
$st->execute([$id]);
$c = $st->fetch();

if (!$c) {
    echo '<section class="content pt-3"><div class="alert alert-warning">La compra no existe. '
       . '<a href="main.php?module=compras">Volver</a></div></section>';
    return;
}

$st = db()->prepare('SELECT dc.cod_producto, pr.p_descrip, t.t_p_descrip, m.u_descrip, dc.cantidad, dc.precio,
                            dc.cantidad * dc.precio AS subtotal
                     FROM detalle_compra dc
                     JOIN producto pr ON pr.cod_producto = dc.cod_producto
                     JOIN tipo_producto t ON t.id_tipo_producto = pr.id_tipo_producto
                     JOIN u_medida m ON m.id_u_medida = pr.id_u_medida
                     WHERE dc.id_compra = ?
                     ORDER BY pr.p_descrip');
$st->execute([$id]);
$detalle = $st->fetchAll();

encabezado('Detalle de compra', 'fas fa-file-invoice', ['Compras' => 'main.php?module=compras', 'Detalle' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="invoice p-3 mb-3">
            <div class="row">
                <div class="col-12">
                    <h4>
                        <i class="fas fa-file-invoice"></i> Compra Nº <?= (int) $c['id_compra'] ?>
                        <?= $c['estado'] === 'activo'
                            ? '<span class="badge badge-success">Activo</span>'
                            : '<span class="badge badge-danger">Anulado</span>' ?>
                        <small class="float-right">Fecha: <?= e(date('d/m/Y', strtotime($c['fecha']))) ?> <?= e(substr($c['hora'], 0, 5)) ?></small>
                    </h4>
                </div>
            </div>
            <div class="row invoice-info mt-3">
                <div class="col-sm-4 invoice-col">
                    <b>Proveedor</b><br>
                    <?= e($c['razon_social']) ?><br>RUC: <?= e($c['ruc']) ?>
                </div>
                <div class="col-sm-4 invoice-col">
                    <b>Nº de factura:</b> <?= e($c['nro_factura']) ?><br>
                    <b>Depósito:</b> <?= e($c['deposito']) ?>
                </div>
                <div class="col-sm-4 invoice-col">
                    <b>Registrado por:</b> <?= e($c['usuario']) ?><br>
                    <?php if ($c['estado'] === 'anulado'): ?>
                        <b>Anulado por:</b> <?= e($c['anulado_nombre']) ?><br>
                        <b>Fecha de anulación:</b> <?= e(date('d/m/Y H:i', strtotime($c['fecha_anulacion']))) ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">Código</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>U. medida</th>
                            <th class="text-right">Cantidad</th>
                            <th class="text-right">Precio (Gs.)</th>
                            <th class="text-right">Subtotal (Gs.)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($detalle as $d): ?>
                            <tr>
                                <td class="text-center"><?= (int) $d['cod_producto'] ?></td>
                                <td><?= e($d['p_descrip']) ?></td>
                                <td><?= e($d['t_p_descrip']) ?></td>
                                <td><?= e($d['u_descrip']) ?></td>
                                <td class="text-right"><?= gs($d['cantidad']) ?></td>
                                <td class="text-right"><?= gs($d['precio']) ?></td>
                                <td class="text-right"><?= gs($d['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">TOTAL Gs.</th>
                            <th class="text-right"><?= gs($c['total']) ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row no-print">
                <div class="col-12">
                    <a href="modules/compras/imprimir.php?id=<?= (int) $c['id_compra'] ?>" target="_blank" class="btn btn-warning">
                        <i class="fas fa-print"></i> Imprimir formulario
                    </a>
                    <a href="main.php?module=compras" class="btn btn-default"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </div>
    </div>
</section>

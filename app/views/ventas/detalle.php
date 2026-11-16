<?php
// Detalle de una venta
defined('APP') or exit;
require_once __DIR__ . '/../../core/ventas.php';

[$v, $detalle] = venta_completa(int_param($_GET, 'id'));

if (!$v) {
    echo '<section class="content pt-3"><div class="alert alert-warning">La venta no existe. '
       . '<a href="main.php?module=ventas">Volver</a></div></section>';
    return;
}

encabezado('Detalle de venta', 'fas fa-file-invoice-dollar', ['Ventas' => 'main.php?module=ventas', 'Detalle' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="invoice p-3 mb-3">
            <div class="row">
                <div class="col-12">
                    <h4>
                        <i class="fas fa-file-invoice-dollar"></i> Factura Nº <?= e($v['factura']) ?>
                        <?= $v['estado'] === 'activo'
                            ? '<span class="badge badge-success">Activo</span>'
                            : '<span class="badge badge-danger">Anulado</span>' ?>
                        <small class="float-right">Fecha: <?= e(date('d/m/Y', strtotime($v['fecha']))) ?> <?= e(substr($v['hora'], 0, 5)) ?></small>
                    </h4>
                </div>
            </div>
            <div class="row invoice-info mt-3">
                <div class="col-sm-4 invoice-col">
                    <b>Cliente</b><br>
                    <?= e($v['cli_apellido'] . ', ' . $v['cli_nombre']) ?><br>C.I./RUC: <?= e($v['ci_ruc']) ?>
                </div>
                <div class="col-sm-4 invoice-col">
                    <b>Timbrado:</b> <?= e($v['nro_timbrado']) ?><br>
                    <b>Condición:</b> <?= e(condicion_texto($v['condicion'])) ?><br>
                    <b>Depósito:</b> <?= e($v['deposito']) ?>
                </div>
                <div class="col-sm-4 invoice-col">
                    <b>Registrado por:</b> <?= e($v['usuario']) ?><br>
                    <?php if ($v['estado'] === 'anulado'): ?>
                        <b>Anulado por:</b> <?= e($v['anulado_nombre']) ?><br>
                        <b>Fecha de anulación:</b> <?= e(date('d/m/Y H:i', strtotime($v['fecha_anulacion']))) ?>
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
                            <th>U. medida</th>
                            <th class="text-center">IVA</th>
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
                                <td><?= e($d['u_descrip']) ?></td>
                                <td class="text-center"><?= e(iva_texto($d['iva'])) ?></td>
                                <td class="text-right"><?= gs($d['cantidad']) ?></td>
                                <td class="text-right"><?= gs($d['precio']) ?></td>
                                <td class="text-right"><?= gs($d['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">TOTAL Gs.</th>
                            <th class="text-right"><?= gs($v['total']) ?></th>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right text-muted">
                                Liquidación del IVA: (10 %) <?= gs($v['iva_10']) ?> &nbsp;|&nbsp; (5 %) <?= gs($v['iva_5']) ?>
                                &nbsp;|&nbsp; Total IVA: <?= gs($v['iva_10'] + $v['iva_5']) ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row no-print">
                <div class="col-12">
                    <a href="modules/ventas/imprimir.php?id=<?= (int) $v['id_venta'] ?>" target="_blank" class="btn btn-warning">
                        <i class="fas fa-print"></i> Imprimir factura
                    </a>
                    <a href="main.php?module=ventas" class="btn btn-default"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </div>
    </div>
</section>

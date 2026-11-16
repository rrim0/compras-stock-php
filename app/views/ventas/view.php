<?php
// Lista de ventas
defined('APP') or exit;

$filas = db()->query("SELECT v.id_venta, v.fecha, v.hora, v.factura, v.condicion, v.total, v.estado,
                             CONCAT(c.cli_apellido, ', ', c.cli_nombre) AS cliente, d.descrip AS deposito, u.nombre AS usuario
                      FROM venta v
                      JOIN clientes c ON c.id_cliente = v.id_cliente
                      JOIN deposito d ON d.id_deposito = v.id_deposito
                      JOIN usuarios u ON u.id_usuario  = v.id_usuario
                      ORDER BY v.id_venta DESC")->fetchAll();

encabezado('Ventas', 'fas fa-cash-register', ['Ventas' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de ventas</h3>
                <div class="card-tools">
                    <a href="main.php?module=form_ventas" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Registrar venta</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable" data-order='[[0,"desc"]]'>
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>Factura Nº</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Hora</th>
                        <th>Cliente</th>
                        <th>Condición</th>
                        <th>Depósito</th>
                        <th class="text-right">Total (Gs.)</th>
                        <th class="text-center">Estado</th>
                        <th>Registrado por</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['id_venta'] ?></td>
                            <td class="text-nowrap"><?= e($f['factura']) ?></td>
                            <td class="text-center" data-order="<?= e($f['fecha']) ?>"><?= e(date('d/m/Y', strtotime($f['fecha']))) ?></td>
                            <td class="text-center"><?= e(substr($f['hora'], 0, 5)) ?></td>
                            <td><?= e($f['cliente']) ?></td>
                            <td><?= e(ucfirst($f['condicion'] === 'credito' ? 'crédito' : 'contado')) ?></td>
                            <td><?= e($f['deposito']) ?></td>
                            <td class="text-right" data-order="<?= (int) $f['total'] ?>"><?= gs($f['total']) ?></td>
                            <td class="text-center">
                                <?= $f['estado'] === 'activo'
                                    ? '<span class="badge badge-success">Activo</span>'
                                    : '<span class="badge badge-danger">Anulado</span>' ?>
                            </td>
                            <td><?= e($f['usuario']) ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=detalle_ventas&amp;id=<?= (int) $f['id_venta'] ?>"
                                   class="btn btn-info btn-sm" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                <a href="modules/ventas/imprimir.php?id=<?= (int) $f['id_venta'] ?>" target="_blank"
                                   class="btn btn-warning btn-sm" title="Imprimir factura"><i class="fas fa-print"></i></a>
                                <?php if ($f['estado'] === 'activo'): ?>
                                    <form action="modules/ventas/procesar.php?act=anular" method="post" class="d-inline"
                                          onsubmit="return confirm('¿Confirma anular la factura Nº <?= e($f['factura']) ?>? Los productos volverán al stock.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $f['id_venta'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Anular"><i class="fas fa-ban"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

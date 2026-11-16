<?php
// Vista de compra y lista de compras
defined('APP') or exit;

$filas = db()->query("SELECT c.id_compra, c.fecha, c.hora, c.nro_factura, c.total, c.estado,
                             p.razon_social, d.descrip AS deposito, u.nombre AS usuario
                      FROM compra c
                      JOIN proveedor p ON p.id_proveedor = c.id_proveedor
                      JOIN deposito  d ON d.id_deposito  = c.id_deposito
                      JOIN usuarios  u ON u.id_usuario   = c.id_usuario
                      ORDER BY c.id_compra DESC")->fetchAll();

encabezado('Compras', 'fas fa-shopping-cart', ['Compras' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de compras</h3>
                <div class="card-tools">
                    <a href="main.php?module=form_compras" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar compra</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable" data-order='[[0,"desc"]]'>
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Hora</th>
                        <th>Proveedor</th>
                        <th>Nº Factura</th>
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
                            <td class="text-center"><?= (int) $f['id_compra'] ?></td>
                            <td class="text-center" data-order="<?= e($f['fecha']) ?>"><?= e(date('d/m/Y', strtotime($f['fecha']))) ?></td>
                            <td class="text-center"><?= e(substr($f['hora'], 0, 5)) ?></td>
                            <td><?= e($f['razon_social']) ?></td>
                            <td><?= e($f['nro_factura']) ?></td>
                            <td><?= e($f['deposito']) ?></td>
                            <td class="text-right" data-order="<?= (float) $f['total'] ?>"><?= gs($f['total']) ?></td>
                            <td class="text-center">
                                <?= $f['estado'] === 'activo'
                                    ? '<span class="badge badge-success">Activo</span>'
                                    : '<span class="badge badge-danger">Anulado</span>' ?>
                            </td>
                            <td><?= e($f['usuario']) ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=detalle_compras&amp;id=<?= (int) $f['id_compra'] ?>"
                                   class="btn btn-info btn-sm" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                <a href="modules/compras/imprimir.php?id=<?= (int) $f['id_compra'] ?>" target="_blank"
                                   class="btn btn-warning btn-sm" title="Imprimir"><i class="fas fa-print"></i></a>
                                <?php if ($f['estado'] === 'activo'): ?>
                                    <form action="modules/compras/procesar.php?act=anular" method="post" class="d-inline"
                                          onsubmit="return confirm('¿Confirma anular la compra Nº <?= (int) $f['id_compra'] ?>? Se descontará el stock ingresado.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $f['id_compra'] ?>">
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

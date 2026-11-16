<?php
// Reporte de compras con filtros y exportación a CSV y PDF
defined('APP') or exit;
require_once __DIR__ . '/../../core/reporte_compras.php';

$f           = reporte_filtros($_GET);
$filas       = reporte_compras($f);
$total       = reporte_total($filas);
$proveedores = db()->query('SELECT id_proveedor, razon_social FROM proveedor ORDER BY razon_social')->fetchAll();
$consultado  = isset($_GET['desde']);

encabezado('Reporte de compras', 'fas fa-chart-bar', ['Referenciales de compras' => null, 'Reporte de compras' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3></div>
            <div class="card-body">
                <form method="get" action="main.php" class="form-row align-items-end">
                    <input type="hidden" name="module" value="reporte_compras">
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label for="desde">Fecha desde</label>
                        <input type="date" class="form-control" id="desde" name="desde" value="<?= e($f['desde']) ?>" required>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label for="hasta">Fecha hasta</label>
                        <input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($f['hasta']) ?>" required>
                    </div>
                    <div class="col-md-4 col-sm-8 mb-2">
                        <label for="proveedor">Proveedor <small class="text-muted">(opcional)</small></label>
                        <select class="form-control select2" id="proveedor" name="proveedor">
                            <option value="0">Todos los proveedores</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= (int) $p['id_proveedor'] ?>" <?= $f['proveedor'] === (int) $p['id_proveedor'] ? 'selected' : '' ?>>
                                    <?= e($p['razon_social']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-2">
                        <label for="estado">Estado</label>
                        <select class="form-control" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="activo" <?= $f['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="anulado" <?= $f['estado'] === 'anulado' ? 'selected' : '' ?>>Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Consultar</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($f['errores']): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <?= e(implode(' ', $f['errores'])) ?></div>
        <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= e(reporte_descripcion($f)) ?></h3>
                <div class="card-tools">
                    <a href="modules/reporte_compras/csv.php?<?= e(reporte_query($f)) ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                    <a href="modules/reporte_compras/pdf.php?<?= e(reporte_query($f)) ?>" target="_blank" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable" data-order='[[1,"asc"]]'>
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th class="text-center">Fecha</th>
                        <th>Proveedor</th>
                        <th>Nº Factura</th>
                        <th>Depósito</th>
                        <th>Registrado por</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Total (Gs.)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $r): ?>
                        <tr class="<?= $r['estado'] === 'anulado' ? 'text-muted' : '' ?>">
                            <td class="text-center"><a href="main.php?module=detalle_compras&amp;id=<?= (int) $r['id_compra'] ?>"><?= (int) $r['id_compra'] ?></a></td>
                            <td class="text-center" data-order="<?= e($r['fecha']) ?>"><?= e(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                            <td><?= e($r['razon_social']) ?></td>
                            <td><?= e($r['nro_factura']) ?></td>
                            <td><?= e($r['deposito']) ?></td>
                            <td><?= e($r['usuario']) ?></td>
                            <td class="text-center">
                                <?= $r['estado'] === 'activo'
                                    ? '<span class="badge badge-success">Activo</span>'
                                    : '<span class="badge badge-danger">Anulado</span>' ?>
                            </td>
                            <td class="text-right" data-order="<?= (int) $r['total'] ?>"><?= gs($r['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="7" class="text-right">Total de compras activas (<?= count(array_filter($filas, static function ($r) { return $r['estado'] === 'activo'; })) ?>) Gs.</th>
                        <th class="text-right"><?= gs($total) ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

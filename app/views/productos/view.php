<?php
// Módulo referencial Productos
defined('APP') or exit;

$filas = db()->query('SELECT p.cod_producto, p.p_descrip, p.precio, p.precio_venta, p.iva, t.t_p_descrip, m.u_descrip,
                             COALESCE(SUM(s.cantidad), 0) AS stock
                      FROM producto p
                      JOIN tipo_producto t ON t.id_tipo_producto = p.id_tipo_producto
                      JOIN u_medida m ON m.id_u_medida = p.id_u_medida
                      LEFT JOIN stock s ON s.cod_producto = p.cod_producto
                      GROUP BY p.cod_producto, p.p_descrip, p.precio, p.precio_venta, p.iva, t.t_p_descrip, m.u_descrip
                      ORDER BY p.p_descrip')->fetchAll();

encabezado('Productos', 'fas fa-box', ['Referenciales de compras' => null, 'Productos' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de productos</h3>
                <div class="card-tools">
                    <a href="main.php?module=form_productos&amp;form=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>U. medida</th>
                        <th class="text-right">Precio de compra (Gs.)</th>
                        <th class="text-right">Precio de venta (Gs.)</th>
                        <th class="text-center">IVA</th>
                        <th class="text-right">Stock</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['cod_producto'] ?></td>
                            <td><?= e($f['p_descrip']) ?></td>
                            <td><?= e($f['t_p_descrip']) ?></td>
                            <td><?= e($f['u_descrip']) ?></td>
                            <td class="text-right" data-order="<?= (int) $f['precio'] ?>"><?= gs($f['precio']) ?></td>
                            <td class="text-right" data-order="<?= (int) $f['precio_venta'] ?>"><?= gs($f['precio_venta']) ?></td>
                            <td class="text-center"><?= (int) $f['iva'] === 0 ? 'Exento' : (int) $f['iva'] . ' %' ?></td>
                            <td class="text-right" data-order="<?= (int) $f['stock'] ?>"><?= gs($f['stock']) ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=form_productos&amp;form=edit&amp;id=<?= (int) $f['cod_producto'] ?>"
                                   class="btn btn-primary btn-sm" title="Modificar"><i class="fas fa-edit"></i></a>
                                <form action="modules/productos/procesar.php?act=delete" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Confirma eliminar el producto seleccionado?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $f['cod_producto'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
// Lista de proveedores
defined('APP') or exit;

$filas = db()->query('SELECT p.id_proveedor, p.ruc, p.razon_social, p.direccion, p.telefono, c.descrip_ciudad,
                             (SELECT COUNT(*) FROM compra co WHERE co.id_proveedor = p.id_proveedor) AS compras
                      FROM proveedor p JOIN ciudad c ON c.id_ciudad = p.id_ciudad
                      ORDER BY p.razon_social')->fetchAll();

encabezado('Proveedores', 'fas fa-truck', ['Referenciales de compras' => null, 'Proveedores' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de proveedores</h3>
                <div class="card-tools">
                    <a href="main.php?module=form_proveedores&amp;form=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>RUC</th>
                        <th>Razón social</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th class="text-center">Compras</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['id_proveedor'] ?></td>
                            <td class="text-nowrap"><?= e($f['ruc']) ?></td>
                            <td><?= e($f['razon_social']) ?></td>
                            <td><?= e($f['direccion']) ?></td>
                            <td><?= e($f['telefono']) ?></td>
                            <td><?= e($f['descrip_ciudad']) ?></td>
                            <td class="text-center"><?= (int) $f['compras'] ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=form_proveedores&amp;form=edit&amp;id=<?= (int) $f['id_proveedor'] ?>"
                                   class="btn btn-primary btn-sm" title="Modificar"><i class="fas fa-edit"></i></a>
                                <form action="modules/proveedores/procesar.php?act=delete" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Confirma eliminar el proveedor seleccionado?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $f['id_proveedor'] ?>">
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

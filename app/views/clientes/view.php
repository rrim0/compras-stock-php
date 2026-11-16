<?php
// Referencial Clientes
defined('APP') or exit;

$filas = db()->query('SELECT cl.id_cliente, cl.ci_ruc, cl.cli_nombre, cl.cli_apellido, cl.cli_direccion,
                             cl.cli_telefono, c.descrip_ciudad
                      FROM clientes cl JOIN ciudad c ON c.id_ciudad = cl.id_ciudad
                      ORDER BY cl.cli_apellido, cl.cli_nombre')->fetchAll();

encabezado('Clientes', 'fas fa-users', ['Referenciales de ventas' => null, 'Clientes' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de clientes</h3>
                <div class="card-tools">
                    <a href="modules/clientes/reporte.php" target="_blank" class="btn btn-warning btn-sm"><i class="fas fa-print"></i> Imprimir reporte</a>
                    <a href="main.php?module=form_clientes&amp;form=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>C.I./RUC</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['id_cliente'] ?></td>
                            <td><?= e($f['ci_ruc']) ?></td>
                            <td><?= e($f['cli_nombre']) ?></td>
                            <td><?= e($f['cli_apellido']) ?></td>
                            <td><?= e($f['cli_direccion']) ?></td>
                            <td><?= e($f['cli_telefono']) ?></td>
                            <td><?= e($f['descrip_ciudad']) ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=form_clientes&amp;form=edit&amp;id=<?= (int) $f['id_cliente'] ?>"
                                   class="btn btn-primary btn-sm" title="Modificar"><i class="fas fa-edit"></i></a>
                                <form action="modules/clientes/procesar.php?act=delete" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Confirma eliminar el cliente seleccionado?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $f['id_cliente'] ?>">
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

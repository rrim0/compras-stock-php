<?php
// Vista del referencial Ciudad
defined('APP') or exit;

$filas = db()->query('SELECT c.id_ciudad, c.descrip_ciudad, d.dep_descripcion
                      FROM ciudad c JOIN departamento d ON d.id_departamento = c.id_departamento
                      ORDER BY d.dep_descripcion, c.descrip_ciudad')->fetchAll();

encabezado('Ciudades', 'fas fa-city', ['Referenciales generales' => null, 'Ciudades' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de ciudades</h3>
                <div class="card-tools">
                    <a href="main.php?module=form_ciudad&amp;form=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>Ciudad</th>
                        <th>Departamento</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['id_ciudad'] ?></td>
                            <td><?= e($f['descrip_ciudad']) ?></td>
                            <td><?= e($f['dep_descripcion']) ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=form_ciudad&amp;form=edit&amp;id=<?= (int) $f['id_ciudad'] ?>"
                                   class="btn btn-primary btn-sm" title="Modificar"><i class="fas fa-edit"></i></a>
                                <form action="modules/ciudad/procesar.php?act=delete" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Confirma eliminar la ciudad seleccionada?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $f['id_ciudad'] ?>">
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

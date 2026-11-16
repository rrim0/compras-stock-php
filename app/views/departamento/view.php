<?php
// Referencial Departamento
defined('APP') or exit;

$filas = db()->query('SELECT d.id_departamento, d.dep_descripcion, COUNT(c.id_ciudad) AS ciudades
                      FROM departamento d LEFT JOIN ciudad c ON c.id_departamento = d.id_departamento
                      GROUP BY d.id_departamento, d.dep_descripcion
                      ORDER BY d.dep_descripcion')->fetchAll();

encabezado('Departamentos', 'fas fa-map', ['Referenciales generales' => null, 'Departamentos' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de departamentos</h3>
                <div class="card-tools">
                    <a href="main.php?module=form_departamento&amp;form=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>Descripción</th>
                        <th class="text-center">Ciudades</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['id_departamento'] ?></td>
                            <td><?= e($f['dep_descripcion']) ?></td>
                            <td class="text-center"><?= (int) $f['ciudades'] ?></td>
                            <td class="text-center">
                                <a href="main.php?module=form_departamento&amp;form=edit&amp;id=<?= (int) $f['id_departamento'] ?>"
                                   class="btn btn-primary btn-sm" title="Modificar"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
// Lista de un referencial de un solo campo
defined('APP') or exit;
require_once __DIR__ . '/../../core/simples.php';

$ref = $modulo_actual;          // módulo de permiso de la página: deposito | tipo_producto | u_medida
$c   = SIMPLES[$ref];
$filas = db()->query("SELECT t.{$c['pk']} AS id, t.{$c['campo']} AS descripcion, {$c['usos']} AS usos
                      FROM {$c['tabla']} t ORDER BY t.{$c['campo']}")->fetchAll();

encabezado($c['titulo'], $c['icono'], ['Referenciales de compras' => null, $c['titulo'] => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de <?= e(mb_strtolower($c['titulo'], 'UTF-8')) ?></h3>
                <div class="card-tools">
                    <a href="main.php?module=form_<?= e($ref) ?>&amp;form=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>Descripción</th>
                        <th class="text-center"><?= e($c['col_usos']) ?></th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center"><?= (int) $f['id'] ?></td>
                            <td><?= e($f['descripcion']) ?></td>
                            <td class="text-center"><?= (int) $f['usos'] ?></td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=form_<?= e($ref) ?>&amp;form=edit&amp;id=<?= (int) $f['id'] ?>"
                                   class="btn btn-primary btn-sm" title="Modificar"><i class="fas fa-edit"></i></a>
                                <form action="modules/simple/procesar.php?ref=<?= e($ref) ?>&amp;act=delete" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Confirma eliminar el registro seleccionado?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
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

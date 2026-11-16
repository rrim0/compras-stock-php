<?php
// Formulario de un referencial de un solo campo
defined('APP') or exit;
require_once __DIR__ . '/../../core/simples.php';

$ref    = $modulo_actual;
$c      = SIMPLES[$ref];
$editar = ($_GET['form'] ?? '') === 'edit';
$r      = ['id' => 0, 'descripcion' => ''];

if ($editar) {
    $st = db()->prepare("SELECT {$c['pk']} AS id, {$c['campo']} AS descripcion FROM {$c['tabla']} WHERE {$c['pk']} = ?");
    $st->execute([int_param($_GET, 'id')]);
    $r = $st->fetch();
    if (!$r) {
        echo '<section class="content pt-3"><div class="alert alert-warning">' . e($c['no_existe'])
           . ' <a href="main.php?module=' . e($ref) . '">Volver</a></div></section>';
        return;
    }
}

encabezado(($editar ? 'Modificar ' : 'Agregar ') . $c['singular'], $c['icono'],
    [$c['titulo'] => 'main.php?module=' . $ref, $editar ? 'Modificar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del registro</h3></div>
            <form action="modules/simple/procesar.php?ref=<?= e($ref) ?>&amp;act=<?= $editar ? 'update' : 'insert' ?>" method="post" class="form-horizontal">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <div class="card-body">
                    <?php if ($editar): ?>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-2"><input type="text" class="form-control" value="<?= (int) $r['id'] ?>" readonly></div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group row">
                        <label for="descripcion" class="col-sm-2 col-form-label">Descripción</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?= e($r['descripcion']) ?>" maxlength="<?= (int) $c['max'] ?>" required autofocus>
                            <small class="form-text text-muted">Máximo <?= (int) $c['max'] ?> caracteres.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=<?= e($ref) ?>" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

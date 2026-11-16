<?php
// Formulario para agregar y modificar departamentos
defined('APP') or exit;

$editar = ($_GET['form'] ?? '') === 'edit';
$d = ['id_departamento' => 0, 'dep_descripcion' => ''];

if ($editar) {
    $st = db()->prepare('SELECT id_departamento, dep_descripcion FROM departamento WHERE id_departamento = ?');
    $st->execute([int_param($_GET, 'id')]);
    $d = $st->fetch();
    if (!$d) {
        echo '<section class="content pt-3"><div class="alert alert-warning">El departamento no existe. '
           . '<a href="main.php?module=departamento">Volver</a></div></section>';
        return;
    }
}

encabezado($editar ? 'Modificar departamento' : 'Agregar departamento', 'fas fa-map',
    ['Departamentos' => 'main.php?module=departamento', $editar ? 'Modificar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del departamento</h3></div>
            <form action="modules/departamento/procesar.php?act=<?= $editar ? 'update' : 'insert' ?>" method="post" class="form-horizontal">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $d['id_departamento'] ?>">
                <div class="card-body">
                    <?php if ($editar): ?>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-2"><input type="text" class="form-control" value="<?= (int) $d['id_departamento'] ?>" readonly></div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group row">
                        <label for="descripcion" class="col-sm-2 col-form-label">Descripción</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?= e($d['dep_descripcion']) ?>" maxlength="60" required autofocus>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=departamento" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

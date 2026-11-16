<?php
// Formulario Ciudad (agregar y editar)
defined('APP') or exit;

$editar = ($_GET['form'] ?? '') === 'edit';
$c = ['id_ciudad' => 0, 'descrip_ciudad' => '', 'id_departamento' => 0];

if ($editar) {
    $st = db()->prepare('SELECT id_ciudad, descrip_ciudad, id_departamento FROM ciudad WHERE id_ciudad = ?');
    $st->execute([int_param($_GET, 'id')]);
    $c = $st->fetch();
    if (!$c) {
        echo '<section class="content pt-3"><div class="alert alert-warning">La ciudad no existe. '
           . '<a href="main.php?module=ciudad">Volver</a></div></section>';
        return;
    }
}
$departamentos = db()->query('SELECT id_departamento, dep_descripcion FROM departamento ORDER BY dep_descripcion')->fetchAll();

encabezado($editar ? 'Modificar ciudad' : 'Agregar ciudad', 'fas fa-city',
    ['Ciudades' => 'main.php?module=ciudad', $editar ? 'Modificar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos de la ciudad</h3></div>
            <form action="modules/ciudad/procesar.php?act=<?= $editar ? 'update' : 'insert' ?>" method="post" class="form-horizontal">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $c['id_ciudad'] ?>">
                <div class="card-body">
                    <?php if ($editar): ?>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-2"><input type="text" class="form-control" value="<?= (int) $c['id_ciudad'] ?>" readonly></div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group row">
                        <label for="descripcion" class="col-sm-2 col-form-label">Ciudad</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?= e($c['descrip_ciudad']) ?>" maxlength="60" required autofocus>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_departamento" class="col-sm-2 col-form-label">Departamento</label>
                        <div class="col-sm-5">
                            <select class="form-control select2" id="id_departamento" name="id_departamento" required>
                                <option value="">-- Seleccionar departamento --</option>
                                <?php foreach ($departamentos as $d): ?>
                                    <option value="<?= (int) $d['id_departamento'] ?>"
                                        <?= (int) $c['id_departamento'] === (int) $d['id_departamento'] ? 'selected' : '' ?>>
                                        <?= e($d['dep_descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=ciudad" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

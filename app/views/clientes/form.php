<?php
// Formulario para agregar y modificar clientes
defined('APP') or exit;

$editar = ($_GET['form'] ?? '') === 'edit';
$c = ['id_cliente' => 0, 'ci_ruc' => '', 'cli_nombre' => '', 'cli_apellido' => '',
      'cli_direccion' => '', 'cli_telefono' => '', 'id_ciudad' => 0];

if ($editar) {
    $st = db()->prepare('SELECT id_cliente, ci_ruc, cli_nombre, cli_apellido, cli_direccion, cli_telefono, id_ciudad
                         FROM clientes WHERE id_cliente = ?');
    $st->execute([int_param($_GET, 'id')]);
    $c = $st->fetch();
    if (!$c) {
        echo '<section class="content pt-3"><div class="alert alert-warning">El cliente no existe. '
           . '<a href="main.php?module=clientes">Volver</a></div></section>';
        return;
    }
}
$ciudades = db()->query('SELECT c.id_ciudad, c.descrip_ciudad, d.dep_descripcion
                         FROM ciudad c JOIN departamento d ON d.id_departamento = c.id_departamento
                         ORDER BY c.descrip_ciudad')->fetchAll();

encabezado($editar ? 'Modificar cliente' : 'Agregar cliente', 'fas fa-user-tie',
    ['Clientes' => 'main.php?module=clientes', $editar ? 'Modificar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del cliente</h3></div>
            <form action="modules/clientes/procesar.php?act=<?= $editar ? 'update' : 'insert' ?>" method="post" class="form-horizontal">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $c['id_cliente'] ?>">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="ci_ruc" class="col-sm-2 col-form-label">C.I./RUC</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="ci_ruc" name="ci_ruc" value="<?= e($c['ci_ruc']) ?>"
                                   pattern="[0-9]{3,15}(-[0-9])?" maxlength="20" required autofocus>
                            <small class="form-text text-muted">Ej.: 4567890 o 80012345-6</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="nombre" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= e($c['cli_nombre']) ?>" maxlength="60" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="apellido" class="col-sm-2 col-form-label">Apellido</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?= e($c['cli_apellido']) ?>" maxlength="60" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="direccion" class="col-sm-2 col-form-label">Dirección</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?= e($c['cli_direccion']) ?>" maxlength="120">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="telefono" class="col-sm-2 col-form-label">Teléfono</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= e($c['cli_telefono']) ?>"
                                   pattern="[0-9+\-() ]{6,20}" maxlength="20">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_ciudad" class="col-sm-2 col-form-label">Ciudad</label>
                        <div class="col-sm-5">
                            <select class="form-control select2" id="id_ciudad" name="id_ciudad" required>
                                <option value="">-- Seleccionar ciudad --</option>
                                <?php foreach ($ciudades as $ci): ?>
                                    <option value="<?= (int) $ci['id_ciudad'] ?>" <?= (int) $c['id_ciudad'] === (int) $ci['id_ciudad'] ? 'selected' : '' ?>>
                                        <?= e($ci['descrip_ciudad'] . ' (' . $ci['dep_descripcion'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=clientes" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

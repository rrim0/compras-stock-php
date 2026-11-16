<?php
// Formulario para agregar y modificar proveedores
defined('APP') or exit;

$editar = ($_GET['form'] ?? '') === 'edit';
$p = ['id_proveedor' => 0, 'ruc' => '', 'razon_social' => '', 'direccion' => '', 'telefono' => '', 'id_ciudad' => 0];

if ($editar) {
    $st = db()->prepare('SELECT id_proveedor, ruc, razon_social, direccion, telefono, id_ciudad FROM proveedor WHERE id_proveedor = ?');
    $st->execute([int_param($_GET, 'id')]);
    $p = $st->fetch();
    if (!$p) {
        echo '<section class="content pt-3"><div class="alert alert-warning">El proveedor no existe. '
           . '<a href="main.php?module=proveedores">Volver</a></div></section>';
        return;
    }
}
$ciudades = db()->query('SELECT c.id_ciudad, c.descrip_ciudad, d.dep_descripcion
                         FROM ciudad c JOIN departamento d ON d.id_departamento = c.id_departamento
                         ORDER BY c.descrip_ciudad')->fetchAll();

encabezado($editar ? 'Modificar proveedor' : 'Agregar proveedor', 'fas fa-truck',
    ['Proveedores' => 'main.php?module=proveedores', $editar ? 'Modificar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del proveedor</h3></div>
            <form action="modules/proveedores/procesar.php?act=<?= $editar ? 'update' : 'insert' ?>" method="post" class="form-horizontal">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $p['id_proveedor'] ?>">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="ruc" class="col-sm-2 col-form-label">RUC</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="ruc" name="ruc" value="<?= e($p['ruc']) ?>"
                                   pattern="[0-9]{5,15}-[0-9]" maxlength="20" required autofocus>
                            <small class="form-text text-muted">Con dígito verificador. Ej.: 80012345-6</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="razon_social" class="col-sm-2 col-form-label">Razón social</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="razon_social" name="razon_social" value="<?= e($p['razon_social']) ?>" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="direccion" class="col-sm-2 col-form-label">Dirección</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?= e($p['direccion']) ?>" maxlength="120">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="telefono" class="col-sm-2 col-form-label">Teléfono</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= e($p['telefono']) ?>"
                                   pattern="[0-9+\-() ]{6,20}" maxlength="20">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_ciudad" class="col-sm-2 col-form-label">Ciudad</label>
                        <div class="col-sm-5">
                            <select class="form-control select2" id="id_ciudad" name="id_ciudad" required>
                                <option value="">-- Seleccionar ciudad --</option>
                                <?php foreach ($ciudades as $ci): ?>
                                    <option value="<?= (int) $ci['id_ciudad'] ?>" <?= (int) $p['id_ciudad'] === (int) $ci['id_ciudad'] ? 'selected' : '' ?>>
                                        <?= e($ci['descrip_ciudad'] . ' (' . $ci['dep_descripcion'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=proveedores" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

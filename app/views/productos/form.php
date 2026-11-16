<?php
// Formulario para agregar y modificar productos
defined('APP') or exit;

$pdo    = db();
$editar = ($_GET['form'] ?? '') === 'edit';
$p = ['cod_producto' => 0, 'p_descrip' => '', 'id_tipo_producto' => 0, 'id_u_medida' => 0, 'precio' => '', 'precio_venta' => '', 'iva' => 10];

if ($editar) {
    $st = $pdo->prepare('SELECT cod_producto, p_descrip, id_tipo_producto, id_u_medida, precio, precio_venta, iva FROM producto WHERE cod_producto = ?');
    $st->execute([int_param($_GET, 'id')]);
    $p = $st->fetch();
    if (!$p) {
        echo '<section class="content pt-3"><div class="alert alert-warning">El producto no existe. '
           . '<a href="main.php?module=productos">Volver</a></div></section>';
        return;
    }
}
$tipos   = $pdo->query('SELECT id_tipo_producto, t_p_descrip FROM tipo_producto ORDER BY t_p_descrip')->fetchAll();
$medidas = $pdo->query('SELECT id_u_medida, u_descrip FROM u_medida ORDER BY u_descrip')->fetchAll();

encabezado($editar ? 'Modificar producto' : 'Agregar producto', 'fas fa-box',
    ['Productos' => 'main.php?module=productos', $editar ? 'Modificar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del producto</h3></div>
            <form action="modules/productos/procesar.php?act=<?= $editar ? 'update' : 'insert' ?>" method="post" class="form-horizontal">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $p['cod_producto'] ?>">
                <div class="card-body">
                    <?php if ($editar): ?>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-2"><input type="text" class="form-control" value="<?= (int) $p['cod_producto'] ?>" readonly></div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group row">
                        <label for="descripcion" class="col-sm-2 col-form-label">Descripción</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?= e($p['p_descrip']) ?>" minlength="3" maxlength="100" required autofocus>
                            <small class="form-text text-muted">De 3 a 100 caracteres.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_tipo_producto" class="col-sm-2 col-form-label">Tipo de producto</label>
                        <div class="col-sm-5">
                            <select class="form-control select2" id="id_tipo_producto" name="id_tipo_producto" required>
                                <option value="">-- Seleccionar tipo --</option>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?= (int) $t['id_tipo_producto'] ?>" <?= (int) $p['id_tipo_producto'] === (int) $t['id_tipo_producto'] ? 'selected' : '' ?>>
                                        <?= e($t['t_p_descrip']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_u_medida" class="col-sm-2 col-form-label">Unidad de medida</label>
                        <div class="col-sm-5">
                            <select class="form-control select2" id="id_u_medida" name="id_u_medida" required>
                                <option value="">-- Seleccionar unidad --</option>
                                <?php foreach ($medidas as $m): ?>
                                    <option value="<?= (int) $m['id_u_medida'] ?>" <?= (int) $p['id_u_medida'] === (int) $m['id_u_medida'] ? 'selected' : '' ?>>
                                        <?= e($m['u_descrip']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="precio" class="col-sm-2 col-form-label">Precio de compra (Gs.)</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control text-right" id="precio" name="precio"
                                   value="<?= $p['precio'] === '' ? '' : (int) $p['precio'] ?>" min="1" max="999999999999" step="1" required>
                            <small class="form-text text-muted">Número entero mayor a cero.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="precio_venta" class="col-sm-2 col-form-label">Precio de venta (Gs.)</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control text-right" id="precio_venta" name="precio_venta"
                                   value="<?= $p['precio_venta'] === '' ? '' : (int) $p['precio_venta'] ?>" min="1" max="999999999999" step="1" required>
                            <small class="form-text text-muted">IVA incluido. Número entero mayor a cero.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="iva" class="col-sm-2 col-form-label">IVA</label>
                        <div class="col-sm-3">
                            <select class="form-control" id="iva" name="iva" required>
                                <?php foreach ([10 => '10 %', 5 => '5 %', 0 => 'Exento'] as $tasa => $texto): ?>
                                    <option value="<?= $tasa ?>" <?= (int) $p['iva'] === $tasa ? 'selected' : '' ?>><?= $texto ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Stock</label>
                        <div class="col-sm-8 col-form-label font-weight-normal text-muted">
                            El stock no se carga a mano: aumenta con las compras y disminuye con las ventas.
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=productos" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

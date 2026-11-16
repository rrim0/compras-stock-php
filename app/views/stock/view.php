<?php
// Stock de productos (consulta de existencias por depósito)
defined('APP') or exit;

$pdo       = db();
$depositos = $pdo->query('SELECT id_deposito, descrip FROM deposito ORDER BY id_deposito')->fetchAll();
$id_dep    = int_param($_GET, 'deposito'); // 0 = todos los depósitos

$nombre_dep = 'Todos los depósitos';
foreach ($depositos as $d) {
    if ((int) $d['id_deposito'] === $id_dep) {
        $nombre_dep = $d['descrip'];
    }
}
if ($nombre_dep === 'Todos los depósitos') {
    $id_dep = 0; // depósito inexistente: se muestran todos
}

$sql = 'SELECT d.descrip AS deposito, t.t_p_descrip, p.cod_producto, p.p_descrip, m.u_descrip, s.cantidad
        FROM stock s
        JOIN deposito d ON d.id_deposito = s.id_deposito
        JOIN producto p ON p.cod_producto = s.cod_producto
        JOIN tipo_producto t ON t.id_tipo_producto = p.id_tipo_producto
        JOIN u_medida m ON m.id_u_medida = p.id_u_medida'
     . ($id_dep ? ' WHERE s.id_deposito = ?' : '')
     . ' ORDER BY d.descrip, p.p_descrip';
$st = $pdo->prepare($sql);
$st->execute($id_dep ? [$id_dep] : []);
$filas = $st->fetchAll();

encabezado('Stock de productos', 'fas fa-boxes', ['Stock' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-body">
                <form method="get" action="main.php" class="form-row align-items-end">
                    <input type="hidden" name="module" value="stock">
                    <div class="col-md-5 col-sm-8 mb-2">
                        <label for="deposito">Depósito</label>
                        <select name="deposito" id="deposito" class="form-control select2">
                            <option value="0">Todos los depósitos</option>
                            <?php foreach ($depositos as $d): ?>
                                <option value="<?= (int) $d['id_deposito'] ?>" <?= $id_dep === (int) $d['id_deposito'] ? 'selected' : '' ?>>
                                    <?= (int) $d['id_deposito'] ?> | <?= e($d['descrip']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar depósito</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock de productos: <b><?= e($nombre_dep) ?></b></h3>
                <div class="card-tools">
                    <a href="modules/stock/reporte.php?deposito=<?= $id_dep ?>" target="_blank" class="btn btn-warning btn-sm">
                        <i class="fas fa-print"></i> Imprimir stock
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th>Depósito</th>
                        <th>Tipo de producto</th>
                        <th class="text-center">Código</th>
                        <th>Producto</th>
                        <th>U. medida</th>
                        <th class="text-right">Cantidad</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr class="<?= (int) $f['cantidad'] === 0 ? 'text-danger' : '' ?>">
                            <td><?= e($f['deposito']) ?></td>
                            <td><?= e($f['t_p_descrip']) ?></td>
                            <td class="text-center"><?= (int) $f['cod_producto'] ?></td>
                            <td><?= e($f['p_descrip']) ?></td>
                            <td><?= e($f['u_descrip']) ?></td>
                            <td class="text-right" data-order="<?= (int) $f['cantidad'] ?>"><?= gs($f['cantidad']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

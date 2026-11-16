<?php
// Tabla de productos del modal (AJAX)
require_once __DIR__ . '/../../../app/core/session.php';
require_login(true);
require_area('compras', true);
require_once __DIR__ . '/paginacion.php';

const POR_PAGINA = 5;

$q    = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 60, 'UTF-8') : '';
$page = max(1, int_param($_GET, 'page'));

$where  = '';
$params = [];
if ($q !== '') {
    $where  = 'WHERE p.p_descrip LIKE :q1 OR t.t_p_descrip LIKE :q2';
    $like   = '%' . addcslashes($q, '%_\\') . '%';
    $params = [':q1' => $like, ':q2' => $like];
}

$pdo = db();
$st  = $pdo->prepare("SELECT COUNT(*) FROM producto p
                      JOIN tipo_producto t ON t.id_tipo_producto = p.id_tipo_producto $where");
$st->execute($params);
$total  = (int) $st->fetchColumn();
$tpages = max(1, (int) ceil($total / POR_PAGINA));
$page   = min($page, $tpages); // la página no puede pasar del total
$offset = ($page - 1) * POR_PAGINA;

$st = $pdo->prepare("SELECT p.cod_producto, p.p_descrip, p.precio, t.t_p_descrip, m.u_descrip
                     FROM producto p
                     JOIN tipo_producto t ON t.id_tipo_producto = p.id_tipo_producto
                     JOIN u_medida m ON m.id_u_medida = p.id_u_medida
                     $where
                     ORDER BY p.p_descrip
                     LIMIT :limite OFFSET :offset");
foreach ($params as $k => $v) {
    $st->bindValue($k, $v);
}
$st->bindValue(':limite', POR_PAGINA, PDO::PARAM_INT);
$st->bindValue(':offset', $offset, PDO::PARAM_INT);
$st->execute();
$productos = $st->fetchAll();

if (!$productos) {
    echo '<div class="alert alert-warning">No se encontraron productos.</div>';
    exit;
}
?>
<table class="table table-sm table-bordered table-hover">
    <thead class="thead-light">
    <tr>
        <th class="text-center">Código</th>
        <th>Producto</th>
        <th>Tipo</th>
        <th>U. medida</th>
        <th style="width:110px;">Cantidad</th>
        <th style="width:150px;">Precio (Gs.)</th>
        <th class="text-center" style="width:70px;">Agregar</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($productos as $p): $cod = (int) $p['cod_producto']; ?>
        <tr>
            <td class="text-center"><?= $cod ?></td>
            <td><?= e($p['p_descrip']) ?></td>
            <td><?= e($p['t_p_descrip']) ?></td>
            <td><?= e($p['u_descrip']) ?></td>
            <td><input type="number" class="form-control form-control-sm text-right" id="cantidad_<?= $cod ?>" value="1" min="1" max="1000000" step="1"></td>
            <td><input type="number" class="form-control form-control-sm text-right" id="precio_<?= $cod ?>" value="<?= (int) $p['precio'] ?>" min="1" max="999999999999" step="1"></td>
            <td class="text-center">
                <button type="button" class="btn btn-success btn-sm" onclick="agregar(<?= $cod ?>)" title="Agregar a la compra">
                    <i class="fas fa-plus"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center">
    <small class="text-muted">Mostrando <?= $offset + 1 ?> a <?= $offset + count($productos) ?> de <?= $total ?> productos</small>
    <?= paginate($page, $tpages) ?>
</div>

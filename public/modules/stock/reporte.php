<?php
// Reporte PDF del stock de productos (por depósito o todos)
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('stock');
require_once __DIR__ . '/../../../app/core/pdf.php';

$pdo    = db();
$id_dep = int_param($_GET, 'deposito');
$nombre = 'Todos los depósitos';

if ($id_dep) {
    $st = $pdo->prepare('SELECT descrip FROM deposito WHERE id_deposito = ?');
    $st->execute([$id_dep]);
    $nombre = $st->fetchColumn();
    if ($nombre === false) {
        $id_dep = 0;
        $nombre = 'Todos los depósitos';
    }
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

$html  = pdf_estilos() . pdf_encabezado('Stock de productos', 'Depósito: ' . $nombre);
$html .= '<table class="rep"><thead><tr>
            <th style="width:18%;">Depósito</th>
            <th style="width:17%;">Tipo de producto</th>
            <th style="width:9%;" class="cen">Código</th>
            <th style="width:30%;">Producto</th>
            <th style="width:13%;">U. medida</th>
            <th style="width:13%;" class="der">Cantidad</th>
          </tr></thead><tbody>';
if (!$filas) {
    $html .= '<tr><td colspan="6" class="cen">Sin existencias registradas.</td></tr>';
}
foreach ($filas as $f) {
    $html .= '<tr>'
           . '<td>' . e($f['deposito']) . '</td>'
           . '<td>' . e($f['t_p_descrip']) . '</td>'
           . '<td class="cen">' . (int) $f['cod_producto'] . '</td>'
           . '<td>' . e($f['p_descrip']) . '</td>'
           . '<td>' . e($f['u_descrip']) . '</td>'
           . '<td class="der">' . gs($f['cantidad']) . '</td>'
           . '</tr>';
}
$html .= '</tbody></table>';

pdf_salida($html, 'stock_productos');

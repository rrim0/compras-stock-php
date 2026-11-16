<?php
// Compra en PDF
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('compras');
require_once __DIR__ . '/../../../app/core/pdf.php';

$id = int_param($_GET, 'id');
$pdo = db();
$st = $pdo->prepare('SELECT c.*, p.razon_social, p.ruc, p.direccion, p.telefono, d.descrip AS deposito, u.nombre AS usuario
                     FROM compra c
                     JOIN proveedor p ON p.id_proveedor = c.id_proveedor
                     JOIN deposito  d ON d.id_deposito  = c.id_deposito
                     JOIN usuarios  u ON u.id_usuario   = c.id_usuario
                     WHERE c.id_compra = ?');
$st->execute([$id]);
$c = $st->fetch();
if (!$c) {
    exit('La compra no existe.');
}

$st = $pdo->prepare('SELECT dc.cod_producto, pr.p_descrip, m.u_descrip, dc.cantidad, dc.precio, dc.cantidad * dc.precio AS subtotal
                     FROM detalle_compra dc
                     JOIN producto pr ON pr.cod_producto = dc.cod_producto
                     JOIN u_medida m ON m.id_u_medida = pr.id_u_medida
                     WHERE dc.id_compra = ? ORDER BY pr.p_descrip');
$st->execute([$id]);
$detalle = $st->fetchAll();

$html  = pdf_estilos() . pdf_encabezado('Formulario de compra Nº ' . $c['id_compra']);
if ($c['estado'] === 'anulado') {
    $html .= '<p style="text-align:center; color:#c00; font-size:14pt; font-weight:bold;">*** COMPRA ANULADA ***</p>';
}
$html .= '<table style="width:100%; margin-bottom:8px;"><tr>
            <td style="width:55%; vertical-align:top;">
                <b>Proveedor:</b> ' . e($c['razon_social']) . '<br>
                <b>RUC:</b> ' . e($c['ruc']) . '<br>
                <b>Dirección:</b> ' . e($c['direccion']) . '<br>
                <b>Teléfono:</b> ' . e($c['telefono']) . '
            </td>
            <td style="width:45%; vertical-align:top;">
                <b>Fecha:</b> ' . e(date('d/m/Y', strtotime($c['fecha']))) . ' &nbsp; <b>Hora:</b> ' . e(substr($c['hora'], 0, 5)) . '<br>
                <b>Nº de factura:</b> ' . e($c['nro_factura']) . '<br>
                <b>Depósito:</b> ' . e($c['deposito']) . '<br>
                <b>Registrado por:</b> ' . e($c['usuario']) . '
            </td>
          </tr></table>';

$html .= '<table class="rep"><thead><tr>
            <th style="width:9%;" class="cen">Código</th>
            <th style="width:37%;">Producto</th>
            <th style="width:14%;">U. medida</th>
            <th style="width:10%;" class="der">Cantidad</th>
            <th style="width:14%;" class="der">Precio (Gs.)</th>
            <th style="width:16%;" class="der">Subtotal (Gs.)</th>
          </tr></thead><tbody>';
foreach ($detalle as $d) {
    $html .= '<tr>'
           . '<td class="cen">' . (int) $d['cod_producto'] . '</td>'
           . '<td>' . e($d['p_descrip']) . '</td>'
           . '<td>' . e($d['u_descrip']) . '</td>'
           . '<td class="der">' . gs($d['cantidad']) . '</td>'
           . '<td class="der">' . gs($d['precio']) . '</td>'
           . '<td class="der">' . gs($d['subtotal']) . '</td>'
           . '</tr>';
}
$html .= '<tr><td colspan="5" class="der"><b>TOTAL Gs.</b></td><td class="der"><b>' . gs($c['total']) . '</b></td></tr>';
$html .= '</tbody></table>';

$html .= '<table style="width:100%; margin-top:40px;"><tr>
            <td style="width:50%; text-align:center;">______________________<br>Firma responsable</td>
            <td style="width:50%; text-align:center;">______________________<br>Recibido conforme</td>
          </tr></table>';

pdf_salida($html, 'compra_' . $c['id_compra']);

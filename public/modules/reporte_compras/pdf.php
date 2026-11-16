<?php
// Exportación del reporte de compras a PDF
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('reporte_compras');
require_once __DIR__ . '/../../../app/core/reporte_compras.php';
require_once __DIR__ . '/../../../app/core/pdf.php';

$f = reporte_filtros($_GET);
if ($f['errores']) {
    flash('warning', implode(' ', $f['errores']));
    redirect('../../main.php?module=reporte_compras');
}
$filas = reporte_compras($f);

$html  = pdf_estilos() . pdf_encabezado('Reporte de compras', reporte_descripcion($f));
$html .= '<table class="rep"><thead><tr>
            <th style="width:7%;" class="cen">Código</th>
            <th style="width:10%;" class="cen">Fecha</th>
            <th style="width:24%;">Proveedor</th>
            <th style="width:15%;">Nº Factura</th>
            <th style="width:13%;">Depósito</th>
            <th style="width:16%;">Registrado por</th>
            <th style="width:8%;" class="cen">Estado</th>
            <th style="width:13%;" class="der">Total (Gs.)</th>
          </tr></thead><tbody>';
if (!$filas) {
    $html .= '<tr><td colspan="8" class="cen">No hay compras con los filtros seleccionados.</td></tr>';
}
foreach ($filas as $r) {
    $color = $r['estado'] === 'anulado' ? ' style="color:#999;"' : '';
    $html .= '<tr' . $color . '>'
           . '<td class="cen">' . (int) $r['id_compra'] . '</td>'
           . '<td class="cen">' . e(date('d/m/Y', strtotime($r['fecha']))) . '</td>'
           . '<td>' . e($r['razon_social']) . '</td>'
           . '<td>' . e($r['nro_factura']) . '</td>'
           . '<td>' . e($r['deposito']) . '</td>'
           . '<td>' . e($r['usuario']) . '</td>'
           . '<td class="cen">' . e(ucfirst($r['estado'])) . '</td>'
           . '<td class="der">' . gs($r['total']) . '</td>'
           . '</tr>';
}
$html .= '<tr><td colspan="7" class="der"><b>Total de compras activas Gs.</b></td><td class="der"><b>' . gs(reporte_total($filas)) . '</b></td></tr>';
$html .= '</tbody></table>';

pdf_salida($html, 'reporte_compras', 'L');

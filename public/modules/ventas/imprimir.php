<?php
// Factura de venta en PDF
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('ventas');
require_once __DIR__ . '/../../../app/core/ventas.php';
require_once __DIR__ . '/../../../app/core/pdf.php';

[$v, $detalle] = venta_completa(int_param($_GET, 'id'));
if (!$v) {
    exit('La venta no existe.');
}

$html  = pdf_estilos();
$html .= '<table style="width:100%; border:1px solid #333;"><tr>
            <td style="width:58%; padding:6px; vertical-align:top;">
                <span style="font-size:15pt; font-weight:bold; color:#3c8dbc;">' . e(EMPRESA_NOMBRE) . '</span><br>
                ' . e(EMPRESA_DIRECCION) . '<br>Tel.: ' . e(EMPRESA_TELEFONO) . '
            </td>
            <td style="width:42%; padding:6px; border-left:1px solid #333; text-align:center; vertical-align:top;">
                <b>TIMBRADO Nº ' . e($v['nro_timbrado']) . '</b><br>
                <span style="font-size:8pt;">Vigencia: ' . e(date('d/m/Y', strtotime($v['vigencia_desde']))) . ' al ' . e(date('d/m/Y', strtotime($v['vigencia_hasta']))) . '</span><br>
                RUC: ' . e(EMPRESA_RUC) . '<br>
                <span style="font-size:13pt; font-weight:bold;">FACTURA</span><br>
                <span style="font-size:13pt; font-weight:bold;">Nº ' . e($v['factura']) . '</span>
            </td>
          </tr></table>';

if ($v['estado'] === 'anulado') {
    $html .= '<p style="text-align:center; color:#c00; font-size:14pt; font-weight:bold; margin:6px 0;">*** FACTURA ANULADA ***</p>';
}

$html .= '<table style="width:100%; border:1px solid #333; margin-top:4px;"><tr>
            <td style="width:58%; padding:5px; vertical-align:top;">
                <b>Fecha de emisión:</b> ' . e(date('d/m/Y', strtotime($v['fecha']))) . ' ' . e(substr($v['hora'], 0, 5)) . '<br>
                <b>Nombre o razón social:</b> ' . e($v['cli_apellido'] . ', ' . $v['cli_nombre']) . '<br>
                <b>Dirección:</b> ' . e($v['cli_direccion']) . '
            </td>
            <td style="width:42%; padding:5px; vertical-align:top;">
                <b>Condición de venta:</b> ' . e(condicion_texto($v['condicion'])) . '<br>
                <b>RUC / C.I.:</b> ' . e($v['ci_ruc']) . '<br>
                <b>Teléfono:</b> ' . e($v['cli_telefono']) . '
            </td>
          </tr></table>';

$html .= '<table class="rep" style="margin-top:4px;"><thead><tr>
            <th style="width:8%;" class="cen">Cant.</th>
            <th style="width:38%;">Descripción</th>
            <th style="width:14%;" class="der">Precio unitario</th>
            <th style="width:12%;" class="der">Exentas</th>
            <th style="width:14%;" class="der">5 %</th>
            <th style="width:14%;" class="der">10 %</th>
          </tr></thead><tbody>';
foreach ($detalle as $d) {
    $tasa = (int) $d['iva'];
    $html .= '<tr>'
           . '<td class="cen">' . gs($d['cantidad']) . '</td>'
           . '<td>' . e($d['p_descrip'] . ' (' . $d['u_descrip'] . ')') . '</td>'
           . '<td class="der">' . gs($d['precio']) . '</td>'
           . '<td class="der">' . ($tasa === 0 ? gs($d['subtotal']) : '') . '</td>'
           . '<td class="der">' . ($tasa === 5 ? gs($d['subtotal']) : '') . '</td>'
           . '<td class="der">' . ($tasa === 10 ? gs($d['subtotal']) : '') . '</td>'
           . '</tr>';
}
$html .= '<tr><td colspan="3" class="der"><b>Subtotales</b></td>'
       . '<td class="der">' . gs($v['exenta']) . '</td><td class="der">' . gs($v['gravada_5']) . '</td><td class="der">' . gs($v['gravada_10']) . '</td></tr>';
$html .= '<tr><td colspan="5" class="der"><b>TOTAL A PAGAR Gs.</b></td><td class="der"><b>' . gs($v['total']) . '</b></td></tr>';
$html .= '<tr><td colspan="6"><b>Liquidación del IVA:</b> (5 %) ' . gs($v['iva_5']) . ' &nbsp;&nbsp; (10 %) ' . gs($v['iva_10'])
       . ' &nbsp;&nbsp; <b>Total IVA:</b> ' . gs($v['iva_5'] + $v['iva_10']) . '</td></tr>';
$html .= '</tbody></table>';

$html .= '<p style="font-size:8pt; margin-top:6px;">Depósito: ' . e($v['deposito']) . ' &nbsp;|&nbsp; Vendedor: ' . e($v['usuario']) . '</p>';
$html .= '<p style="font-size:7.5pt; color:#777; text-align:center; margin-top:14px;">Documento generado con fines académicos. '
       . 'Timbrado y datos del emisor ficticios: sin validez fiscal.</p>';

pdf_salida($html, 'factura_' . $v['factura']);

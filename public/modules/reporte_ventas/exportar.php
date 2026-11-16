<?php
// Exportación del reporte de ventas
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('reporte_ventas');
require_once __DIR__ . '/../../../app/core/ventas.php';

$f = rv_filtros($_GET);
if ($f['errores']) {
    flash('warning', implode(' ', $f['errores']));
    redirect('../../main.php?module=reporte_ventas');
}
$filas = rv_ventas($f);
[$n_act, $total, $iva] = rv_totales($filas);

// PDF
if (($_GET['formato'] ?? '') === 'pdf') {
    require_once __DIR__ . '/../../../app/core/pdf.php';
    $html  = pdf_estilos() . pdf_encabezado('Reporte de ventas', rv_descripcion($f));
    $html .= '<table class="rep"><thead><tr>
                <th style="width:14%;">Factura Nº</th>
                <th style="width:9%;" class="cen">Fecha</th>
                <th style="width:22%;">Cliente</th>
                <th style="width:9%;">Condición</th>
                <th style="width:12%;">Depósito</th>
                <th style="width:8%;" class="cen">Estado</th>
                <th style="width:12%;" class="der">IVA (Gs.)</th>
                <th style="width:14%;" class="der">Total (Gs.)</th>
              </tr></thead><tbody>';
    if (!$filas) {
        $html .= '<tr><td colspan="8" class="cen">No hay ventas con los filtros seleccionados.</td></tr>';
    }
    foreach ($filas as $r) {
        $html .= '<tr' . ($r['estado'] === 'anulado' ? ' style="color:#999;"' : '') . '>'
               . '<td>' . e($r['factura']) . '</td>'
               . '<td class="cen">' . e(date('d/m/Y', strtotime($r['fecha']))) . '</td>'
               . '<td>' . e($r['cliente']) . '</td>'
               . '<td>' . e(condicion_texto($r['condicion'])) . '</td>'
               . '<td>' . e($r['deposito']) . '</td>'
               . '<td class="cen">' . e(ucfirst($r['estado'])) . '</td>'
               . '<td class="der">' . gs($r['iva_10'] + $r['iva_5']) . '</td>'
               . '<td class="der">' . gs($r['total']) . '</td>'
               . '</tr>';
    }
    $html .= '<tr><td colspan="6" class="der"><b>Total de ventas activas (' . $n_act . ') Gs.</b></td>'
           . '<td class="der"><b>' . gs($iva) . '</b></td><td class="der"><b>' . gs($total) . '</b></td></tr></tbody></table>';
    pdf_salida($html, 'reporte_ventas', 'L');
}

// CSV
// Evita que Excel tome un texto como fórmula
function celda($valor): string
{
    $v = (string) $valor;
    return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;
}

// Escribe una fila CSV
function fila($out, array $datos): void
{
    fputcsv($out, $datos, ';', '"', '');
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Ymd_His') . '.csv"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para los acentos en Excel
fila($out, ['Reporte de ventas', celda(rv_descripcion($f))]);
fila($out, ['Factura Nº', 'Fecha', 'Hora', 'Cliente', 'C.I./RUC', 'Condición', 'Depósito', 'Registrado por', 'Estado', 'IVA (Gs.)', 'Total (Gs.)']);
foreach ($filas as $r) {
    fila($out, [
        $r['factura'],
        date('d/m/Y', strtotime($r['fecha'])),
        substr($r['hora'], 0, 5),
        celda($r['cliente']),
        celda($r['ci_ruc']),
        condicion_texto($r['condicion']),
        celda($r['deposito']),
        celda($r['usuario']),
        $r['estado'],
        (int) $r['iva_10'] + (int) $r['iva_5'],
        (int) $r['total'],
    ]);
}
fila($out, ['', '', '', '', '', '', '', '', 'Total activas', $iva, $total]);
fclose($out);
exit;

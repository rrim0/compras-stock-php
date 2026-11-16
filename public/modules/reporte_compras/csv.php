<?php
// Exportación del reporte de compras a CSV
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('reporte_compras');
require_once __DIR__ . '/../../../app/core/reporte_compras.php';

$f = reporte_filtros($_GET);
if ($f['errores']) {
    flash('warning', implode(' ', $f['errores']));
    redirect('../../main.php?module=reporte_compras');
}
$filas = reporte_compras($f);

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
header('Content-Disposition: attachment; filename="reporte_compras_' . date('Ymd_His') . '.csv"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para los acentos en Excel
fila($out, ['Reporte de compras', celda(reporte_descripcion($f))]);
fila($out, ['Código', 'Fecha', 'Hora', 'Proveedor', 'RUC', 'Nº Factura', 'Depósito', 'Registrado por', 'Estado', 'Total (Gs.)']);
foreach ($filas as $r) {
    fila($out, [
        (int) $r['id_compra'],
        date('d/m/Y', strtotime($r['fecha'])),
        substr($r['hora'], 0, 5),
        celda($r['razon_social']),
        celda($r['ruc']),
        celda($r['nro_factura']),
        celda($r['deposito']),
        celda($r['usuario']),
        $r['estado'],
        (int) $r['total'],
    ]);
}
fila($out, ['', '', '', '', '', '', '', '', 'Total activas', reporte_total($filas)]);
fclose($out);
exit;

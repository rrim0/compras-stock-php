<?php
// Reporte de compras: filtros y consulta
defined('APP') or exit;

// ¿Es una fecha válida con formato AAAA-MM-DD?
function fecha_valida(string $f): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $f);
    return $d !== false && $d->format('Y-m-d') === $f;
}

// Lee y valida los filtros del reporte desde GET
function reporte_filtros(array $get): array
{
    $f = [
        'desde'     => is_string($get['desde'] ?? null) ? $get['desde'] : date('Y-m-01'),
        'hasta'     => is_string($get['hasta'] ?? null) ? $get['hasta'] : date('Y-m-d'),
        'proveedor' => int_param($get, 'proveedor'),
        'estado'    => in_array($get['estado'] ?? '', ['activo', 'anulado'], true) ? $get['estado'] : '',
        'errores'   => [],
    ];
    if (!fecha_valida($f['desde']) || !fecha_valida($f['hasta'])) {
        $f['errores'][] = 'Ingrese fechas válidas.';
    } elseif ($f['desde'] > $f['hasta']) {
        $f['errores'][] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
    }
    return $f;
}

// Compras que cumplen los filtros
function reporte_compras(array $f): array
{
    if ($f['errores']) {
        return [];
    }
    $sql = 'SELECT c.id_compra, c.fecha, c.hora, c.nro_factura, c.total, c.estado,
                   p.razon_social, p.ruc, d.descrip AS deposito, u.nombre AS usuario
            FROM compra c
            JOIN proveedor p ON p.id_proveedor = c.id_proveedor
            JOIN deposito  d ON d.id_deposito  = c.id_deposito
            JOIN usuarios  u ON u.id_usuario   = c.id_usuario
            WHERE c.fecha BETWEEN ? AND ?';
    $params = [$f['desde'], $f['hasta']];
    if ($f['proveedor'] > 0) {
        $sql .= ' AND c.id_proveedor = ?';
        $params[] = $f['proveedor'];
    }
    if ($f['estado'] !== '') {
        $sql .= ' AND c.estado = ?';
        $params[] = $f['estado'];
    }
    $sql .= ' ORDER BY c.fecha, c.id_compra';

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

// Texto con los filtros aplicados
function reporte_descripcion(array $f): string
{
    $txt = 'Del ' . date('d/m/Y', strtotime($f['desde'])) . ' al ' . date('d/m/Y', strtotime($f['hasta']));
    if ($f['proveedor'] > 0) {
        $st = db()->prepare('SELECT razon_social FROM proveedor WHERE id_proveedor = ?');
        $st->execute([$f['proveedor']]);
        $txt .= ' - Proveedor: ' . ($st->fetchColumn() ?: 'inexistente');
    } else {
        $txt .= ' - Todos los proveedores';
    }
    $txt .= $f['estado'] !== '' ? ' - Estado: ' . $f['estado'] : ' - Todos los estados';
    return $txt;
}

// Total de las compras activas
function reporte_total(array $filas): int
{
    $total = 0;
    foreach ($filas as $r) {
        if ($r['estado'] === 'activo') {
            $total += (int) $r['total'];
        }
    }
    return $total;
}

// Filtros para los enlaces de exportación
function reporte_query(array $f): string
{
    return http_build_query(['desde' => $f['desde'], 'hasta' => $f['hasta'],
                             'proveedor' => $f['proveedor'], 'estado' => $f['estado']]);
}

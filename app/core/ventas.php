<?php
// Consultas de ventas (detalle, factura y reporte)
defined('APP') or exit;
require_once __DIR__ . '/reporte_compras.php'; // fecha_valida()

// Cabecera y detalle de una venta
function venta_completa(int $id): array
{
    $st = db()->prepare("SELECT v.*, t.nro_timbrado, t.vigencia_desde, t.vigencia_hasta,
                                c.ci_ruc, c.cli_nombre, c.cli_apellido, c.cli_direccion, c.cli_telefono,
                                d.descrip AS deposito, u.nombre AS usuario, a.nombre AS anulado_nombre
                         FROM venta v
                         JOIN timbrado t ON t.id_timbrado = v.id_timbrado
                         JOIN clientes c ON c.id_cliente  = v.id_cliente
                         JOIN deposito d ON d.id_deposito = v.id_deposito
                         JOIN usuarios u ON u.id_usuario  = v.id_usuario
                         LEFT JOIN usuarios a ON a.id_usuario = v.anulado_por
                         WHERE v.id_venta = ?");
    $st->execute([$id]);
    $v = $st->fetch();
    if (!$v) {
        return [null, []];
    }
    $st = db()->prepare('SELECT dv.cod_producto, p.p_descrip, m.u_descrip, dv.cantidad, dv.precio, dv.iva,
                                dv.cantidad * dv.precio AS subtotal
                         FROM detalle_venta dv
                         JOIN producto p ON p.cod_producto = dv.cod_producto
                         JOIN u_medida m ON m.id_u_medida = p.id_u_medida
                         WHERE dv.id_venta = ? ORDER BY p.p_descrip');
    $st->execute([$id]);
    return [$v, $st->fetchAll()];
}

function condicion_texto(string $c): string
{
    return $c === 'credito' ? 'Crédito' : 'Contado';
}

// Reporte de ventas: filtros
function rv_filtros(array $get): array
{
    $f = [
        'desde'   => is_string($get['desde'] ?? null) ? $get['desde'] : date('Y-m-01'),
        'hasta'   => is_string($get['hasta'] ?? null) ? $get['hasta'] : date('Y-m-d'),
        'cliente' => int_param($get, 'cliente'),
        'estado'  => in_array($get['estado'] ?? '', ['activo', 'anulado'], true) ? $get['estado'] : '',
        'errores' => [],
    ];
    if (!fecha_valida($f['desde']) || !fecha_valida($f['hasta'])) {
        $f['errores'][] = 'Ingrese fechas válidas.';
    } elseif ($f['desde'] > $f['hasta']) {
        $f['errores'][] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
    }
    return $f;
}

function rv_ventas(array $f): array
{
    if ($f['errores']) {
        return [];
    }
    $sql = "SELECT v.id_venta, v.fecha, v.hora, v.factura, v.condicion, v.total, v.iva_10, v.iva_5, v.estado,
                   CONCAT(c.cli_apellido, ', ', c.cli_nombre) AS cliente, c.ci_ruc, d.descrip AS deposito, u.nombre AS usuario
            FROM venta v
            JOIN clientes c ON c.id_cliente  = v.id_cliente
            JOIN deposito d ON d.id_deposito = v.id_deposito
            JOIN usuarios u ON u.id_usuario  = v.id_usuario
            WHERE v.fecha BETWEEN ? AND ?";
    $params = [$f['desde'], $f['hasta']];
    if ($f['cliente'] > 0) {
        $sql .= ' AND v.id_cliente = ?';
        $params[] = $f['cliente'];
    }
    if ($f['estado'] !== '') {
        $sql .= ' AND v.estado = ?';
        $params[] = $f['estado'];
    }
    $st = db()->prepare($sql . ' ORDER BY v.fecha, v.id_venta');
    $st->execute($params);
    return $st->fetchAll();
}

function rv_descripcion(array $f): string
{
    $txt = 'Del ' . date('d/m/Y', strtotime($f['desde'])) . ' al ' . date('d/m/Y', strtotime($f['hasta']));
    if ($f['cliente'] > 0) {
        $st = db()->prepare("SELECT CONCAT(cli_apellido, ', ', cli_nombre) FROM clientes WHERE id_cliente = ?");
        $st->execute([$f['cliente']]);
        $txt .= ' - Cliente: ' . ($st->fetchColumn() ?: 'inexistente');
    } else {
        $txt .= ' - Todos los clientes';
    }
    return $txt . ($f['estado'] !== '' ? ' - Estado: ' . $f['estado'] : ' - Todos los estados');
}

// Totales de las ventas activas
function rv_totales(array $filas): array
{
    $n = $total = $iva = 0;
    foreach ($filas as $r) {
        if ($r['estado'] === 'activo') {
            $n++;
            $total += (int) $r['total'];
            $iva   += (int) $r['iva_10'] + (int) $r['iva_5'];
        }
    }
    return [$n, $total, $iva];
}

function rv_query(array $f): string
{
    return http_build_query(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'cliente' => $f['cliente'], 'estado' => $f['estado']]);
}

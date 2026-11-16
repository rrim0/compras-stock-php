<?php
// Paginación de la tabla de productos
defined('APP') or exit;

function paginate(int $page, int $tpages, int $adjacents = 2): string
{
    if ($tpages <= 1) {
        return '';
    }

    $item = static function (int $n, string $texto, bool $activo = false, bool $deshabilitado = false): string {
        $clase = 'page-item' . ($activo ? ' active' : '') . ($deshabilitado ? ' disabled' : '');
        if ($activo || $deshabilitado) {
            return '<li class="' . $clase . '"><span class="page-link">' . $texto . '</span></li>';
        }
        return '<li class="' . $clase . '"><a class="page-link" href="javascript:void(0)" onclick="load(' . $n . ')">' . $texto . '</a></li>';
    };

    $out = '<ul class="pagination pagination-sm justify-content-center m-0">';
    $out .= $item($page - 1, '&lsaquo; Anterior', false, $page <= 1);

    if ($page > $adjacents + 1) {
        $out .= $item(1, '1');
    }
    if ($page > $adjacents + 2) {
        $out .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    $desde = max(1, $page - $adjacents);
    $hasta = min($tpages, $page + $adjacents);
    for ($i = $desde; $i <= $hasta; $i++) {
        $out .= $item($i, (string) $i, $i === $page);
    }

    if ($page < $tpages - $adjacents - 1) {
        $out .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    if ($page < $tpages - $adjacents) {
        $out .= $item($tpages, (string) $tpages);
    }

    $out .= $item($page + 1, 'Siguiente &rsaquo;', false, $page >= $tpages);
    return $out . '</ul>';
}

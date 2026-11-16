<?php
// Reporte PDF de clientes
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('clientes');
require_once __DIR__ . '/../../../app/core/pdf.php';

$filas = db()->query('SELECT cl.ci_ruc, cl.cli_nombre, cl.cli_apellido, cl.cli_direccion, cl.cli_telefono,
                             c.descrip_ciudad, d.dep_descripcion
                      FROM clientes cl
                      JOIN ciudad c ON c.id_ciudad = cl.id_ciudad
                      JOIN departamento d ON d.id_departamento = c.id_departamento
                      ORDER BY cl.cli_apellido, cl.cli_nombre')->fetchAll();

$html  = pdf_estilos() . pdf_encabezado('Reporte de clientes', 'Total: ' . count($filas) . ' cliente(s)');
$html .= '<table class="rep"><thead><tr>
            <th style="width:5%;" class="cen">Nro.</th>
            <th style="width:12%;">C.I./RUC</th>
            <th style="width:22%;">Apellido y nombre</th>
            <th style="width:23%;">Dirección</th>
            <th style="width:12%;">Teléfono</th>
            <th style="width:13%;">Ciudad</th>
            <th style="width:13%;">Departamento</th>
          </tr></thead><tbody>';
foreach ($filas as $i => $f) {
    $html .= '<tr>'
           . '<td class="cen">' . ($i + 1) . '</td>'
           . '<td>' . e($f['ci_ruc']) . '</td>'
           . '<td>' . e($f['cli_apellido'] . ', ' . $f['cli_nombre']) . '</td>'
           . '<td>' . e($f['cli_direccion']) . '</td>'
           . '<td>' . e($f['cli_telefono']) . '</td>'
           . '<td>' . e($f['descrip_ciudad']) . '</td>'
           . '<td>' . e($f['dep_descripcion']) . '</td>'
           . '</tr>';
}
$html .= '</tbody></table>';

pdf_salida($html, 'reporte_clientes');

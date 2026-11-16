<?php
// Reporte PDF de usuarios
require_once __DIR__ . '/../../../app/core/session.php';
require_login();
require_area('user');
require_once __DIR__ . '/../../../app/core/pdf.php';

$usuarios = db()->query('SELECT u.usuario, u.nombre, u.email, u.telefono, r.rol_nombre AS rol, u.estado
                         FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol ORDER BY u.nombre')->fetchAll();

$html  = pdf_estilos() . pdf_encabezado('Reporte de usuarios', 'Total: ' . count($usuarios) . ' usuario(s)');
$html .= '<table class="rep"><thead><tr>
            <th style="width:5%;" class="cen">Nro.</th>
            <th style="width:14%;">Usuario</th>
            <th style="width:25%;">Nombre</th>
            <th style="width:24%;">Email</th>
            <th style="width:12%;">Teléfono</th>
            <th style="width:11%;">Rol</th>
            <th style="width:9%;" class="cen">Estado</th>
          </tr></thead><tbody>';
foreach ($usuarios as $i => $u) {
    $html .= '<tr>'
           . '<td class="cen">' . ($i + 1) . '</td>'
           . '<td>' . e($u['usuario']) . '</td>'
           . '<td>' . e($u['nombre']) . '</td>'
           . '<td>' . e($u['email']) . '</td>'
           . '<td>' . e($u['telefono']) . '</td>'
           . '<td>' . e($u['rol']) . '</td>'
           . '<td class="cen">' . e(ucfirst($u['estado'])) . '</td>'
           . '</tr>';
}
$html .= '</tbody></table>';

pdf_salida($html, 'reporte_usuarios');

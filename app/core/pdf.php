<?php
// Reportes PDF con la librería HTML2PDF
defined('APP') or exit;

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;

// Encabezado común de los reportes
function pdf_encabezado(string $titulo, string $subtitulo = ''): string
{
    $html  = '<table style="width:100%; border-bottom:1px solid #3c8dbc; padding-bottom:4px;"><tr>';
    $html .= '<td style="width:60%;"><span style="font-size:15pt; font-weight:bold; color:#3c8dbc;">' . e(APP_NAME) . '</span><br>';
    $html .= '<span style="font-size:9pt;">Universidad Tecnológica Intercontinental - UTIC</span></td>';
    $html .= '<td style="width:40%; text-align:right; font-size:8pt;">Fecha: ' . date('d/m/Y H:i') . '<br>Usuario: ' . e($_SESSION['nombre'] ?? '') . '</td>';
    $html .= '</tr></table>';
    $html .= '<h3 style="text-align:center; margin:10px 0 2px 0;">' . e($titulo) . '</h3>';
    if ($subtitulo !== '') {
        $html .= '<p style="text-align:center; margin:0 0 8px 0; font-size:9pt;">' . e($subtitulo) . '</p>';
    }
    return $html;
}

// Envía el PDF al navegador
function pdf_salida(string $contenido, string $archivo, string $orientacion = 'P'): void
{
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (!is_file($autoload)) {
        exit('Falta la librería HTML2PDF: no se encontró la carpeta vendor en ' . realpath(__DIR__ . '/../..')
           . '. Copie la carpeta vendor del proyecto o ejecute "composer install" en esa carpeta.');
    }
    require_once $autoload;

    $html = '<page backtop="8mm" backbottom="10mm" backleft="5mm" backright="5mm" style="font-size:9pt; font-family:helvetica;">'
          . '<page_footer><table style="width:100%; font-size:7pt; color:#777;"><tr>'
          . '<td style="width:50%;">' . e(APP_NAME) . '</td>'
          . '<td style="width:50%; text-align:right;">Página [[page_cu]] de [[page_nb]]</td>'
          . '</tr></table></page_footer>'
          . $contenido . '</page>';

    try {
        $pdf = new Html2Pdf($orientacion, 'A4', 'es', true, 'UTF-8', [10, 10, 10, 10]);
        $pdf->pdf->SetTitle($archivo);
        $pdf->writeHTML($html);
        $pdf->output($archivo . '.pdf', 'I'); // 'I' = mostrar en el navegador
    } catch (Html2PdfException $ex) {
        error_log('HTML2PDF: ' . $ex->getMessage());
        exit('No se pudo generar el reporte PDF.');
    }
    exit;
}

// Estilos de las tablas de los reportes
function pdf_estilos(): string
{
    return '<style>
        table.rep { width:100%; border-collapse:collapse; }
        table.rep th { background-color:#3c8dbc; color:#fff; padding:4px; border:0.5px solid #2e6da4; font-size:8.5pt; }
        table.rep td { padding:3px 4px; border:0.5px solid #bbb; font-size:8.5pt; }
        .der { text-align:right; }
        .cen { text-align:center; }
    </style>';
}

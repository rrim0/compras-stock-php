<?php
// Sesión, login, permisos y funciones comunes
require_once __DIR__ . '/../config/database.php';

if (!defined('APP')) {
    define('APP', true); // permite incluir vistas solo desde el sistema
}

// Sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_name('SYSWEB');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // la cookie no es accesible desde JavaScript
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// Permisos por rol (tablas roles, menu y permisos)

// id del rol Super Admin
const ROL_SUPER_ADMIN = 1;

// Módulos permitidos para el rol del usuario
function modulos_permitidos(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        if (!empty($_SESSION['id_rol'])) {
            $st = db()->prepare('SELECT m.modulo FROM permisos p
                                 JOIN menu m ON m.id_menu = p.id_menu
                                 WHERE p.id_rol = ? AND m.modulo IS NOT NULL');
            $st->execute([$_SESSION['id_rol']]);
            $cache = $st->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    return $cache;
}

// ¿El rol tiene permiso sobre el módulo?
function can(string $modulo): bool
{
    return in_array($modulo, modulos_permitidos(), true);
}

// Autenticación
function is_logged(): bool
{
    return !empty($_SESSION['id_usuario']);
}

// Exige sesión iniciada y usuario activo
function require_login(bool $ajax = false): void
{
    $motivo = null;

    if (!is_logged()) {
        $motivo = 'sesion';
    } elseif (time() - ($_SESSION['ultimo_acceso'] ?? 0) > SESSION_TIMEOUT * 60) {
        $motivo = 'expirada';
    } else {
        $st = db()->prepare('SELECT u.nombre, u.id_rol, r.rol_nombre, u.estado, u.foto
                             FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
                             WHERE u.id_usuario = ?');
        $st->execute([$_SESSION['id_usuario']]);
        $u = $st->fetch();
        if (!$u || $u['estado'] !== 'activo') {
            $motivo = 'bloqueado';
        } else {
            $_SESSION['nombre']        = $u['nombre'];
            $_SESSION['id_rol']        = (int) $u['id_rol'];
            $_SESSION['rol']           = $u['rol_nombre'];
            $_SESSION['foto']          = $u['foto'];
            $_SESSION['ultimo_acceso'] = time();
        }
    }

    if ($motivo !== null) {
        cerrar_sesion();
        if ($ajax) {
            http_response_code(401);
            exit('Sesión finalizada. Vuelva a ingresar al sistema.');
        }
        redirect(base_url() . 'index.php?alert=' . $motivo);
    }
}

// Exige permiso sobre el módulo
function require_area(string $modulo, bool $ajax = false): void
{
    if (!can($modulo)) {
        if ($ajax) {
            http_response_code(403);
            exit('No tiene permiso para realizar esta operación.');
        }
        flash('danger', 'No tiene permiso para acceder a esa sección.');
        redirect(base_url() . 'main.php?module=start');
    }
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start(); // sesión nueva y vacía
}

// Token CSRF de los formularios
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(bool $ajax = false): void
{
    $token = $_POST['csrf'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        if ($ajax) {
            exit('Solicitud no válida. Recargue la página e intente de nuevo.');
        }
        flash('danger', 'Solicitud no válida. Recargue la página e intente de nuevo.');
        redirect(base_url() . 'main.php?module=start');
    }
}

// Mensajes que se muestran una sola vez
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function flash_show(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $iconos = ['success' => 'check', 'danger' => 'ban', 'warning' => 'exclamation-triangle', 'info' => 'info'];
    $icono  = $iconos[$f['tipo']] ?? 'info';
    return '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible fade show">'
         . '<button type="button" class="close" data-dismiss="alert">&times;</button>'
         . '<i class="icon fas fa-' . $icono . '"></i> ' . e($f['mensaje']) . '</div>';
}

// Utilidades

// Escapa texto para mostrarlo en HTML
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Formato de guaraníes
function gs($numero): string
{
    return number_format((float) $numero, 0, ',', '.');
}

// Ruta relativa hacia la raíz del sistema
function base_url(): string
{
    $raiz   = str_replace('\\', '/', (string) realpath(__DIR__ . '/../../public'));
    $script = str_replace('\\', '/', dirname((string) realpath($_SERVER['SCRIPT_FILENAME'] ?? '')));
    if ($raiz === '' || stripos($script, $raiz) !== 0) {
        return '';
    }
    $niveles = array_filter(explode('/', substr($script, strlen($raiz))), 'strlen');
    return str_repeat('../', count($niveles));
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Entero positivo de GET/POST (0 si no es válido)
function int_param(array $origen, string $clave): int
{
    $v = filter_var($origen[$clave] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $v === false ? 0 : (int) $v;
}

// Texto del POST sin espacios sobrantes
function post_str(string $clave): string
{
    $v = $_POST[$clave] ?? '';
    return is_string($v) ? trim($v) : '';
}

// Largo de un texto (cuenta bien los acentos)
function largo(string $texto): int
{
    return mb_strlen($texto, 'UTF-8');
}

// ¿El error es por clave única o foránea?
function es_error_integridad(PDOException $e): bool
{
    return $e->getCode() === '23000';
}

// Título de la página y ruta de navegación
function encabezado(string $titulo, string $icono, array $migas = []): void
{
    echo '<section class="content-header"><div class="container-fluid"><div class="row mb-2">';
    echo '<div class="col-sm-6"><h1><i class="' . e($icono) . '"></i> ' . e($titulo) . '</h1></div>';
    echo '<div class="col-sm-6"><ol class="breadcrumb float-sm-right">';
    echo '<li class="breadcrumb-item"><a href="main.php?module=start"><i class="fas fa-home"></i> Inicio</a></li>';
    foreach ($migas as $texto => $url) {
        echo $url === null
            ? '<li class="breadcrumb-item active">' . e($texto) . '</li>'
            : '<li class="breadcrumb-item"><a href="' . e($url) . '">' . e($texto) . '</a></li>';
    }
    echo '</ol></div></div></div></section>';
}

// Número de factura completo: 001-001-0000001
function nro_factura(string $establecimiento, string $punto, int $numero): string
{
    return sprintf('%s-%s-%07d', $establecimiento, $punto, $numero);
}

// Timbrado vigente para la próxima factura
function timbrado_vigente(PDO $pdo, bool $bloquear = false): ?array
{
    $sql = "SELECT * FROM timbrado
            WHERE estado = 'activo' AND CURDATE() BETWEEN vigencia_desde AND vigencia_hasta
              AND nro_actual < nro_hasta
            ORDER BY id_timbrado LIMIT 1" . ($bloquear ? ' FOR UPDATE' : '');
    $t = $pdo->query($sql)->fetch();
    return $t ?: null;
}

// Texto de la tasa de IVA
function iva_texto($tasa): string
{
    return (int) $tasa === 0 ? 'Exento' : (int) $tasa . ' %';
}

// URL de la foto de perfil (o la imagen por defecto)
function foto_url(?string $foto): string
{
    if ($foto && is_file(FOTO_DIR . basename($foto))) {
        return FOTO_URL . rawurlencode(basename($foto));
    }
    return 'assets/img/user-default.png';
}

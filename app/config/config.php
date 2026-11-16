<?php
// Configuración general del sistema

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sysweb');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'Sistema de Compras y Stock');
define('APP_SHORT', 'SysWeb');

// Datos de la empresa para la factura (ficticios)
define('EMPRESA_NOMBRE', 'SysWeb Comercial S.A.');
define('EMPRESA_RUC', '80099999-9');
define('EMPRESA_DIRECCION', 'Avda. Mcal. López 1234 - Asunción');
define('EMPRESA_TELEFONO', '021 000 000');

// Minutos de inactividad antes de cerrar la sesión automáticamente
define('SESSION_TIMEOUT', 30);

// Intentos fallidos permitidos antes de bloquear el usuario
define('LOGIN_MAX_INTENTOS', 5);

// Fotos de perfil
define('FOTO_MAX_BYTES', 1048576); // 1 MB
define('FOTO_DIR', __DIR__ . '/../../public/uploads/usuarios/');
define('FOTO_URL', 'uploads/usuarios/');

date_default_timezone_set('America/Asuncion');

-- Base de datos "sysweb" - Sistema de Compras y Stock
-- Usuarios de prueba: admin / Admin2026*, compras / Compras2026*, ventas / Ventas2026*

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS sysweb;
CREATE DATABASE sysweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sysweb;

-- Roles, menú y permisos
CREATE TABLE roles (
    id_rol       INT AUTO_INCREMENT PRIMARY KEY,
    rol_nombre   VARCHAR(30)  NOT NULL,
    descripcion  VARCHAR(120) NULL,
    UNIQUE KEY uk_roles_nombre (rol_nombre)
) ENGINE=InnoDB;

CREATE TABLE menu (
    id_menu   INT AUTO_INCREMENT PRIMARY KEY,
    id_padre  INT NULL COMMENT 'NULL = opción de primer nivel o grupo',
    titulo    VARCHAR(50) NOT NULL,
    icono     VARCHAR(50) NOT NULL DEFAULT 'far fa-circle',
    modulo    VARCHAR(30) NULL COMMENT 'Clave del módulo (main.php?module=...). NULL = grupo',
    orden     INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_menu_modulo (modulo),
    CONSTRAINT fk_menu_padre FOREIGN KEY (id_padre)
        REFERENCES menu (id_menu) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE permisos (
    id_rol   INT NOT NULL,
    id_menu  INT NOT NULL,
    PRIMARY KEY (id_rol, id_menu),
    CONSTRAINT fk_permisos_rol FOREIGN KEY (id_rol)
        REFERENCES roles (id_rol) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_permisos_menu FOREIGN KEY (id_menu)
        REFERENCES menu (id_menu) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Usuarios
CREATE TABLE usuarios (
    id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
    usuario        VARCHAR(50)  NOT NULL,
    nombre         VARCHAR(100) NOT NULL,
    password       VARCHAR(255) NOT NULL COMMENT 'Hash generado con password_hash()',
    email          VARCHAR(100) NULL,
    telefono       VARCHAR(20)  NULL,
    foto           VARCHAR(100) NULL,
    id_rol         INT NOT NULL,
    estado         ENUM('activo','bloqueado') NOT NULL DEFAULT 'activo',
    intentos       TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos de login consecutivos',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_usuario (usuario),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol)
        REFERENCES roles (id_rol) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Departamentos y ciudades
CREATE TABLE departamento (
    id_departamento  INT AUTO_INCREMENT PRIMARY KEY,
    dep_descripcion  VARCHAR(60) NOT NULL,
    UNIQUE KEY uk_departamento (dep_descripcion)
) ENGINE=InnoDB;

CREATE TABLE ciudad (
    id_ciudad        INT AUTO_INCREMENT PRIMARY KEY,
    descrip_ciudad   VARCHAR(60) NOT NULL,
    id_departamento  INT NOT NULL,
    UNIQUE KEY uk_ciudad (id_departamento, descrip_ciudad),
    CONSTRAINT fk_ciudad_departamento FOREIGN KEY (id_departamento)
        REFERENCES departamento (id_departamento) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Clientes
CREATE TABLE clientes (
    id_cliente     INT AUTO_INCREMENT PRIMARY KEY,
    ci_ruc         VARCHAR(20)  NOT NULL,
    cli_nombre     VARCHAR(60)  NOT NULL,
    cli_apellido   VARCHAR(60)  NOT NULL,
    cli_direccion  VARCHAR(120) NULL,
    cli_telefono   VARCHAR(20)  NULL,
    id_ciudad      INT NOT NULL,
    UNIQUE KEY uk_clientes_ci_ruc (ci_ruc),
    CONSTRAINT fk_clientes_ciudad FOREIGN KEY (id_ciudad)
        REFERENCES ciudad (id_ciudad) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Referenciales de compras
CREATE TABLE proveedor (
    id_proveedor   INT AUTO_INCREMENT PRIMARY KEY,
    ruc            VARCHAR(20)  NOT NULL,
    razon_social   VARCHAR(100) NOT NULL,
    direccion      VARCHAR(120) NULL,
    telefono       VARCHAR(20)  NULL,
    id_ciudad      INT NOT NULL,
    UNIQUE KEY uk_proveedor_ruc (ruc),
    CONSTRAINT fk_proveedor_ciudad FOREIGN KEY (id_ciudad)
        REFERENCES ciudad (id_ciudad) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE deposito (
    id_deposito  INT AUTO_INCREMENT PRIMARY KEY,
    descrip      VARCHAR(60) NOT NULL,
    UNIQUE KEY uk_deposito (descrip)
) ENGINE=InnoDB;

CREATE TABLE tipo_producto (
    id_tipo_producto  INT AUTO_INCREMENT PRIMARY KEY,
    t_p_descrip       VARCHAR(60) NOT NULL,
    UNIQUE KEY uk_tipo_producto (t_p_descrip)
) ENGINE=InnoDB;

CREATE TABLE u_medida (
    id_u_medida   INT AUTO_INCREMENT PRIMARY KEY,
    u_descrip     VARCHAR(30) NOT NULL,
    UNIQUE KEY uk_u_medida (u_descrip)
) ENGINE=InnoDB;

CREATE TABLE producto (
    cod_producto      INT AUTO_INCREMENT PRIMARY KEY,
    p_descrip         VARCHAR(100) NOT NULL,
    id_tipo_producto  INT NOT NULL,
    id_u_medida       INT NOT NULL,
    precio            DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'Precio de compra de referencia (Gs.)',
    precio_venta      DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'Precio de venta, IVA incluido (Gs.)',
    iva               TINYINT UNSIGNED NOT NULL DEFAULT 10 COMMENT 'Tasa de IVA: 10, 5 o 0 (exento)',
    UNIQUE KEY uk_producto (p_descrip),
    CONSTRAINT fk_producto_tipo FOREIGN KEY (id_tipo_producto)
        REFERENCES tipo_producto (id_tipo_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_producto_medida FOREIGN KEY (id_u_medida)
        REFERENCES u_medida (id_u_medida) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stock por depósito
CREATE TABLE stock (
    id_deposito   INT NOT NULL,
    cod_producto  INT NOT NULL,
    cantidad      INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Nunca negativo: la base rechaza restas por debajo de cero',
    PRIMARY KEY (id_deposito, cod_producto),
    CONSTRAINT fk_stock_deposito FOREIGN KEY (id_deposito)
        REFERENCES deposito (id_deposito) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_stock_producto FOREIGN KEY (cod_producto)
        REFERENCES producto (cod_producto) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Compras: cabecera, detalle y tabla temporal
CREATE TABLE compra (
    id_compra        INT AUTO_INCREMENT PRIMARY KEY,
    fecha            DATE NOT NULL,
    hora             TIME NOT NULL,
    nro_factura      VARCHAR(30) NOT NULL,
    id_proveedor     INT NOT NULL,
    id_deposito      INT NOT NULL,
    id_usuario       INT NOT NULL COMMENT 'Usuario que registró la compra',
    total            DECIMAL(15,0) NOT NULL DEFAULT 0,
    estado           ENUM('activo','anulado') NOT NULL DEFAULT 'activo',
    anulado_por      INT NULL,
    fecha_anulacion  DATETIME NULL,
    UNIQUE KEY uk_compra_factura (id_proveedor, nro_factura),
    CONSTRAINT fk_compra_proveedor FOREIGN KEY (id_proveedor)
        REFERENCES proveedor (id_proveedor) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_compra_deposito FOREIGN KEY (id_deposito)
        REFERENCES deposito (id_deposito) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_compra_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_compra_anulado_por FOREIGN KEY (anulado_por)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE detalle_compra (
    id_compra     INT NOT NULL,
    cod_producto  INT NOT NULL,
    cantidad      INT NOT NULL,
    precio        DECIMAL(15,0) NOT NULL,
    PRIMARY KEY (id_compra, cod_producto),
    CONSTRAINT fk_detalle_compra FOREIGN KEY (id_compra)
        REFERENCES compra (id_compra) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (cod_producto)
        REFERENCES producto (cod_producto) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla temporal: productos de la compra en curso (una lista por usuario)
CREATE TABLE tmp (
    id_tmp        INT AUTO_INCREMENT PRIMARY KEY,
    cod_producto  INT NOT NULL,
    cantidad      INT NOT NULL,
    precio        DECIMAL(15,0) NOT NULL,
    id_usuario    INT NOT NULL,
    UNIQUE KEY uk_tmp_usuario_producto (id_usuario, cod_producto),
    CONSTRAINT fk_tmp_producto FOREIGN KEY (cod_producto)
        REFERENCES producto (cod_producto) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tmp_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Ventas: timbrado, cabecera, detalle y tabla temporal
CREATE TABLE timbrado (
    id_timbrado       INT AUTO_INCREMENT PRIMARY KEY,
    nro_timbrado      CHAR(8) NOT NULL,
    establecimiento   CHAR(3) NOT NULL DEFAULT '001',
    punto_expedicion  CHAR(3) NOT NULL DEFAULT '001',
    vigencia_desde    DATE NOT NULL,
    vigencia_hasta    DATE NOT NULL,
    nro_desde         INT UNSIGNED NOT NULL DEFAULT 1,
    nro_hasta         INT UNSIGNED NOT NULL,
    nro_actual        INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Último número de factura emitido',
    estado            ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    UNIQUE KEY uk_timbrado (nro_timbrado, establecimiento, punto_expedicion)
) ENGINE=InnoDB;

CREATE TABLE venta (
    id_venta         INT AUTO_INCREMENT PRIMARY KEY,
    fecha            DATE NOT NULL,
    hora             TIME NOT NULL,
    id_timbrado      INT NOT NULL,
    nro_factura      INT UNSIGNED NOT NULL COMMENT 'Correlativo dentro del timbrado',
    factura          CHAR(15) NOT NULL COMMENT 'Número completo: 001-001-0000001',
    condicion        ENUM('contado','credito') NOT NULL DEFAULT 'contado',
    id_cliente       INT NOT NULL,
    id_deposito      INT NOT NULL,
    id_usuario       INT NOT NULL COMMENT 'Usuario que registró la venta',
    total            DECIMAL(15,0) NOT NULL DEFAULT 0,
    gravada_10       DECIMAL(15,0) NOT NULL DEFAULT 0,
    gravada_5        DECIMAL(15,0) NOT NULL DEFAULT 0,
    exenta           DECIMAL(15,0) NOT NULL DEFAULT 0,
    iva_10           DECIMAL(15,0) NOT NULL DEFAULT 0,
    iva_5            DECIMAL(15,0) NOT NULL DEFAULT 0,
    estado           ENUM('activo','anulado') NOT NULL DEFAULT 'activo',
    anulado_por      INT NULL,
    fecha_anulacion  DATETIME NULL,
    UNIQUE KEY uk_venta_factura (id_timbrado, nro_factura),
    CONSTRAINT fk_venta_timbrado FOREIGN KEY (id_timbrado)
        REFERENCES timbrado (id_timbrado) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_venta_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes (id_cliente) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_venta_deposito FOREIGN KEY (id_deposito)
        REFERENCES deposito (id_deposito) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_venta_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_venta_anulado_por FOREIGN KEY (anulado_por)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE detalle_venta (
    id_venta      INT NOT NULL,
    cod_producto  INT NOT NULL,
    cantidad      INT NOT NULL,
    precio        DECIMAL(15,0) NOT NULL COMMENT 'Precio unitario, IVA incluido',
    iva           TINYINT UNSIGNED NOT NULL COMMENT 'Tasa aplicada: 10, 5 o 0',
    PRIMARY KEY (id_venta, cod_producto),
    CONSTRAINT fk_detventa_venta FOREIGN KEY (id_venta)
        REFERENCES venta (id_venta) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_detventa_producto FOREIGN KEY (cod_producto)
        REFERENCES producto (cod_producto) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla temporal: productos de la venta en curso
CREATE TABLE tmp_venta (
    id_tmp        INT AUTO_INCREMENT PRIMARY KEY,
    cod_producto  INT NOT NULL,
    cantidad      INT NOT NULL,
    precio        DECIMAL(15,0) NOT NULL,
    id_usuario    INT NOT NULL,
    UNIQUE KEY uk_tmpventa_usuario_producto (id_usuario, cod_producto),
    CONSTRAINT fk_tmpventa_producto FOREIGN KEY (cod_producto)
        REFERENCES producto (cod_producto) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tmpventa_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Trigger: al guardar una compra vacía la tabla temporal de ese usuario
DROP TRIGGER IF EXISTS borrar_tmp;
DELIMITER $$
CREATE TRIGGER borrar_tmp AFTER INSERT ON compra
FOR EACH ROW
BEGIN
    DELETE FROM tmp WHERE id_usuario = NEW.id_usuario;
END$$
DELIMITER ;

-- Trigger: lo mismo para ventas
DROP TRIGGER IF EXISTS borrar_tmp_venta;
DELIMITER $$
CREATE TRIGGER borrar_tmp_venta AFTER INSERT ON venta
FOR EACH ROW
BEGIN
    DELETE FROM tmp_venta WHERE id_usuario = NEW.id_usuario;
END$$
DELIMITER ;

-- Procedimiento para agregar (v_id = 0) o modificar un producto
DROP PROCEDURE IF EXISTS sp_producto_guardar;
DELIMITER $$
CREATE PROCEDURE sp_producto_guardar(
    IN v_id           INT,
    IN v_descrip      VARCHAR(100),
    IN v_tipo         INT,
    IN v_medida       INT,
    IN v_precio       DECIMAL(15,0),
    IN v_precio_venta DECIMAL(15,0),
    IN v_iva          TINYINT UNSIGNED
)
BEGIN
    IF v_id IS NULL OR v_id = 0 THEN
        INSERT INTO producto (p_descrip, id_tipo_producto, id_u_medida, precio, precio_venta, iva)
        VALUES (v_descrip, v_tipo, v_medida, v_precio, v_precio_venta, v_iva);
    ELSE
        UPDATE producto
           SET p_descrip = v_descrip, id_tipo_producto = v_tipo, id_u_medida = v_medida,
               precio = v_precio, precio_venta = v_precio_venta, iva = v_iva
         WHERE cod_producto = v_id;
    END IF;
END$$
DELIMITER ;

-- Datos de prueba
INSERT INTO roles (id_rol, rol_nombre, descripcion) VALUES
(1, 'Super Admin', 'Acceso total al sistema'),
(2, 'Compras',     'Encargado de compras: proveedores, depósitos, productos y stock'),
(3, 'Ventas',      'Encargado de ventas: clientes, facturación y consulta de stock');

-- Menú (modulo NULL = grupo)
INSERT INTO menu (id_menu, id_padre, titulo, icono, modulo, orden) VALUES
(1,  NULL, 'Inicio',                   'fas fa-home',         'start',           1),
(2,  NULL, 'Referenciales Generales',  'fas fa-file-alt',     NULL,              2),
(3,  2,    'Departamentos',            'far fa-circle',       'departamento',    1),
(4,  2,    'Ciudades',                 'far fa-circle',       'ciudad',          2),
(5,  NULL, 'Referenciales de Compras', 'fas fa-file-invoice', NULL,              3),
(6,  5,    'Productos',                'far fa-circle',       'productos',       5),
(7,  5,    'Compras',                  'far fa-circle',       'compras',         6),
(8,  5,    'Stock de productos',       'far fa-circle',       'stock',           7),
(9,  5,    'Reporte de compras',       'far fa-circle',       'reporte_compras', 8),
(10, NULL, 'Referenciales de Ventas',  'fas fa-cog',          NULL,              4),
(11, 10,   'Clientes',                 'far fa-circle',       'clientes',        1),
(17, 10,   'Ventas',                   'far fa-circle',       'ventas',          2),
(18, 10,   'Reporte de ventas',        'far fa-circle',       'reporte_ventas',  3),
(19, 10,   'Timbrados',                'far fa-circle',       'timbrado',        4),
(20, 5,    'Proveedores',              'far fa-circle',       'proveedores',     1),
(21, 5,    'Depósitos',                'far fa-circle',       'deposito',        2),
(22, 5,    'Tipos de producto',        'far fa-circle',       'tipo_producto',   3),
(23, 5,    'Unidades de medida',       'far fa-circle',       'u_medida',        4),
(12, NULL, 'Administración',           'fas fa-users-cog',    NULL,              5),
(13, 12,   'Administrar usuarios',     'far fa-circle',       'user',            1),
(14, 12,   'Permisos por rol',         'far fa-circle',       'permisos',        2),
(15, NULL, 'Mi perfil',                'fas fa-user',         'perfil',          6),
(16, NULL, 'Cambiar contraseña',       'fas fa-key',          'password',        7);

-- Super Admin: todas las opciones
INSERT INTO permisos (id_rol, id_menu) SELECT 1, id_menu FROM menu WHERE modulo IS NOT NULL;
-- Compras
INSERT INTO permisos (id_rol, id_menu) VALUES (2,1),(2,3),(2,4),(2,6),(2,7),(2,8),(2,9),(2,15),(2,16),(2,20),(2,21),(2,22),(2,23);
-- Ventas
INSERT INTO permisos (id_rol, id_menu) VALUES (3,1),(3,3),(3,4),(3,8),(3,11),(3,15),(3,16),(3,17),(3,18);

INSERT INTO usuarios (usuario, nombre, password, email, telefono, id_rol, estado) VALUES
('admin',   'Administrador del Sistema', '$2y$10$sjvc03ly9IzS7KJ1KnwbQ.m3aCqddSv5Mrx5N8tGYJYrEZKroYi1.', 'admin@sysweb.local',   '0981000001', 1, 'activo'),
('compras', 'Usuario de Compras',        '$2y$10$raJmJXUi3TxGW.6T8qtKL.wwVQxkAgRGhzTxmLUtpdcowEH./CEAy', 'compras@sysweb.local', '0981000002', 2, 'activo'),
('ventas',  'Usuario de Ventas',         '$2y$10$O08zUDe0AjKWjCYrjOQWBeJSUkK5t742IlFTeUNKgnX0x/55t/gF.', 'ventas@sysweb.local',  '0981000003', 3, 'activo');

INSERT INTO departamento (dep_descripcion) VALUES
('Central'), ('Alto Paraná'), ('Itapúa'), ('Cordillera'), ('Caaguazú');

INSERT INTO ciudad (descrip_ciudad, id_departamento) VALUES
('Asunción', 1), ('San Lorenzo', 1), ('Luque', 1), ('Fernando de la Mora', 1),
('Ciudad del Este', 2), ('Hernandarias', 2),
('Encarnación', 3),
('Caacupé', 4),
('Coronel Oviedo', 5);

INSERT INTO clientes (ci_ruc, cli_nombre, cli_apellido, cli_direccion, cli_telefono, id_ciudad) VALUES
('4567890',   'María',  'González', 'Avda. España 1234',   '0982111222', 1),
('3456789-1', 'Carlos', 'Benítez',  'Mcal. López 567',     '0971333444', 2),
('5678901',   'Laura',  'Martínez', 'Ruta 2 Km 30',        '0991555666', 8);

INSERT INTO proveedor (ruc, razon_social, direccion, telefono, id_ciudad) VALUES
('80012345-6', 'Lácteos del Sur S.A.',       'Ruta 1 Km 20',          '021500100', 2),
('80023456-7', 'Distribuidora Central S.R.L.', 'Avda. Artigas 2500',  '021600200', 1),
('80034567-8', 'Comercial Este S.A.',          'Avda. Monseñor Rodríguez 900', '061500300', 5);

INSERT INTO deposito (descrip) VALUES ('Dep. Central'), ('Dep. Suc. 2');

INSERT INTO tipo_producto (t_p_descrip) VALUES ('Lácteos'), ('Bebidas'), ('Almacén'), ('Limpieza');

INSERT INTO u_medida (u_descrip) VALUES ('Unidad'), ('1 Litro'), ('Kilogramo'), ('Paquete');

INSERT INTO producto (p_descrip, id_tipo_producto, id_u_medida, precio, precio_venta, iva) VALUES
('Leche entera',            1, 2,   6500,   8500,  5),
('Yogur descremado',        1, 2,   9500,  12500, 10),
('Queso Paraguay',          1, 3,  38000,  48000, 10),
('Manteca 200 g',           1, 1,  12000,  15500, 10),
('Agua mineral 2 L',        2, 1,   7000,   9500, 10),
('Gaseosa cola 2 L',        2, 1,  12500,  16000, 10),
('Jugo de naranja',         2, 2,  10500,  13500, 10),
('Cerveza lata 473 ml',     2, 1,   8000,  10500, 10),
('Arroz tipo 1',            3, 3,   9000,  11500,  5),
('Fideo spaghetti 500 g',   3, 4,   6000,   8000,  5),
('Azúcar blanca',           3, 3,   7500,   9500, 10),
('Yerba mate 1 kg',         3, 4,  28000,  35000,  5),
('Aceite de girasol',       3, 2,  16000,  20500,  5),
('Detergente 500 ml',       4, 1,   9500,  12500, 10),
('Lavandina 1 L',           4, 2,   5500,   7500, 10),
('Jabón en polvo 800 g',    4, 4,  21000,  27000, 10);

-- Timbrado de prueba (ficticio)
INSERT INTO timbrado (nro_timbrado, establecimiento, punto_expedicion, vigencia_desde, vigencia_hasta, nro_desde, nro_hasta, nro_actual, estado) VALUES
('12345678', '001', '001', MAKEDATE(YEAR(CURDATE()), 1), DATE_ADD(MAKEDATE(YEAR(CURDATE()), 1), INTERVAL 2 YEAR), 1, 1000, 0, 'activo');

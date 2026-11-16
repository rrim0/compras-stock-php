# Mini-sistema de Gestión con Compras y Stock

**Actividad 8.1 – Práctica Integradora – Lenguaje de Programación III (Unidad VIII)**

Alumno: ______________________
Video demostrativo: ______________________ (pegar acá el enlace de YouTube / Drive)

Este README explica solo lo que pide la Actividad 8.1. El sistema tiene algunas cosas de más (ventas, clientes, etc.) que no forman parte de la tarea; están explicadas aparte en `README_completo.md`.

---

## 1. Requisitos

- WAMP instalado y en verde (Apache + PHP 7.4 o más nuevo + MySQL o MariaDB).
- Un navegador (Chrome, Edge o Firefox).
- No hace falta instalar nada más: las librerías ya vienen dentro de la carpeta del proyecto.

## 2. Pasos de instalación

1. Copiar la carpeta `sistema_compras` dentro de `C:\wamp64\www\`.
2. Entrar a `http://localhost/phpmyadmin` (usuario `root`, sin contraseña).
3. Ir a la pestaña **Importar**, elegir el archivo `database/sysweb.sql` y darle a **Importar**. Tiene que decir "45 consultas ejecutadas". Con eso se crea la base `sysweb` con todas las tablas y los datos de prueba.
4. Abrir `app/config/config.php` y revisar el puerto:
   - `3306` si se usa MySQL (así viene).
   - `3307` si se usa MariaDB.
   - Si el usuario `root` tiene contraseña, ponerla en `DB_PASS`.
5. Abrir en el navegador: `http://localhost/sistema_compras/`

Si al importar no aparece la base en el panel izquierdo de phpMyAdmin, cerrar sesión y volver a entrar (la base igual está creada).

## 3. Credenciales de prueba

| Usuario | Contraseña | Rol | Qué puede hacer |
|---|---|---|---|
| admin | Admin2026* | Super Admin (administrador) | Todo: usuarios, permisos, productos, compras, reportes |
| compras | Compras2026* | Compras (operador) | Productos, compras, stock y reporte de compras |

Los usuarios se cargan con el script SQL (no hay pantalla de registro público). Las contraseñas están guardadas con hash, no en texto.

Si un usuario se equivoca 5 veces seguidas queda **bloqueado**. Lo desbloquea el admin en *Administración → Administrar usuarios → Desbloquear*.
Si se llega a bloquear el propio admin, se arregla desde phpMyAdmin con:

```sql
UPDATE sysweb.usuarios SET estado = 'activo', intentos = 0 WHERE usuario = 'admin';
```

## 4. Qué pide la actividad y dónde está

| Lo que pide | Cómo se hizo | Archivo principal |
|---|---|---|
| Login con contraseña hasheada | `password_hash()` al guardar y `password_verify()` al entrar | `public/login_check.php` |
| Regenerar ID de sesión y cerrar sesión | `session_regenerate_id(true)` después del login; el logout destruye la sesión | `public/login_check.php`, `public/logout.php` |
| Bloqueo por intentos (contador en BD) | Columna `usuarios.intentos`; al llegar a 5 pasa a `estado = 'bloqueado'` | `public/login_check.php` |
| Desbloqueo por admin | Botón Desbloquear en la lista de usuarios | `public/modules/user/procesar.php` |
| Menú dinámico por rol | El menú se arma leyendo las tablas `roles`, `menu` y `permisos` | `app/views/layout/side_menu.php` |
| Cada usuario ve solo lo permitido | Además de ocultar el menú, cada página revisa el permiso en el servidor | `app/core/session.php` (funciones `can` y `require_area`) |
| CRUD completo de un referencial | Productos: listar, agregar, modificar y eliminar | `app/views/productos/`, `public/modules/productos/procesar.php` |
| Validaciones | Obligatorios, largo del texto, números enteros, y se limpia lo que se muestra | `public/modules/productos/procesar.php` |
| Sentencias preparadas y función en la BD | Todo con PDO preparado; alta y modificación usan el procedimiento `sp_producto_guardar` | `database/sysweb.sql` |
| Compra maestro-detalle | Cabecera en `compra` y renglones en `detalle_compra` | `public/modules/compras/procesar.php` |
| Transacción begin/commit/rollback | `beginTransaction()`, `commit()` y `rollBack()` si algo falla | `public/modules/compras/procesar.php` |
| Aumentar stock al confirmar | Suma la cantidad de cada producto en la tabla `stock` | `public/modules/compras/procesar.php` |
| Recalcular total y guardar | El total se calcula en el servidor sumando cantidad x precio | `public/modules/compras/procesar.php` |
| Reporte de compras con filtros | Fecha desde / hasta, proveedor (opcional) y estado | `app/views/reporte_compras/view.php` |
| Exportar CSV o PDF | Tiene los dos | `public/modules/reporte_compras/csv.php` y `pdf.php` |
| PDO + prepared statements | En todas las consultas | `app/config/database.php` |
| `htmlspecialchars` en la salida | Función `e()` que se usa en todas las vistas | `app/core/session.php` |
| Estructura `app/` y `public/` | Las credenciales están en `app/config/`, fuera de la carpeta pública | ver punto 5 |

## 5. Estructura de carpetas

```
sistema_compras/
├── app/                  Código interno (no se puede abrir desde el navegador)
│   ├── config/           config.php (credenciales) y database.php (conexión PDO)
│   ├── core/             sesión, permisos, rutas, PDF
│   └── views/            las pantallas
├── public/               Lo único que ve el navegador
│   ├── index.php         login
│   ├── login_check.php   proceso del login
│   ├── main.php          pantalla principal con el menú
│   ├── logout.php
│   ├── modules/          procesos de cada módulo (guardar, eliminar, reportes)
│   └── assets/           CSS, JS e imágenes
├── database/sysweb.sql   Dump de la base con datos semilla
└── vendor/               Librería para generar PDF
```

## 6. Estructura de tablas (las que usa la actividad)

**Seguridad y menú**

| Tabla | Campos | Para qué |
|---|---|---|
| `roles` | id_rol, rol_nombre, descripcion | Los roles (Super Admin, Compras, Ventas) |
| `menu` | id_menu, id_padre, titulo, icono, modulo, orden | Las opciones del menú |
| `permisos` | id_rol, id_menu | Qué opción puede ver cada rol |
| `usuarios` | id_usuario, usuario, nombre, password, email, telefono, foto, id_rol, estado, intentos, created_at | Usuarios; `intentos` es el contador de fallos y `estado` puede ser activo o bloqueado |

**Referencial**

| Tabla | Campos | Para qué |
|---|---|---|
| `producto` | cod_producto, p_descrip, id_tipo_producto, id_u_medida, precio, precio_venta, iva | Productos (`precio` es el precio de compra) |
| `tipo_producto` | id_tipo_producto, t_p_descrip | Tipo del producto |
| `u_medida` | id_u_medida, u_descrip | Unidad de medida |
| `stock` | id_deposito, cod_producto, cantidad | Stock de cada producto por depósito |

**Compras (maestro-detalle)**

| Tabla | Campos | Para qué |
|---|---|---|
| `compra` | id_compra, fecha, hora, nro_factura, id_proveedor, id_deposito, id_usuario, total, estado, anulado_por, fecha_anulacion | Cabecera de la compra (maestro) |
| `detalle_compra` | id_compra, cod_producto, cantidad, precio | Renglones de la compra (detalle) |
| `tmp` | id_tmp, cod_producto, cantidad, precio, id_usuario | Productos que se van cargando antes de guardar |
| `proveedor` | id_proveedor, ruc, razon_social, direccion, telefono, id_ciudad | Proveedores |
| `deposito` | id_deposito, descrip | Depósitos |

También hay un trigger (`borrar_tmp`) que vacía la tabla `tmp` del usuario cuando se guarda la compra, y un procedimiento (`sp_producto_guardar`) para el alta y la modificación de productos.

## 7. Datos semilla

El dump trae: 3 usuarios, 3 roles con su menú y permisos, 16 productos, 3 proveedores, 2 depósitos, y ciudades y departamentos. El stock arranca en cero y no hay compras cargadas, así se ve cómo sube el stock al registrar la primera compra.

## 8. Notas

- **Base de datos:** la consigna dice PostgreSQL. Este trabajo está hecho en **MySQL (WAMP)** porque el docente autorizó entregar el proyecto de las 14 etapas de la materia como resultado de esta actividad. La conexión es igual con PDO, solo cambia el driver.
- **Función PL/pgSQL:** como es MySQL, en su lugar se usó un procedimiento almacenado (`sp_producto_guardar`), que cumple la misma función.
- **Nombres de tablas:** la consigna las llama `compras` y `compra_detalle`; acá se llaman `compra` y `detalle_compra`, que son los nombres que se usan en el proyecto de la materia. Hacen lo mismo.
- **Stock:** la consigna propone el stock como un campo del producto. Acá está en una tabla aparte (`stock`) porque se maneja por depósito. En el listado de productos se ve el stock total.
- **Roles:** la consigna pide como mínimo Admin y Operador. Acá el admin es "Super Admin" y el operador es "Compras". Hay un tercer rol (Ventas) que no hace falta para la actividad.
- **Productos semilla:** la consigna pide de 3 a 5 y hay 16, para que se pueda ver la paginación al elegir productos en la compra.
- **Anular compra:** las compras no se borran, se anulan, y al anular se descuenta el stock que había entrado.
- **Extras que no pide la actividad:** ventas con factura, clientes, proveedores, depósitos, perfil con foto y reportes PDF de otros módulos. Están en el menú pero no se usan para esta entrega.
- **Librerías de terceros usadas:** AdminLTE 3 y Bootstrap 4 (diseño), jQuery, DataTables y Select2 (tablas y listas), Font Awesome (íconos), HTML2PDF y TCPDF (reportes en PDF).
- **Herramientas de apoyo:** en el desarrollo y las pruebas se usó un asistente de inteligencia artificial (Claude).

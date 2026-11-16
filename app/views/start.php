<?php
// Página de inicio (formulario de movimiento)
defined('APP') or exit;

$pdo = db();
$tot_compras  = (int) $pdo->query("SELECT COUNT(*) FROM compra WHERE estado = 'activo'")->fetchColumn();
$tot_clientes = (int) $pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
$tot_stock    = (int) $pdo->query('SELECT COALESCE(SUM(cantidad), 0) FROM stock')->fetchColumn();
$tot_ventas   = (int) $pdo->query("SELECT COUNT(*) FROM venta WHERE estado = 'activo'")->fetchColumn();
?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1><i class="fas fa-home"></i> Inicio</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active"><i class="fas fa-home"></i> Inicio</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="callout callout-info">
            <h5>Bienvenido/a, <?= e($_SESSION['nombre']) ?></h5>
            <p>Rol: <b><?= e($_SESSION['rol']) ?></b>. Seleccione una opción del menú para comenzar.</p>
        </div>

        <h2 class="h3 mb-3">Formulario de movimiento</h2>
        <div class="row">
            <?php if (can('compras')): ?>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $tot_compras ?></h3>
                        <p>Compras registradas<br><small>Registrar la compra de productos</small></p>
                    </div>
                    <div class="icon"><i class="fas fa-piggy-bank"></i></div>
                    <a href="main.php?module=form_compras" class="small-box-footer">Registrar compra <i class="fas fa-plus"></i></a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (can('ventas')): ?>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $tot_ventas ?></h3>
                        <p>Ventas registradas<br><small>Registrar la venta de productos</small></p>
                    </div>
                    <div class="icon"><i class="fas fa-cash-register"></i></div>
                    <a href="main.php?module=form_ventas" class="small-box-footer">Registrar venta <i class="fas fa-plus"></i></a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (can('clientes')): ?>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $tot_clientes ?></h3>
                        <p>Clientes<br><small>Administrar clientes</small></p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <a href="main.php?module=clientes" class="small-box-footer">Ver clientes <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (can('reporte_compras')): ?>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><i class="fas fa-file-csv"></i></h3>
                        <p>Reporte de compras<br><small>Filtros, CSV y PDF</small></p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-bar"></i></div>
                    <a href="main.php?module=reporte_compras" class="small-box-footer">Ver reporte <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (can('stock')): ?>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= gs($tot_stock) ?></h3>
                        <p>Unidades en stock<br><small>Visualizar stock de productos</small></p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-area"></i></div>
                    <a href="main.php?module=stock" class="small-box-footer">Ver stock <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

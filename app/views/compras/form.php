<?php
// Formulario para agregar compra y formulario modal para cargar productos
defined('APP') or exit;

$pdo = db();
// Código estimado de la próxima compra
$codigo      = (int) $pdo->query('SELECT COALESCE(MAX(id_compra), 0) + 1 FROM compra')->fetchColumn();
$proveedores = $pdo->query('SELECT id_proveedor, razon_social, ruc FROM proveedor ORDER BY razon_social')->fetchAll();
$depositos   = $pdo->query('SELECT id_deposito, descrip FROM deposito ORDER BY id_deposito')->fetchAll();

encabezado('Agregar compra', 'fas fa-edit', ['Compras' => 'main.php?module=compras', 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos de la compra</h3></div>
            <form action="modules/compras/procesar.php?act=insert" method="post" id="form-compra" autocomplete="off">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label>Código</label>
                            <input type="text" class="form-control" value="<?= $codigo ?>" readonly>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha"
                                   value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Hora</label>
                            <input type="text" class="form-control" id="hora" value="<?= date('H:i:s') ?>" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="id_proveedor">Proveedor</label>
                            <select class="form-control select2" id="id_proveedor" name="id_proveedor" required>
                                <option value="">-- Seleccionar proveedor --</option>
                                <?php foreach ($proveedores as $p): ?>
                                    <option value="<?= (int) $p['id_proveedor'] ?>"><?= e($p['razon_social'] . ' - RUC ' . $p['ruc']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="nro_factura">Nº de factura</label>
                            <input type="text" class="form-control" id="nro_factura" name="nro_factura"
                                   placeholder="001-001-0000001" maxlength="30" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="id_deposito">Depósito</label>
                            <select class="form-control select2" id="id_deposito" name="id_deposito" required>
                                <option value="">-- Seleccionar depósito --</option>
                                <?php foreach ($depositos as $d): ?>
                                    <option value="<?= (int) $d['id_deposito'] ?>"><?= e($d['descrip']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Productos</label><br>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalProductos">
                            <i class="fas fa-plus"></i> Agregar productos
                        </button>
                    </div>

                    <!-- Detalle de la compra (AJAX) -->
                    <div id="detalle-compra" class="table-responsive">
                        <div class="text-center p-3"><img src="assets/img/ajax-loader.gif" alt="Cargando..."></div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="btn-guardar"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=compras" class="btn btn-default">Volver</a>
                    <button type="button" class="btn btn-outline-danger float-right" id="btn-vaciar">
                        <i class="fas fa-trash"></i> Vaciar productos
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Modal para cargar productos -->
<div class="modal fade" id="modalProductos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search"></i> Buscar productos</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="q" placeholder="Buscar por nombre o tipo de producto" maxlength="60">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="btn-buscar"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                </div>
                <div id="loader" class="text-center"></div>
                <div id="outer_div"></div><!-- tabla de productos -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

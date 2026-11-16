<?php
// Formulario para registrar una venta
defined('APP') or exit;

$pdo       = db();
$t         = timbrado_vigente($pdo);
$proxima   = $t ? nro_factura($t['establecimiento'], $t['punto_expedicion'], max((int) $t['nro_actual'], (int) $t['nro_desde'] - 1) + 1) : '';
$clientes  = $pdo->query('SELECT id_cliente, ci_ruc, cli_nombre, cli_apellido FROM clientes ORDER BY cli_apellido, cli_nombre')->fetchAll();
$depositos = $pdo->query('SELECT id_deposito, descrip FROM deposito ORDER BY id_deposito')->fetchAll();

encabezado('Registrar venta', 'fas fa-cash-register', ['Ventas' => 'main.php?module=ventas', 'Registrar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <?php if (!$t): ?>
            <div class="alert alert-danger" id="sin-timbrado">
                <i class="fas fa-ban"></i> No hay un timbrado activo, vigente y con numeración disponible: no se pueden emitir facturas.
                <?php if (can('timbrado')): ?><a href="main.php?module=timbrado">Registrar un timbrado</a><?php else: ?>Solicite al administrador que registre uno.<?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos de la factura</h3></div>
            <form action="modules/ventas/procesar.php?act=insert" method="post" id="form-venta" autocomplete="off">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Factura Nº <small class="text-muted">(próxima)</small></label>
                            <input type="text" class="form-control" id="proxima-factura" value="<?= e($proxima) ?>" readonly>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Timbrado</label>
                            <input type="text" class="form-control" id="timbrado-info" readonly
                                   value="<?= $t ? e($t['nro_timbrado'] . ' (vence ' . date('d/m/Y', strtotime($t['vigencia_hasta'])) . ')') : '' ?>">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Fecha</label>
                            <input type="text" class="form-control" value="<?= date('d/m/Y') ?>" readonly>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="condicion">Condición</label>
                            <select class="form-control" id="condicion" name="condicion" required>
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="id_cliente">Cliente</label>
                            <select class="form-control select2" id="id_cliente" name="id_cliente" required>
                                <option value="">-- Seleccionar cliente --</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= (int) $c['id_cliente'] ?>"><?= e($c['cli_apellido'] . ', ' . $c['cli_nombre'] . ' - C.I./RUC ' . $c['ci_ruc']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="id_deposito">Depósito <small class="text-muted">(de donde sale la mercadería)</small></label>
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
                        <button type="button" class="btn btn-info" id="btn-productos">
                            <i class="fas fa-plus"></i> Agregar productos
                        </button>
                    </div>

                    <div id="detalle-venta" class="table-responsive">
                        <div class="text-center p-3"><img src="assets/img/ajax-loader.gif" alt="Cargando..."></div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="btn-guardar" <?= $t ? '' : 'disabled' ?>><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=ventas" class="btn btn-default">Volver</a>
                    <button type="button" class="btn btn-outline-danger float-right" id="btn-vaciar">
                        <i class="fas fa-trash"></i> Vaciar productos
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Modal para elegir productos -->
<div class="modal fade" id="modalProductos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search"></i> Productos disponibles en <span id="modal-deposito"></span></h5>
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
                <div id="outer_div"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

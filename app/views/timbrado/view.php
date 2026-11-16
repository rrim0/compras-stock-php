<?php
// Lista y alta de timbrados
defined('APP') or exit;

$pdo   = db();
$filas = $pdo->query("SELECT t.*, (SELECT COUNT(*) FROM venta v WHERE v.id_timbrado = t.id_timbrado) AS facturas,
                             (t.estado = 'activo' AND CURDATE() BETWEEN t.vigencia_desde AND t.vigencia_hasta AND t.nro_actual < t.nro_hasta) AS utilizable
                      FROM timbrado t ORDER BY t.id_timbrado")->fetchAll();
$en_uso = timbrado_vigente($pdo);

encabezado('Timbrados', 'fas fa-stamp', ['Referenciales de ventas' => null, 'Timbrados' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Lista de timbrados</h3></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped" id="tabla-timbrados">
                    <thead>
                    <tr>
                        <th>Timbrado Nº</th>
                        <th class="text-center">Establecimiento - Punto</th>
                        <th class="text-center">Vigencia</th>
                        <th class="text-center">Numeración autorizada</th>
                        <th>Última factura emitida</th>
                        <th class="text-center">Facturas</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $t): $emitio = (int) $t['nro_actual'] >= (int) $t['nro_desde']; ?>
                        <tr>
                            <td><?= e($t['nro_timbrado']) ?>
                                <?php if ($en_uso && (int) $en_uso['id_timbrado'] === (int) $t['id_timbrado']): ?><span class="badge badge-info">En uso</span><?php endif; ?></td>
                            <td class="text-center"><?= e($t['establecimiento'] . '-' . $t['punto_expedicion']) ?></td>
                            <td class="text-center"><?= e(date('d/m/Y', strtotime($t['vigencia_desde']))) ?> al <?= e(date('d/m/Y', strtotime($t['vigencia_hasta']))) ?></td>
                            <td class="text-center"><?= sprintf('%07d', $t['nro_desde']) ?> al <?= sprintf('%07d', $t['nro_hasta']) ?></td>
                            <td><?= $emitio ? e(nro_factura($t['establecimiento'], $t['punto_expedicion'], (int) $t['nro_actual'])) : '<span class="text-muted">Ninguna</span>' ?></td>
                            <td class="text-center"><?= (int) $t['facturas'] ?></td>
                            <td class="text-center">
                                <?php if ($t['estado'] !== 'activo'): ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php elseif ((int) $t['utilizable'] === 1): ?>
                                    <span class="badge badge-success">Activo y vigente</span>
                                <?php elseif ((int) $t['nro_actual'] >= (int) $t['nro_hasta']): ?>
                                    <span class="badge badge-danger">Numeración agotada</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Fuera de vigencia</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <form action="modules/timbrado/procesar.php?act=estado" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Confirma <?= $t['estado'] === 'activo' ? 'desactivar' : 'activar' ?> el timbrado <?= e($t['nro_timbrado']) ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $t['id_timbrado'] ?>">
                                    <?php if ($t['estado'] === 'activo'): ?>
                                        <button type="submit" class="btn btn-warning btn-sm" title="Desactivar"><i class="fas fa-pause"></i> Desactivar</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-success btn-sm" title="Activar"><i class="fas fa-play"></i> Activar</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Registrar timbrado</h3></div>
            <form action="modules/timbrado/procesar.php?act=insert" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for="nro_timbrado">Timbrado Nº</label>
                            <input type="text" class="form-control" id="nro_timbrado" name="nro_timbrado" pattern="[0-9]{8}" maxlength="8" placeholder="8 dígitos" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="establecimiento">Establecimiento</label>
                            <input type="text" class="form-control" id="establecimiento" name="establecimiento" pattern="[0-9]{3}" maxlength="3" value="001" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="punto_expedicion">Punto de expedición</label>
                            <input type="text" class="form-control" id="punto_expedicion" name="punto_expedicion" pattern="[0-9]{3}" maxlength="3" value="001" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="vigencia_desde">Vigencia desde</label>
                            <input type="date" class="form-control" id="vigencia_desde" name="vigencia_desde" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="vigencia_hasta">Vigencia hasta</label>
                            <input type="date" class="form-control" id="vigencia_hasta" name="vigencia_hasta" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="nro_desde">Numeración desde</label>
                            <input type="number" class="form-control" id="nro_desde" name="nro_desde" min="1" max="9999999" step="1" value="1" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="nro_hasta">Numeración hasta</label>
                            <input type="number" class="form-control" id="nro_hasta" name="nro_hasta" min="1" max="9999999" step="1" required>
                        </div>
                    </div>
                    <small class="text-muted">La primera factura llevará el número "desde". Un timbrado con facturas emitidas no se elimina: se desactiva.</small>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</section>

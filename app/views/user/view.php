<?php
// Tabla de usuarios con las opciones agregar, editar, activar y bloquear
defined('APP') or exit;

$usuarios = db()->query('SELECT u.id_usuario, u.usuario, u.nombre, u.email, u.telefono, u.foto, u.estado, u.intentos,
                                r.rol_nombre AS rol
                         FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
                         ORDER BY u.id_usuario')->fetchAll();

encabezado('Administrar usuarios', 'fas fa-users-cog', ['Usuarios' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de usuarios</h3>
                <div class="card-tools">
                    <a href="modules/user/reporte.php" target="_blank" class="btn btn-warning btn-sm">
                        <i class="fas fa-print"></i> Imprimir reporte
                    </a>
                    <a href="main.php?module=form_user&amp;form=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Agregar
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Nro.</th>
                        <th class="text-center">Foto</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Intentos fallidos</th>
                        <th class="text-center no-sort">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $i => $u): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td class="text-center"><img src="<?= e(foto_url($u['foto'])) ?>" class="img-circle img-tabla" alt="Foto"></td>
                            <td><?= e($u['usuario']) ?></td>
                            <td><?= e($u['nombre']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['telefono']) ?></td>
                            <td><?= e($u['rol']) ?></td>
                            <td class="text-center">
                                <?php if ($u['estado'] === 'activo'): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= (int) $u['intentos'] >= LOGIN_MAX_INTENTOS ? 'badge-danger' : ((int) $u['intentos'] > 0 ? 'badge-warning' : 'badge-light') ?>">
                                    <?= (int) $u['intentos'] ?> / <?= LOGIN_MAX_INTENTOS ?>
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="main.php?module=form_user&amp;form=edit&amp;id=<?= (int) $u['id_usuario'] ?>"
                                   class="btn btn-primary btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                <?php if ((int) $u['id_usuario'] !== (int) $_SESSION['id_usuario']): ?>
                                    <form action="modules/user/procesar.php?act=<?= $u['estado'] === 'activo' ? 'off' : 'on' ?>"
                                          method="post" class="d-inline"
                                          onsubmit="return confirm('¿Confirma <?= $u['estado'] === 'activo' ? 'bloquear' : 'desbloquear' ?> al usuario <?= e($u['usuario']) ?>?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                                        <?php if ($u['estado'] === 'activo'): ?>
                                            <button type="submit" class="btn btn-warning btn-sm" title="Bloquear"><i class="fas fa-lock"></i></button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-success btn-sm" title="Desbloquear"><i class="fas fa-lock-open"></i> Desbloquear</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
// Vista del perfil del usuario autenticado
defined('APP') or exit;

$st = db()->prepare('SELECT u.usuario, u.nombre, u.email, u.telefono, u.foto, r.rol_nombre AS rol, u.created_at FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol WHERE u.id_usuario = ?');
$st->execute([$_SESSION['id_usuario']]);
$u = $st->fetch();

encabezado('Perfil de usuario', 'fas fa-user', ['Perfil' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle img-perfil" src="<?= e(foto_url($u['foto'])) ?>" alt="Foto de perfil">
                        </div>
                        <h3 class="profile-username text-center"><?= e($u['nombre']) ?></h3>
                        <p class="text-muted text-center"><?= e($u['rol']) ?></p>
                        <a href="main.php?module=form_perfil" class="btn btn-primary btn-block"><i class="fas fa-edit"></i> Modificar perfil</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Datos del usuario</h3></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Usuario</dt>         <dd class="col-sm-8"><?= e($u['usuario']) ?></dd>
                            <dt class="col-sm-4">Nombre</dt>          <dd class="col-sm-8"><?= e($u['nombre']) ?></dd>
                            <dt class="col-sm-4">Email</dt>           <dd class="col-sm-8"><?= e($u['email'] ?: '-') ?></dd>
                            <dt class="col-sm-4">Teléfono</dt>        <dd class="col-sm-8"><?= e($u['telefono'] ?: '-') ?></dd>
                            <dt class="col-sm-4">Rol</dt> <dd class="col-sm-8"><?= e($u['rol']) ?></dd>
                            <dt class="col-sm-4">Registrado el</dt>   <dd class="col-sm-8"><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

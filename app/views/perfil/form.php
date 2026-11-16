<?php
// Formulario para modificar el perfil del usuario (incluye la foto)
defined('APP') or exit;

$st = db()->prepare('SELECT u.usuario, u.nombre, u.email, u.telefono, u.foto, r.rol_nombre AS rol FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol WHERE u.id_usuario = ?');
$st->execute([$_SESSION['id_usuario']]);
$u = $st->fetch();

encabezado('Modificar perfil', 'fas fa-user-edit', ['Perfil' => 'main.php?module=perfil', 'Modificar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del perfil</h3></div>
            <form action="modules/perfil/procesar.php" method="post" enctype="multipart/form-data" class="form-horizontal">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Usuario</label>
                        <div class="col-sm-5"><input type="text" class="form-control" value="<?= e($u['usuario']) ?>" readonly></div>
                    </div>
                    <div class="form-group row">
                        <label for="nombre" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= e($u['nombre']) ?>" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-5">
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($u['email']) ?>" maxlength="100">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="telefono" class="col-sm-2 col-form-label">Teléfono</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= e($u['telefono']) ?>"
                                   pattern="[0-9+\-() ]{6,20}" maxlength="20">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Rol</label>
                        <div class="col-sm-5"><input type="text" class="form-control" value="<?= e($u['rol']) ?>" readonly></div>
                    </div>
                    <div class="form-group row">
                        <label for="foto" class="col-sm-2 col-form-label">Foto</label>
                        <div class="col-sm-5">
                            <img src="<?= e(foto_url($u['foto'])) ?>" class="img-circle img-perfil mb-2" alt="Foto actual" id="foto-preview">
                            <input type="file" class="form-control-file" id="foto" name="foto" accept="image/jpeg,image/png">
                            <small class="form-text text-muted">Formatos JPG o PNG, máximo 1 MB. Dejar vacío para mantener la foto actual.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=perfil" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

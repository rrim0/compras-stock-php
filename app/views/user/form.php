<?php
// Formulario para agregar y editar usuarios
defined('APP') or exit;

$editar = ($_GET['form'] ?? '') === 'edit';
$u = ['id_usuario' => 0, 'usuario' => '', 'nombre' => '', 'email' => '', 'telefono' => '', 'id_rol' => 2];

if ($editar) {
    $id = int_param($_GET, 'id');
    $st = db()->prepare('SELECT id_usuario, usuario, nombre, email, telefono, id_rol FROM usuarios WHERE id_usuario = ?');
    $st->execute([$id]);
    $u = $st->fetch();
    if (!$u) {
        echo '<section class="content pt-3"><div class="alert alert-warning">El usuario no existe. '
           . '<a href="main.php?module=user">Volver</a></div></section>';
        return;
    }
}
$es_propio = (int) $u['id_usuario'] === (int) $_SESSION['id_usuario'];
$roles     = db()->query('SELECT id_rol, rol_nombre FROM roles ORDER BY id_rol')->fetchAll();

encabezado($editar ? 'Editar usuario' : 'Agregar usuario', 'fas fa-user-edit',
    ['Usuarios' => 'main.php?module=user', $editar ? 'Editar' : 'Agregar' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Datos del usuario</h3></div>
            <form action="modules/user/procesar.php?act=<?= $editar ? 'update' : 'insert' ?>" method="post"
                  class="form-horizontal" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="usuario" class="col-sm-2 col-form-label">Usuario</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="usuario" name="usuario" value="<?= e($u['usuario']) ?>"
                                   pattern="[A-Za-z0-9_.]{3,50}" maxlength="50" required>
                            <small class="form-text text-muted">3 a 50 caracteres: letras, números, punto o guion bajo.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="nombre" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= e($u['nombre']) ?>" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Contraseña</label>
                        <div class="col-sm-5">
                            <input type="password" class="form-control" id="password" name="password"
                                   minlength="8" maxlength="72" <?= $editar ? '' : 'required' ?>>
                            <small class="form-text text-muted">
                                <?= $editar ? 'Dejar vacío para mantener la contraseña actual.' : 'Mínimo 8 caracteres.' ?>
                            </small>
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
                        <label for="id_rol" class="col-sm-2 col-form-label">Rol</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="id_rol" name="id_rol" required <?= $es_propio ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= (int) $r['id_rol'] ?>" <?= (int) $u['id_rol'] === (int) $r['id_rol'] ? 'selected' : '' ?>><?= e($r['rol_nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($es_propio): ?>
                                <small class="form-text text-muted">No puede cambiar su propio rol.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="main.php?module=user" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

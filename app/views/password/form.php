<?php
// Formulario para cambio de contraseña
defined('APP') or exit;

encabezado('Cambiar contraseña', 'fas fa-key', ['Cambiar contraseña' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="row">
            <div class="col-lg-6 col-md-8">
                <div class="card card-primary">
                    <div class="card-header"><h3 class="card-title">Cambio de contraseña de <?= e($_SESSION['usuario'] ?? '') ?></h3></div>
                    <form action="modules/password/procesar.php" method="post" autocomplete="off">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="old_pass">Contraseña actual</label>
                                <input type="password" class="form-control" id="old_pass" name="old_pass" required>
                            </div>
                            <div class="form-group">
                                <label for="new_pass">Contraseña nueva</label>
                                <input type="password" class="form-control" id="new_pass" name="new_pass"
                                       minlength="8" maxlength="72" required>
                                <small class="form-text text-muted">Mínimo 8 caracteres.</small>
                            </div>
                            <div class="form-group">
                                <label for="retype_pass">Repetir contraseña nueva</label>
                                <input type="password" class="form-control" id="retype_pass" name="retype_pass"
                                       minlength="8" maxlength="72" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                            <a href="main.php?module=start" class="btn btn-default">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

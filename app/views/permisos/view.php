<?php
// Permisos por rol (tablas roles, menu, permisos)
defined('APP') or exit;

$pdo    = db();
$roles  = $pdo->query('SELECT id_rol, rol_nombre, descripcion FROM roles ORDER BY id_rol')->fetchAll();
$id_rol = int_param($_GET, 'rol') ?: (int) ($roles[1]['id_rol'] ?? $roles[0]['id_rol']);

$rol = null;
foreach ($roles as $r) {
    if ((int) $r['id_rol'] === $id_rol) {
        $rol = $r;
    }
}
if ($rol === null) {
    $rol    = $roles[0];
    $id_rol = (int) $rol['id_rol'];
}
$es_admin = $id_rol === ROL_SUPER_ADMIN; // el Super Admin conserva siempre todos los permisos

// Opciones del menú que abren un módulo, con su grupo
$opciones = $pdo->query('SELECT m.id_menu, m.titulo, m.modulo, COALESCE(g.titulo, \'Primer nivel\') AS grupo
                         FROM menu m LEFT JOIN menu g ON g.id_menu = m.id_padre
                         WHERE m.modulo IS NOT NULL
                         ORDER BY COALESCE(g.orden, m.orden), m.orden')->fetchAll();

$st = $pdo->prepare('SELECT id_menu FROM permisos WHERE id_rol = ?');
$st->execute([$id_rol]);
$asignados = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));

encabezado('Permisos por rol', 'fas fa-user-shield', ['Administración' => null, 'Permisos por rol' => null]);
?>
<section class="content">
    <div class="container-fluid">
        <?= flash_show() ?>
        <div class="card card-primary card-outline">
            <div class="card-body">
                <form method="get" action="main.php" class="form-row align-items-end">
                    <input type="hidden" name="module" value="permisos">
                    <div class="col-md-4 col-sm-8 mb-2">
                        <label for="rol">Rol</label>
                        <select name="rol" id="rol" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id_rol'] ?>" <?= (int) $r['id_rol'] === $id_rol ? 'selected' : '' ?>><?= e($r['rol_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i> Ver permisos</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Opciones del menú para: <b><?= e($rol['rol_nombre']) ?></b>
                    <small class="text-muted ml-2"><?= e($rol['descripcion']) ?></small></h3>
            </div>
            <form action="modules/permisos/procesar.php" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_rol" value="<?= $id_rol ?>">
                <div class="card-body">
                    <?php if ($es_admin): ?>
                        <div class="alert alert-info py-2">El rol Super Admin tiene siempre acceso a todas las opciones y no se puede modificar.</div>
                    <?php endif; ?>
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                        <tr>
                            <th>Grupo del menú</th>
                            <th>Opción</th>
                            <th>Módulo</th>
                            <th class="text-center" style="width:110px;">Permitido</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($opciones as $o):
                            $id_menu = (int) $o['id_menu'];
                            $fijo    = $es_admin || $o['modulo'] === 'start'; // Inicio es obligatorio para todos
                            $marcado = $fijo || in_array($id_menu, $asignados, true);
                        ?>
                            <tr>
                                <td><?= e($o['grupo']) ?></td>
                                <td><?= e($o['titulo']) ?></td>
                                <td><code><?= e($o['modulo']) ?></code></td>
                                <td class="text-center">
                                    <input type="checkbox" name="menus[]" value="<?= $id_menu ?>" style="width:18px;height:18px;"
                                        <?= $marcado ? 'checked' : '' ?> <?= $fijo ? 'disabled' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!$es_admin): ?>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar permisos</button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

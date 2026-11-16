<?php
// Menú lateral: se arma desde las tablas menu y permisos
defined('APP') or exit;

$items = db()->query('SELECT id_menu, id_padre, titulo, icono, modulo FROM menu ORDER BY orden, id_menu')->fetchAll();

// Opciones principales y opciones de cada grupo
$raiz  = [];
$hijos = [];
foreach ($items as $it) {
    if ($it['id_padre'] === null) {
        $raiz[] = $it;
    } else {
        $hijos[(int) $it['id_padre']][] = $it;
    }
}

// Enlace de una opción del menú
function menu_enlace(array $it, string $modulo_actual, bool $hijo): string
{
    $activo = $it['modulo'] === $modulo_actual ? ' active' : '';
    $icono  = $hijo ? 'far fa-circle nav-icon' : 'nav-icon ' . $it['icono'];
    return '<li class="nav-item"><a href="main.php?module=' . e($it['modulo']) . '" class="nav-link' . $activo . '">'
         . '<i class="' . e($icono) . '"></i><p>' . e($it['titulo']) . '</p></a></li>';
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="main.php?module=start" class="brand-link">
        <img src="assets/img/logo.png" alt="Logo" class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light"><b><?= e(APP_SHORT) ?></b> Compras</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= e(foto_url($_SESSION['foto'] ?? null)) ?>" class="img-circle elevation-2" alt="Foto">
            </div>
            <div class="info">
                <a href="main.php?module=perfil" class="d-block"><?= e($_SESSION['nombre']) ?></a>
                <small class="text-muted"><?= e($_SESSION['rol']) ?></small>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">MENÚ</li>
                <?php foreach ($raiz as $it): ?>
                    <?php if ($it['modulo'] !== null): ?>
                        <?php if (can($it['modulo'])) echo menu_enlace($it, $modulo_actual, false); ?>
                    <?php else:
                        // El grupo se muestra si tiene alguna opción permitida
                        $visibles = array_filter($hijos[(int) $it['id_menu']] ?? [], static function ($h) {
                            return can($h['modulo']);
                        });
                        if (!$visibles) continue;
                        $abierto = in_array($modulo_actual, array_column($visibles, 'modulo'), true);
                    ?>
                        <li class="nav-item<?= $abierto ? ' menu-open' : '' ?>">
                            <a href="#" class="nav-link<?= $abierto ? ' active' : '' ?>">
                                <i class="nav-icon <?= e($it['icono']) ?>"></i>
                                <p><?= e($it['titulo']) ?> <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php foreach ($visibles as $h) echo menu_enlace($h, $modulo_actual, true); ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>

                <li class="nav-item">
                    <a href="#" class="nav-link" data-toggle="modal" data-target="#modalSalir">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i><p>Salir</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

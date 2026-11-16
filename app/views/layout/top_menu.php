<?php
// Menú superior (Top menú)
defined('APP') or exit;
?>
<nav class="main-header navbar navbar-expand navbar-dark navbar-primary">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" title="Mostrar/ocultar menú">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="main.php?module=start" class="nav-link">Inicio</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="<?= e(foto_url($_SESSION['foto'] ?? null)) ?>" class="user-image img-circle elevation-1" alt="Foto">
                <span class="d-none d-md-inline"><?= e($_SESSION['nombre']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <li class="user-header bg-primary">
                    <img src="<?= e(foto_url($_SESSION['foto'] ?? null)) ?>" class="img-circle elevation-2" alt="Foto">
                    <p>
                        <?= e($_SESSION['nombre']) ?>
                        <small><?= e($_SESSION['rol']) ?></small>
                    </p>
                </li>
                <li class="user-footer">
                    <a href="main.php?module=perfil" class="btn btn-default btn-flat"><i class="fas fa-user"></i> Perfil</a>
                    <a href="#" class="btn btn-default btn-flat float-right" data-toggle="modal" data-target="#modalSalir">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<?php if(isset($_SESSION['admin']) && $_SESSION['admin'] === true) { ?>
    <header class="admin-topbar">
        <div>
            <p class="admin-topbar__label">Bienvenido</p>
            <h2><?php echo s($nombre ?? $_SESSION['nombre'] ?? 'Administrador'); ?></h2>
        </div>

        <div class="admin-topbar__actions">
            <span class="status-badge status-badge--success">
                <?php echo s($_SESSION['rol'] ?? 'Administrador'); ?>
            </span>
            <a class="btn btn--dark" href="/logout">Cerrar sesión</a>
        </div>
    </header>
<?php } else { ?>
    <div class="client-topbar">
        <div>
            <p>Hola,</p>
            <strong><?php echo s($nombre ?? $_SESSION['nombre'] ?? 'Cliente'); ?></strong>
        </div>

        <div class="client-topbar__actions">
            <a href="/cita" class="btn btn--ghost">Crear cita</a>
            <a href="/logout" class="btn btn--dark">Cerrar sesión</a>
        </div>
    </div>
<?php } ?>

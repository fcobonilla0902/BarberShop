<?php
    $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
    $bodyClass = $esAdmin ? 'layout-admin' : 'layout-public';
    $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';

    $activeClass = function($paths) use ($currentPath) {
        if(!is_array($paths)) {
            $paths = [$paths];
        }

        return in_array($currentPath, $paths, true) ? ' admin-nav__link--active' : '';
    };
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/build/css/app.css">
    <link rel="icon" href="/build/img/logo.png" type="image/png">
</head>
<body class="<?php echo $bodyClass; ?>">

    <?php if($esAdmin) { ?>
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <a href="/admin" class="admin-brand">
                    <span class="admin-brand__icon">✂</span>
                    <span>
                        <strong>BarberShop</strong>
                        <small>Admin Panel</small>
                    </span>
                </a>

                <nav class="admin-nav">
                    <a href="/admin" class="admin-nav__link<?php echo $activeClass('/admin'); ?>"><span>▦</span> Dashboard</a>
                    <a href="/admin/citas" class="admin-nav__link<?php echo $activeClass('/admin/citas'); ?>"><span>🗓</span> Citas</a>
                    <a href="/servicios" class="admin-nav__link<?php echo $activeClass(['/servicios', '/servicios/crear', '/servicios/actualizar']); ?>"><span>✂</span> Servicios</a>
                    <a href="/productos" class="admin-nav__link<?php echo $activeClass(['/productos', '/productos/crear']); ?>"><span>🧴</span> Productos</a>
                    <a href="/productos/lotes" class="admin-nav__link<?php echo $activeClass(['/productos/lotes', '/productos/lote']); ?>"><span>📦</span> Inventario</a>
                    <a href="/ventas" class="admin-nav__link<?php echo $activeClass(['/ventas', '/ventas/historial', '/ventas/ticket']); ?>"><span>🧾</span> Ventas</a>
                    <a href="/configuracion" class="admin-nav__link<?php echo $activeClass('/configuracion'); ?>"><span>⚙</span> Configuración</a>
                </nav>

                <div class="admin-sidebar__footer">
                    <p>Sesión activa</p>
                    <strong><?php echo s($_SESSION['rol'] ?? 'Administrador'); ?></strong>
                </div>
            </aside>

            <main class="admin-main">
                <?php echo $contenido; ?>
            </main>
        </div>
    <?php } else { ?>
        <div class="public-shell">
            <section class="public-hero">
                <div class="public-hero__overlay"></div>
                <div class="public-hero__content">
                    <div class="brand-pill">
                        <span>✂</span>
                        <strong>BarberShop</strong>
                    </div>

                    <div>
                        <p class="eyebrow">Sistema de citas e inventario</p>
                        <h2>Administra tu barbería sin complicarte.</h2>
                        <p>Agenda servicios, controla clientes y prepara la operación diaria desde una interfaz clara.</p>
                    </div>
                </div>
            </section>

            <main class="public-content">
                <div class="public-card">
                    <?php echo $contenido; ?>
                </div>
            </main>
        </div>
    <?php } ?>

    <?php echo $script ?? ''; ?>
</body>
</html>

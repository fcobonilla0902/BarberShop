<?php
    $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
    $bodyClass = $esAdmin ? 'layout-admin' : 'layout-public';
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
                    <a href="/admin" class="admin-nav__link">
                        <span>▦</span> Dashboard
                    </a>
                    <a href="/admin" class="admin-nav__link">
                        <span>🗓</span> Citas
                    </a>
                    <a href="/servicios" class="admin-nav__link">
                        <span>✂</span> Servicios
                    </a>
                    <a href="/productos" class="admin-nav__link">
                        <span>🧴</span> Productos
                    </a>
                    <a href="/productos" class="admin-nav__link">
                        <span>📦</span> Inventario
                    </a>
                    <a href="#" class="admin-nav__link admin-nav__link--disabled">
                        <span>🧾</span> Ventas
                    </a>
                    <a href="#" class="admin-nav__link admin-nav__link--disabled">
                        <span>⚙</span> Configuración
                    </a>
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

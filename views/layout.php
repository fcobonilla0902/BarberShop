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
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberShop</title>
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('barbershop-theme') || 'dark';
                if(theme !== 'light' && theme !== 'dark') {
                    theme = 'dark';
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch(error) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/build/css/app.css">
    <link rel="stylesheet" href="/build/css/theme-mode.css">
    <link rel="icon" href="/build/img/logo.png" type="image/png">
    <script src="/build/js/theme.js" defer></script>
</head>
<body class="<?php echo $bodyClass; ?>">

    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Cambiar tema" aria-pressed="false">
        <span class="theme-toggle__icon" data-theme-icon>🌙</span>
        <span class="theme-toggle__text" data-theme-label>Modo oscuro</span>
    </button>

    <?php if($esAdmin) { ?>
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <a href="/admin" class="admin-brand">
                    <span class="admin-brand__icon">
                        <img src="/build/img/logo.png" alt="BarberShop" onerror="this.style.display='none'; this.parentElement.classList.add('admin-brand__icon--fallback');">
                    </span>
                    <span>
                        <strong>BarberShop</strong>
                        <small>Panel administrativo</small>
                    </span>
                </a>

                <nav class="admin-nav">
                    <a href="/admin" class="admin-nav__link<?php echo $activeClass('/admin'); ?>"><span>▦</span> Dashboard</a>
                    <a href="/admin/citas" class="admin-nav__link<?php echo $activeClass(['/admin/citas', '/admin/citas/crear']); ?>"><span>🗓</span> Citas</a>
                    <a href="/clientes" class="admin-nav__link<?php echo $activeClass('/clientes'); ?>"><span>👥</span> Clientes</a>
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
                        <span class="brand-pill__logo">
                            <img src="/build/img/logo.png" alt="BarberShop" onerror="this.style.display='none'; this.parentElement.classList.add('brand-pill__logo--fallback');">
                        </span>
                        <strong>BarberShop</strong>
                    </div>

                    <div>
                        <p class="eyebrow">Citas, ventas e inventario</p>
                        <h2>Gestiona tu barbería desde un solo lugar.</h2>
                        <p>Agenda servicios, controla inventario por lotes y registra ventas con tickets claros.</p>
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

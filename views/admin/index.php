<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<section class="admin-page">
    <div class="page-heading">
        <div>
            <span class="screen-tag">Panel general</span>
            <h1>Dashboard</h1>
            <p>Resumen de citas, ventas, clientes e inventario.</p>
        </div>

        <form method="GET" action="/admin" class="date-filter">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo s($fecha); ?>">
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>
    </div>

    <div class="kpi-grid">
        <article class="kpi-card">
            <span>Citas de hoy</span>
            <strong><?php echo (int)$metricas['citas_hoy']; ?></strong>
            <small>Agenda del día seleccionado</small>
        </article>

        <article class="kpi-card">
            <span>Ventas del día</span>
            <strong>$<?php echo number_format($metricas['ventas_dia'], 2); ?></strong>
            <small>MXN con IVA incluido</small>
        </article>

        <article class="kpi-card">
            <span>Bajo stock</span>
            <strong><?php echo (int)$metricas['productos_bajo_stock']; ?></strong>
            <small>Productos por revisar</small>
        </article>

        <article class="kpi-card">
            <span>Clientes</span>
            <strong><?php echo (int)$metricas['clientes_registrados']; ?></strong>
            <small>Registrados en sistema</small>
        </article>
    </div>

    <div class="panel-grid">
        <section class="data-card">
            <div class="data-card__header">
                <div>
                    <h2>Próximas citas</h2>
                    <p>Citas programadas para la fecha seleccionada.</p>
                </div>
                <span class="status-badge">Agenda</span>
            </div>

            <div class="table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Servicios</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($proximasCitas)) { ?>
                            <tr>
                                <td colspan="4">No hay citas para esta fecha.</td>
                            </tr>
                        <?php } ?>

                        <?php foreach($proximasCitas as $cita) { ?>
                            <tr>
                                <td><?php echo s(substr($cita['hora_inicio'], 0, 5)); ?></td>
                                <td><?php echo s($cita['cliente']); ?></td>
                                <td><?php echo s($cita['servicios']); ?></td>
                                <td>
                                    <span class="status-badge status-badge--success">
                                        <?php echo s($cita['estado']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="data-card">
            <div class="data-card__header">
                <div>
                    <h2>Inventario bajo</h2>
                    <p>Productos que llegaron o bajaron del stock mínimo.</p>
                </div>
                <span class="status-badge status-badge--warning">Stock</span>
            </div>

            <div class="inventory-list">
                <?php if(empty($productosBajoStock)) { ?>
                    <div class="empty-state">
                        No hay productos bajo stock.
                    </div>
                <?php } ?>

                <?php foreach($productosBajoStock as $producto) { ?>
                    <article class="stock-card">
                        <div>
                            <strong><?php echo s($producto['nombre']); ?></strong>
                            <span>Stock mínimo: <?php echo (int)$producto['stock_minimo']; ?></span>
                        </div>

                        <span class="stock-pill">
                            <?php echo (int)$producto['stock_total']; ?> disp.
                        </span>
                    </article>
                <?php } ?>
            </div>
        </section>
    </div>
</section>

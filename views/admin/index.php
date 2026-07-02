<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<section class="admin-page dashboard-page">
    <div class="page-heading dashboard-heading">
        <div>
            <span class="screen-tag">Panel general</span>
            <h1>Dashboard</h1>
            <p>Resumen operativo de citas, ventas, clientes e inventario.</p>
        </div>

        <form method="GET" action="/admin" class="date-filter">
            <div>
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo s($fecha); ?>">
            </div>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>
    </div>

    <div class="kpi-grid dashboard-kpis">
        <article class="kpi-card">
            <span>Citas del día</span>
            <strong><?php echo (int)$metricas['citas_hoy']; ?></strong>
            <small>Agendadas para la fecha seleccionada</small>
        </article>

        <article class="kpi-card kpi-card--money">
            <span>Ventas del día</span>
            <strong>$<?php echo number_format((float)$metricas['ventas_dia'], 2); ?></strong>
            <small><?php echo (int)$metricas['tickets_dia']; ?> ticket(s) registrados</small>
        </article>

        <article class="kpi-card">
            <span>Productos bajo stock</span>
            <strong><?php echo (int)$metricas['productos_bajo_stock']; ?></strong>
            <small>Requieren revisión de inventario</small>
        </article>

        <article class="kpi-card">
            <span>Clientes registrados</span>
            <strong><?php echo (int)$metricas['clientes_registrados']; ?></strong>
            <small>Total de clientes en sistema</small>
        </article>
    </div>

    <div class="dashboard-secondary-kpi">
        <article>
            <span>Utilidad estimada del día</span>
            <strong>$<?php echo number_format((float)$metricas['utilidad_dia'], 2); ?> MXN</strong>
        </article>

        <article>
            <span>Fecha consultada</span>
            <strong><?php echo date('d/m/Y', strtotime($fecha)); ?></strong>
        </article>
    </div>

    <div class="panel-grid dashboard-grid">
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
                            <th>Barbero</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if(empty($proximasCitas)) { ?>
                            <tr>
                                <td colspan="6">No hay citas para esta fecha.</td>
                            </tr>
                        <?php } ?>

                        <?php foreach($proximasCitas as $cita) { ?>
                            <?php
                                $estadoClass = 'status-badge';
                                if($cita['estado'] === 'Atendida') {
                                    $estadoClass .= ' status-badge--success';
                                } elseif($cita['estado'] === 'Cancelada') {
                                    $estadoClass .= ' status-badge--danger';
                                } elseif($cita['estado'] === 'Cancelación solicitada') {
                                    $estadoClass .= ' status-badge--warning';
                                } else {
                                    $estadoClass .= ' status-badge--info';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo s(substr($cita['hora_inicio'], 0, 5)); ?></strong>
                                    <br>
                                    <small><?php echo s(substr($cita['hora_fin'], 0, 5)); ?></small>
                                </td>
                                <td><?php echo s($cita['cliente']); ?></td>
                                <td><?php echo s($cita['servicios']); ?></td>
                                <td><?php echo s($cita['barbero']); ?></td>
                                <td>$<?php echo number_format((float)$cita['total'], 2); ?></td>
                                <td>
                                    <span class="<?php echo $estadoClass; ?>">
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
                    <h2>Productos bajos</h2>
                    <p>Stock calculado desde lotes.</p>
                </div>
                <span class="status-badge status-badge--warning">Stock</span>
            </div>

            <div class="inventory-list">
                <?php if(empty($productosBajoStock)) { ?>
                    <div class="empty-state">No hay productos bajo stock.</div>
                <?php } ?>

                <?php foreach($productosBajoStock as $producto) { ?>
                    <article class="stock-card dashboard-stock-card">
                        <div>
                            <strong><?php echo s($producto['nombre']); ?></strong>
                            <span>Mínimo: <?php echo (int)$producto['stock_minimo']; ?> · Precio: $<?php echo number_format((float)$producto['precio_final'], 2); ?></span>
                        </div>

                        <?php if((int)$producto['stock_total'] <= 0) { ?>
                            <span class="stock-pill stock-pill--danger">Agotado</span>
                        <?php } else { ?>
                            <span class="stock-pill"><?php echo (int)$producto['stock_total']; ?> disp.</span>
                        <?php } ?>
                    </article>
                <?php } ?>
            </div>
        </section>
    </div>

    <div class="panel-grid dashboard-grid dashboard-grid--bottom">
        <section class="data-card">
            <div class="data-card__header">
                <div>
                    <h2>Ventas recientes</h2>
                    <p>Tickets registrados en la fecha seleccionada.</p>
                </div>
                <span class="status-badge status-badge--success">Ventas</span>
            </div>

            <div class="table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Hora</th>
                            <th>Método</th>
                            <th>Total</th>
                            <th>Utilidad</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if(empty($ventasRecientes)) { ?>
                            <tr>
                                <td colspan="5">No hay ventas para esta fecha.</td>
                            </tr>
                        <?php } ?>

                        <?php foreach($ventasRecientes as $venta) { ?>
                            <tr>
                                <td><?php echo s($venta['folio']); ?></td>
                                <td><?php echo s(date('H:i', strtotime($venta['fecha_venta']))); ?></td>
                                <td><?php echo s($venta['metodo_pago']); ?></td>
                                <td>$<?php echo number_format((float)$venta['total_con_iva'], 2); ?></td>
                                <td>
                                    <strong>$<?php echo number_format((float)$venta['utilidad_total'], 2); ?></strong>
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
                    <h2>Próximos a caducar</h2>
                    <p>Lotes con caducidad dentro de 45 días.</p>
                </div>
                <span class="status-badge status-badge--warning">Caducidad</span>
            </div>

            <div class="inventory-list">
                <?php if(empty($productosCaducar)) { ?>
                    <div class="empty-state">No hay lotes próximos a caducar.</div>
                <?php } ?>

                <?php foreach($productosCaducar as $lote) { ?>
                    <article class="stock-card dashboard-stock-card">
                        <div>
                            <strong><?php echo s($lote['nombre']); ?></strong>
                            <span>Lote <?php echo s($lote['codigo_lote']); ?> · <?php echo (int)$lote['cantidad_actual']; ?> piezas</span>
                        </div>

                        <span class="stock-pill stock-pill--warning">
                            <?php echo date('d/m/Y', strtotime($lote['fecha_caducidad'])); ?>
                        </span>
                    </article>
                <?php } ?>
            </div>
        </section>
    </div>
</section>

<section class="admin-page ventas-history-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Historial</span>
            <h1>Historial de ventas</h1>
            <p>Consulta tickets registrados, totales, costos y utilidad.</p>
        </div>

        <div class="page-actions">
            <a href="/ventas" class="btn btn--primary">Nueva venta</a>
        </div>
    </div>

    <form method="GET" action="/ventas/historial" class="sales-history-filters">
        <div class="campo campo--stack">
            <label for="fecha_inicio">Desde</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo s($fechaInicio ?? date('Y-m-01')); ?>">
        </div>

        <div class="campo campo--stack">
            <label for="fecha_fin">Hasta</label>
            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo s($fechaFin ?? date('Y-m-d')); ?>">
        </div>

        <div class="campo campo--stack">
            <label for="q">Buscar</label>
            <input type="text" id="q" name="q" placeholder="Folio, método o colaborador" value="<?php echo s($q ?? ''); ?>">
        </div>

        <div class="sales-history-filters__actions">
            <button type="submit" class="btn btn--primary">Filtrar</button>
            <a href="/ventas/historial" class="btn btn--soft">Limpiar</a>
        </div>
    </form>

    <div class="kpi-grid sales-history-kpis">
        <article class="kpi-card">
            <span>Tickets</span>
            <strong><?php echo (int)($metricas['tickets'] ?? 0); ?></strong>
            <small>Ventas encontradas</small>
        </article>

        <article class="kpi-card kpi-card--money">
            <span>Total vendido</span>
            <strong>$<?php echo number_format((float)($metricas['total'] ?? 0), 2); ?></strong>
            <small>Con IVA incluido</small>
        </article>

        <article class="kpi-card">
            <span>Costo total</span>
            <strong>$<?php echo number_format((float)($metricas['costo'] ?? 0), 2); ?></strong>
            <small>Costo sin IVA</small>
        </article>

        <article class="kpi-card">
            <span>Utilidad</span>
            <strong>$<?php echo number_format((float)($metricas['utilidad'] ?? 0), 2); ?></strong>
            <small>Venta menos costo</small>
        </article>
    </div>

    <section class="data-card">
        <div class="data-card__header">
            <div>
                <h2>Tickets registrados</h2>
                <p>Listado de ventas con método de pago, total, costo y utilidad.</p>
            </div>

            <span class="status-badge">Máx. 150</span>
        </div>

        <div class="table-wrap">
            <table class="table-modern sales-history-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Método de pago</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Costo</th>
                        <th>Utilidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(empty($ventas)) { ?>
                        <tr>
                            <td colspan="8">No hay ventas con esos filtros.</td>
                        </tr>
                    <?php } ?>

                    <?php foreach(($ventas ?? []) as $venta) { ?>
                        <?php
                            $total = (float)$venta['total_con_iva'];
                            $costo = (float)$venta['costo_total_sin_iva'];
                            $utilidad = (float)$venta['utilidad_total'];
                            $margen = $total > 0 ? (($utilidad / $total) * 100) : 0;
                        ?>

                        <tr>
                            <td>
                                <strong><?php echo s($venta['folio']); ?></strong>
                                <br>
                                <small>Cobró: <?php echo s($venta['colaborador']); ?></small>
                            </td>

                            <td>
                                <?php echo s(date('d/m/Y', strtotime($venta['fecha_venta']))); ?>
                                <br>
                                <small><?php echo s(date('H:i', strtotime($venta['fecha_venta']))); ?></small>
                            </td>

                            <td>
                                <span class="status-badge status-badge--success">
                                    <?php echo s($venta['metodo_pago']); ?>
                                </span>
                            </td>

                            <td>
                                <small>
                                    <?php echo (int)$venta['servicios_count']; ?> servicio(s) ·
                                    <?php echo (int)$venta['productos_count']; ?> producto(s)
                                </small>
                            </td>

                            <td>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </td>

                            <td>$<?php echo number_format($costo, 2); ?></td>

                            <td>
                                <strong>$<?php echo number_format($utilidad, 2); ?></strong>
                                <br>
                                <small><?php echo number_format($margen, 1); ?>% margen</small>
                            </td>

                            <td>
                                <a href="/ventas/ticket?id=<?php echo (int)$venta['id']; ?>" class="btn btn--soft">
                                    Ver ticket
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</section>

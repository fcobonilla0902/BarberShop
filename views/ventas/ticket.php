<section class="admin-page ticket-detail-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Ticket</span>
            <h1><?php echo s($ticket['venta']['folio']); ?></h1>
            <p>Detalle de venta registrada.</p>
        </div>

        <div class="page-actions">
            <a href="/ventas/historial" class="btn btn--soft">Volver al historial</a>
            <button class="btn btn--primary" onclick="window.print()">Imprimir ticket</button>
        </div>
    </div>

    <div class="ticket-detail-layout">
        <section class="data-card ticket-detail-summary">
            <div class="data-card__header">
                <div>
                    <h2>Resumen de venta</h2>
                    <p><?php echo s($ticket['venta']['fecha_venta']); ?></p>
                </div>

                <span class="status-badge status-badge--success">
                    <?php echo s($ticket['venta']['metodo_pago']); ?>
                </span>
            </div>

            <div class="ticket-detail-grid">
                <div>
                    <span>Sucursal</span>
                    <strong><?php echo s($ticket['venta']['sucursal']); ?></strong>
                </div>

                <div>
                    <span>Cobró</span>
                    <strong><?php echo s($ticket['venta']['colaborador']); ?></strong>
                </div>

                <div>
                    <span>Subtotal</span>
                    <strong>$<?php echo number_format((float)$ticket['venta']['subtotal_sin_iva'], 2); ?></strong>
                </div>

                <div>
                    <span>IVA</span>
                    <strong>$<?php echo number_format((float)$ticket['venta']['iva_total'], 2); ?></strong>
                </div>

                <div>
                    <span>Total</span>
                    <strong>$<?php echo number_format((float)$ticket['venta']['total_con_iva'], 2); ?></strong>
                </div>

                <div>
                    <span>Costo</span>
                    <strong>$<?php echo number_format((float)$ticket['venta']['costo_total_sin_iva'], 2); ?></strong>
                </div>

                <div>
                    <span>Utilidad</span>
                    <strong>$<?php echo number_format((float)$ticket['venta']['utilidad_total'], 2); ?></strong>
                </div>
            </div>
        </section>

        <section class="data-card">
            <div class="data-card__header">
                <div>
                    <h2>Detalle de artículos</h2>
                    <p>Servicios y productos incluidos en el ticket.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Cantidad</th>
                            <th>Lote</th>
                            <th>Precio sin IVA</th>
                            <th>IVA</th>
                            <th>Total</th>
                            <th>Utilidad</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($ticket['items'] as $item) { ?>
                            <tr>
                                <td><?php echo s($item['tipo']); ?></td>
                                <td><strong><?php echo s($item['nombre']); ?></strong></td>
                                <td><?php echo (int)$item['cantidad']; ?></td>
                                <td><?php echo s($item['lote'] ?: '-'); ?></td>
                                <td>$<?php echo number_format((float)$item['precio_unitario_sin_iva'], 2); ?></td>
                                <td>$<?php echo number_format((float)$item['iva_monto'], 2); ?></td>
                                <td><strong>$<?php echo number_format((float)$item['total_linea_con_iva'], 2); ?></strong></td>
                                <td>$<?php echo number_format((float)$item['utilidad_linea'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="data-card ticket-final ticket-detail-print">
        <div class="data-card__header">
            <div>
                <h2>Vista de ticket</h2>
                <p>Formato simple para impresión.</p>
            </div>
        </div>

        <div class="ticket-print">
            <div class="ticket-print__brand">
                <h3><?php echo s($ticket['venta']['sucursal']); ?></h3>
                <p>Ticket <?php echo s($ticket['venta']['folio']); ?></p>
                <p><?php echo s($ticket['venta']['fecha_venta']); ?></p>
                <p>Cobró: <?php echo s($ticket['venta']['colaborador']); ?></p>
            </div>

            <div class="ticket-print__items">
                <?php foreach($ticket['items'] as $item) { ?>
                    <div>
                        <span><?php echo s($item['tipo']); ?> · <?php echo s($item['nombre']); ?> x<?php echo (int)$item['cantidad']; ?></span>
                        <strong>$<?php echo number_format((float)$item['total_linea_con_iva'], 2); ?></strong>
                    </div>
                <?php } ?>
            </div>

            <div class="ticket-print__totals">
                <div><span>Subtotal</span><strong>$<?php echo number_format((float)$ticket['venta']['subtotal_sin_iva'], 2); ?></strong></div>
                <div><span>IVA</span><strong>$<?php echo number_format((float)$ticket['venta']['iva_total'], 2); ?></strong></div>
                <div><span>Total</span><strong>$<?php echo number_format((float)$ticket['venta']['total_con_iva'], 2); ?></strong></div>
                <div><span>Costo</span><strong>$<?php echo number_format((float)$ticket['venta']['costo_total_sin_iva'], 2); ?></strong></div>
                <div><span>Utilidad</span><strong>$<?php echo number_format((float)$ticket['venta']['utilidad_total'], 2); ?></strong></div>
            </div>
        </div>
    </section>
</section>

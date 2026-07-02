<section class="admin-page ventas-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Caja</span>
            <h1>Ventas</h1>
            <p>Selecciona servicios o productos, calcula IVA, descuenta inventario y genera ticket.</p>
        </div>
    </div>

    <?php if($mensaje) { ?>
        <div class="alerta <?php echo s($mensaje['tipo']); ?>">
            <?php echo s($mensaje['texto']); ?>
        </div>
    <?php } ?>

    <form method="POST" action="/ventas" id="venta-form" class="ventas-layout">
        <input type="hidden" name="items_json" id="items_json">

        <section class="ventas-main">
            <div class="data-card">
                <div class="data-card__header">
                    <div>
                        <h2>Servicios</h2>
                        <p>Agrega servicios al ticket.</p>
                    </div>
                    <span class="status-badge">Servicios</span>
                </div>

                <div class="sales-grid">
                    <?php foreach($servicios as $servicio) { ?>
                        <button
                            type="button"
                            class="sale-item"
                            data-tipo="servicio"
                            data-id="<?php echo (int)$servicio['id']; ?>"
                            data-nombre="<?php echo s($servicio['nombre']); ?>"
                            data-precio-sin-iva="<?php echo s($servicio['precio_base_sin_iva']); ?>"
                            data-iva="<?php echo s($servicio['iva_porcentaje']); ?>"
                            data-precio-final="<?php echo s($servicio['precio_final']); ?>"
                            data-stock="999"
                        >
                            <span class="service-chip">Servicio</span>
                            <strong><?php echo s($servicio['nombre']); ?></strong>
                            <small><?php echo (int)$servicio['duracion_minutos']; ?> min · IVA <?php echo number_format((float)$servicio['iva_porcentaje'], 2); ?>%</small>
                            <b>$<?php echo number_format((float)$servicio['precio_final'], 2); ?> MXN</b>
                        </button>
                    <?php } ?>
                </div>
            </div>

            <div class="data-card">
                <div class="data-card__header">
                    <div>
                        <h2>Productos</h2>
                        <p>Al vender producto se toma un lote disponible y se descuenta stock.</p>
                    </div>
                    <span class="status-badge status-badge--success">Inventario</span>
                </div>

                <div class="sales-grid">
                    <?php if(empty($productos)) { ?>
                        <div class="empty-state">No hay productos con stock disponible.</div>
                    <?php } ?>

                    <?php foreach($productos as $producto) { ?>
                        <button
                            type="button"
                            class="sale-item"
                            data-tipo="producto"
                            data-id="<?php echo (int)$producto['id']; ?>"
                            data-nombre="<?php echo s($producto['nombre']); ?>"
                            data-precio-sin-iva="<?php echo s($producto['precio_venta_sin_iva']); ?>"
                            data-iva="<?php echo s($producto['iva_porcentaje']); ?>"
                            data-precio-final="<?php echo s($producto['precio_final']); ?>"
                            data-stock="<?php echo (int)$producto['stock_total']; ?>"
                        >
                            <span class="service-chip">Producto</span>
                            <strong><?php echo s($producto['nombre']); ?></strong>
                            <small>Stock: <?php echo (int)$producto['stock_total']; ?> · IVA <?php echo number_format((float)$producto['iva_porcentaje'], 2); ?>%</small>
                            <b>$<?php echo number_format((float)$producto['precio_final'], 2); ?> MXN</b>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </section>

        <aside class="ticket-panel">
            <div class="ticket-panel__header">
                <span class="screen-tag">Ticket</span>
                <h2>Ticket actual</h2>
                <p><?php echo date('d/m/Y'); ?></p>
            </div>

            <div id="ticket-items" class="ticket-items">
                <div class="empty-state">Selecciona servicios o productos.</div>
            </div>

            <div class="campo campo--stack">
                <label for="metodo_pago_id">Método de pago</label>
                <select name="metodo_pago_id" id="metodo_pago_id" required>
                    <?php foreach($metodosPago as $metodo) { ?>
                        <option value="<?php echo (int)$metodo['id']; ?>">
                            <?php echo s($metodo['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="ticket-totals">
                <div><span>Subtotal sin IVA</span><strong id="ticket-subtotal">$0.00</strong></div>
                <div><span>IVA</span><strong id="ticket-iva">$0.00</strong></div>
                <div><span>Total con IVA</span><strong id="ticket-total">$0.00</strong></div>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Guardar venta</button>
        </aside>
    </form>

    <?php if($ticket) { ?>
        <section class="data-card ticket-final">
            <div class="data-card__header">
                <div>
                    <h2>Último ticket generado</h2>
                    <p>Folio <?php echo s($ticket['venta']['folio']); ?> · <?php echo s($ticket['venta']['metodo_pago']); ?></p>
                </div>

                <button class="btn btn--soft" onclick="window.print()">Imprimir</button>
            </div>

            <div class="ticket-print">
                <div class="ticket-print__brand">
                    <h3>BarberShop</h3>
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
    <?php } ?>
</section>

<script>
(function() {
    const items = [];
    const buttons = document.querySelectorAll('.sale-item');
    const ticketItems = document.querySelector('#ticket-items');
    const subtotalEl = document.querySelector('#ticket-subtotal');
    const ivaEl = document.querySelector('#ticket-iva');
    const totalEl = document.querySelector('#ticket-total');
    const inputJson = document.querySelector('#items_json');
    const form = document.querySelector('#venta-form');

    function money(value) {
        return '$' + Number(value).toFixed(2) + ' MXN';
    }

    function addItem(data) {
        const existente = items.find(item => item.tipo === data.tipo && item.id === data.id);

        if(existente) {
            if(data.tipo === 'producto' && existente.cantidad >= data.stock) {
                alert('No hay más stock disponible para este producto.');
                return;
            }

            existente.cantidad++;
        } else {
            items.push({
                tipo: data.tipo,
                id: data.id,
                nombre: data.nombre,
                precio_sin_iva: data.precioSinIva,
                iva_porcentaje: data.iva,
                precio_final: data.precioFinal,
                stock: data.stock,
                cantidad: 1
            });
        }

        render();
    }

    function removeItem(index) {
        items.splice(index, 1);
        render();
    }

    function render() {
        ticketItems.innerHTML = '';

        if(items.length === 0) {
            ticketItems.innerHTML = '<div class="empty-state">Selecciona servicios o productos.</div>';
        }

        let subtotal = 0;
        let ivaTotal = 0;
        let total = 0;

        items.forEach((item, index) => {
            const lineaSubtotal = item.precio_sin_iva * item.cantidad;
            const lineaIva = lineaSubtotal * (item.iva_porcentaje / 100);
            const lineaTotal = lineaSubtotal + lineaIva;

            subtotal += lineaSubtotal;
            ivaTotal += lineaIva;
            total += lineaTotal;

            const div = document.createElement('div');
            div.classList.add('ticket-item');
            div.innerHTML = `
                <div>
                    <strong>${item.nombre}</strong>
                    <span>${item.tipo} · Cantidad: ${item.cantidad}</span>
                </div>
                <div>
                    <b>${money(lineaTotal)}</b>
                    <button type="button" data-index="${index}">Quitar</button>
                </div>
            `;

            div.querySelector('button').addEventListener('click', () => removeItem(index));
            ticketItems.appendChild(div);
        });

        subtotalEl.textContent = money(subtotal);
        ivaEl.textContent = money(ivaTotal);
        totalEl.textContent = money(total);

        inputJson.value = JSON.stringify(items.map(item => ({
            tipo: item.tipo,
            id: item.id,
            cantidad: item.cantidad
        })));
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            addItem({
                tipo: button.dataset.tipo,
                id: parseInt(button.dataset.id),
                nombre: button.dataset.nombre,
                precioSinIva: parseFloat(button.dataset.precioSinIva),
                iva: parseFloat(button.dataset.iva),
                precioFinal: parseFloat(button.dataset.precioFinal),
                stock: parseInt(button.dataset.stock)
            });
        });
    });

    form.addEventListener('submit', function(e) {
        if(items.length === 0) {
            e.preventDefault();
            alert('Agrega al menos un producto o servicio al ticket.');
        }
    });

    render();
})();
</script>

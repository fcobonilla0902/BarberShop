<section class="admin-page admin-page--narrow">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Inventario</span>
            <h1>Agregar lote</h1>
            <p>Registra entrada de inventario por lote, con caducidad y costo real.</p>
        </div>

        <a href="/productos" class="btn btn--soft">Volver</a>
    </div>

    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <form action="/productos/lote" method="POST" class="formulario form-panel" id="form-lote-producto">
        <div class="form-grid">
            <div class="campo campo--stack form-grid__full">
                <label for="producto_id">Producto</label>
                <select id="producto_id" name="producto_id" required>
                    <option value="">-- Selecciona --</option>
                    <?php foreach($productos as $producto) { ?>
                        <option value="<?php echo (int)$producto->id; ?>" <?php echo ((string)($lote->producto_id ?? '') === (string)$producto->id) ? 'selected' : ''; ?>>
                            <?php echo s($producto->nombre); ?> — stock actual: <?php echo (int)$producto->stock_total; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="campo campo--stack">
                <label for="proveedor_id">Proveedor</label>
                <select id="proveedor_id" name="proveedor_id">
                    <option value="">Sin proveedor</option>
                    <?php foreach($proveedores as $proveedor) { ?>
                        <option value="<?php echo (int)$proveedor->id; ?>" <?php echo ((string)($lote->proveedor_id ?? '') === (string)$proveedor->id) ? 'selected' : ''; ?>>
                            <?php echo s($proveedor->nombre_comercial); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="campo campo--stack">
                <label for="estado_lote_id">Estado del lote</label>
                <select id="estado_lote_id" name="estado_lote_id" required>
                    <?php foreach($estados as $estado) { ?>
                        <option value="<?php echo (int)$estado->id; ?>" <?php echo ((string)($lote->estado_lote_id ?? '1') === (string)$estado->id) ? 'selected' : ''; ?>>
                            <?php echo s($estado->nombre); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="campo campo--stack">
                <label for="codigo_lote">Código de lote</label>
                <input type="text" id="codigo_lote" name="codigo_lote" placeholder="Ej. POM-2026-001" maxlength="45" required value="<?php echo s($lote->codigo_lote ?? ''); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="fecha_entrada">Fecha de entrada</label>
                <input type="date" id="fecha_entrada" name="fecha_entrada" max="<?php echo date('Y-m-d'); ?>" required value="<?php echo s($lote->fecha_entrada ?? date('Y-m-d')); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="fecha_caducidad">Fecha de caducidad</label>
                <input type="date" id="fecha_caducidad" name="fecha_caducidad" value="<?php echo s($lote->fecha_caducidad ?? ''); ?>">
                <small>Si el lote está caducado, no debe guardarse como Disponible.</small>
            </div>

            <div class="campo campo--stack">
                <label for="cantidad_inicial">Cantidad inicial</label>
                <input type="number" id="cantidad_inicial" name="cantidad_inicial" min="1" step="1" placeholder="10" required value="<?php echo s($lote->cantidad_inicial ?? ''); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="costo_unitario_sin_iva">Costo unitario sin IVA</label>
                <input type="number" id="costo_unitario_sin_iva" name="costo_unitario_sin_iva" min="0.01" step="0.01" placeholder="95.00" required value="<?php echo s($lote->costo_unitario_sin_iva ?? ''); ?>">
            </div>

            <div class="service-preview form-grid__full">
                <span>Nota</span>
                <strong>El stock se suma desde los lotes</strong>
                <small>Al guardar se crea también un movimiento de inventario tipo Entrada compra.</small>
            </div>
        </div>

        <div class="form-actions">
            <a href="/productos" class="btn btn--soft">Cancelar</a>
            <input type="submit" class="btn btn--primary" value="Guardar lote">
        </div>
    </form>
</section>

<script>
(function() {
    const form = document.querySelector('#form-lote-producto');
    const producto = document.querySelector('#producto_id');
    const estado = document.querySelector('#estado_lote_id');
    const codigo = document.querySelector('#codigo_lote');
    const fechaEntrada = document.querySelector('#fecha_entrada');
    const fechaCaducidad = document.querySelector('#fecha_caducidad');
    const cantidad = document.querySelector('#cantidad_inicial');
    const costo = document.querySelector('#costo_unitario_sin_iva');

    function hoyISO() {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        return hoy.toISOString().slice(0, 10);
    }

    function fechaValor(valor) {
        if(!valor) return null;
        const fecha = new Date(valor + 'T00:00:00');
        return Number.isNaN(fecha.getTime()) ? null : fecha;
    }

    function numero(input) {
        return Number.parseFloat(input?.value || '0');
    }

    function estadoTexto() {
        if(!estado || !estado.options[estado.selectedIndex]) return '';
        return estado.options[estado.selectedIndex].textContent.trim().toLowerCase();
    }

    function limpiarAlertas() {
        form?.querySelectorAll('.alerta-js').forEach(alerta => alerta.remove());
    }

    function mostrarErrores(errores) {
        limpiarAlertas();
        if(!form || errores.length === 0) return;

        const alerta = document.createElement('div');
        alerta.className = 'alerta error alerta-js';
        alerta.innerHTML = errores.map(error => `<div>${error}</div>`).join('');
        form.prepend(alerta);
        alerta.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if(codigo) {
        codigo.addEventListener('input', function() {
            codigo.value = codigo.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '');
        });
    }

    function validarLote(event) {
        const errores = [];
        const entrada = fechaValor(fechaEntrada.value);
        const caducidad = fechaValor(fechaCaducidad.value);
        const hoy = fechaValor(hoyISO());
        const cantidadValor = Number.parseInt(cantidad.value || '0', 10);
        const costoValor = numero(costo);
        const textoEstado = estadoTexto();

        if(!producto.value) {
            errores.push('Selecciona un producto para el lote.');
        }

        if(!estado.value) {
            errores.push('Selecciona el estado del lote.');
        }

        if(codigo.value.trim().length < 3) {
            errores.push('El código de lote debe tener al menos 3 caracteres.');
        }

        if(!entrada) {
            errores.push('La fecha de entrada no es válida.');
        } else if(entrada > hoy) {
            errores.push('La fecha de entrada no puede ser futura.');
        }

        if(caducidad && entrada && caducidad < entrada) {
            errores.push('La fecha de caducidad no puede ser anterior a la fecha de entrada.');
        }

        if(caducidad && caducidad < hoy && textoEstado.includes('disponible')) {
            errores.push('Un lote caducado no puede guardarse como Disponible para venta.');
        }

        if(!Number.isInteger(cantidadValor) || cantidadValor <= 0) {
            errores.push('La cantidad inicial debe ser un número entero mayor a 0.');
        }

        if(!Number.isFinite(costoValor) || costoValor <= 0) {
            errores.push('El costo unitario sin IVA debe ser mayor a 0.');
        }

        if(errores.length > 0) {
            event.preventDefault();
            mostrarErrores(errores);
        } else {
            limpiarAlertas();
        }
    }

    if(form) {
        form.addEventListener('submit', validarLote);
    }
})();
</script>

<div class="form-grid">
    <div class="campo campo--stack">
        <label for="nombre">Nombre del servicio</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            placeholder="Ej. Corte Adulto"
            maxlength="80"
            required
            value="<?php echo s($servicio->nombre ?? ''); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="categoria_servicio_id">Categoría</label>
        <select id="categoria_servicio_id" name="categoria_servicio_id" required>
            <option value="">-- Selecciona --</option>

            <?php foreach($categorias as $categoria) { ?>
                <option
                    value="<?php echo (int)$categoria->id; ?>"
                    <?php echo ((string)($servicio->categoria_servicio_id ?? '') === (string)$categoria->id) ? 'selected' : ''; ?>
                >
                    <?php echo s($categoria->nombre); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="campo campo--stack form-grid__full">
        <label for="descripcion">Descripción</label>
        <input
            type="text"
            id="descripcion"
            name="descripcion"
            placeholder="Descripción breve del servicio"
            maxlength="180"
            value="<?php echo s($servicio->descripcion ?? ''); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="duracion_minutos">Duración en minutos</label>
        <input
            type="number"
            id="duracion_minutos"
            name="duracion_minutos"
            step="15"
            min="15"
            max="300"
            placeholder="30"
            required
            value="<?php echo s($servicio->duracion_minutos ?? '30'); ?>"
        >
        <small>Usa múltiplos de 15 minutos. Máximo 300 minutos (5 horas).</small>
    </div>

    <div class="campo campo--stack">
        <label for="precio_base_sin_iva">Precio sin IVA</label>
        <input
            type="number"
            id="precio_base_sin_iva"
            name="precio_base_sin_iva"
            step="0.01"
            min="0.01"
            placeholder="129.31"
            required
            value="<?php echo s($servicio->precio_base_sin_iva ?? ''); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="iva_porcentaje">IVA %</label>
        <input
            type="number"
            id="iva_porcentaje"
            name="iva_porcentaje"
            step="0.01"
            min="0"
            max="100"
            placeholder="16.00"
            required
            value="<?php echo s($servicio->iva_porcentaje ?? '16.00'); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="costo_estimado_sin_iva">Costo estimado sin IVA</label>
        <input
            type="number"
            id="costo_estimado_sin_iva"
            name="costo_estimado_sin_iva"
            step="0.01"
            min="0.01"
            placeholder="40.00"
            required
            value="<?php echo s($servicio->costo_estimado_sin_iva ?? '0.00'); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="activo">Estado</label>
        <select id="activo" name="activo" required>
            <option value="1" <?php echo ((string)($servicio->activo ?? '1') === '1') ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo ((string)($servicio->activo ?? '1') === '0') ? 'selected' : ''; ?>>Inactivo</option>
        </select>
    </div>

    <div class="service-preview form-grid__full">
        <span>Vista rápida</span>
        <strong id="preview-total">$0.00 MXN</strong>
        <small id="preview-detail">Precio sin IVA + IVA</small>
    </div>
</div>

<script>
(function() {
    const form = document.currentScript.closest('form');
    const nombre = document.querySelector('#nombre');
    const categoria = document.querySelector('#categoria_servicio_id');
    const descripcion = document.querySelector('#descripcion');
    const duracion = document.querySelector('#duracion_minutos');
    const precio = document.querySelector('#precio_base_sin_iva');
    const iva = document.querySelector('#iva_porcentaje');
    const costo = document.querySelector('#costo_estimado_sin_iva');
    const total = document.querySelector('#preview-total');
    const detail = document.querySelector('#preview-detail');

    const textoPermitido = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s.,+&()\/-]/g;

    function limpiarTexto(input) {
        if(!input) return;
        input.addEventListener('input', function() {
            const limpio = input.value.replace(textoPermitido, '');
            if(input.value !== limpio) input.value = limpio;
        });
    }

    function numero(input) {
        return Number.parseFloat(input?.value || '0');
    }

    function actualizarPreview() {
        const precioSinIva = numero(precio);
        const ivaPorcentaje = numero(iva);
        const ivaMonto = precioSinIva * (ivaPorcentaje / 100);
        const precioFinal = precioSinIva + ivaMonto;

        total.textContent = '$' + precioFinal.toFixed(2) + ' MXN';
        detail.textContent = '$' + precioSinIva.toFixed(2) + ' sin IVA + $' + ivaMonto.toFixed(2) + ' IVA';
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

    function validarServicio(event) {
        const errores = [];
        const nombreValor = nombre.value.trim();
        const descripcionValor = descripcion.value.trim();
        const duracionValor = Number.parseInt(duracion.value || '0', 10);
        const precioValor = numero(precio);
        const ivaValor = numero(iva);
        const costoValor = numero(costo);

        if(nombreValor.length < 3) {
            errores.push('El nombre del servicio debe tener al menos 3 caracteres.');
        }

        if(!categoria.value) {
            errores.push('Selecciona una categoría para el servicio.');
        }

        if(descripcionValor.length > 180) {
            errores.push('La descripción no debe superar 180 caracteres.');
        }

        if(!Number.isInteger(duracionValor) || duracionValor < 15 || duracionValor > 300 || duracionValor % 15 !== 0) {
            errores.push('La duración debe ser múltiplo de 15, mínimo 15 y máximo 300 minutos.');
        }

        if(!Number.isFinite(precioValor) || precioValor <= 0) {
            errores.push('El precio sin IVA debe ser mayor a 0.');
        }

        if(!Number.isFinite(costoValor) || costoValor <= 0) {
            errores.push('El costo estimado sin IVA debe ser mayor a 0.');
        }

        if(precioValor > 0 && costoValor >= precioValor) {
            errores.push('El costo estimado debe ser menor que el precio sin IVA.');
        }

        if(!Number.isFinite(ivaValor) || ivaValor < 0 || ivaValor > 100) {
            errores.push('El IVA debe estar entre 0 y 100.');
        }

        if(errores.length > 0) {
            event.preventDefault();
            mostrarErrores(errores);
        } else {
            limpiarAlertas();
        }
    }

    limpiarTexto(nombre);
    limpiarTexto(descripcion);

    [precio, iva, costo].forEach(input => {
        if(input) input.addEventListener('input', actualizarPreview);
    });

    if(form) {
        form.addEventListener('submit', validarServicio);
    }

    actualizarPreview();
})();
</script>

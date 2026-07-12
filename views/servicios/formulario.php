<div class="form-grid">
    <div class="campo campo--stack">
        <label for="nombre">Nombre del servicio</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            placeholder="Ej. Corte Adulto"
            value="<?php echo s($servicio->nombre ?? ''); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="categoria_servicio_id">Categoría</label>
        <select id="categoria_servicio_id" name="categoria_servicio_id">
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
            placeholder="30"
            value="<?php echo s($servicio->duracion_minutos ?? '30'); ?>"
        >
        <small>Usa múltiplos de 15 minutos para respetar la agenda.</small>
    </div>

    <div class="campo campo--stack">
        <label for="precio_base_sin_iva">Precio sin IVA</label>
        <input
            type="number"
            id="precio_base_sin_iva"
            name="precio_base_sin_iva"
            step="0.01"
            min="0"
            placeholder="129.31"
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
            placeholder="16.00"
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
            min="0"
            placeholder="40.00"
            value="<?php echo s($servicio->costo_estimado_sin_iva ?? '0.00'); ?>"
        >
    </div>

    <div class="campo campo--stack">
        <label for="activo">Estado</label>
        <select id="activo" name="activo">
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
    const precio = document.querySelector('#precio_base_sin_iva');
    const iva = document.querySelector('#iva_porcentaje');
    const total = document.querySelector('#preview-total');
    const detail = document.querySelector('#preview-detail');

    function actualizarPreview() {
        const precioSinIva = parseFloat(precio.value || 0);
        const ivaPorcentaje = parseFloat(iva.value || 0);
        const ivaMonto = precioSinIva * (ivaPorcentaje / 100);
        const precioFinal = precioSinIva + ivaMonto;

        total.textContent = '$' + precioFinal.toFixed(2) + ' MXN';
        detail.textContent = '$' + precioSinIva.toFixed(2) + ' sin IVA + $' + ivaMonto.toFixed(2) + ' IVA';
    }

    if(precio && iva) {
        precio.addEventListener('input', actualizarPreview);
        iva.addEventListener('input', actualizarPreview);
        actualizarPreview();
    }
})();
</script>

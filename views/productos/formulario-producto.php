<div class="form-grid">
    <div class="campo campo--stack">
        <label for="nombre">Nombre del producto</label>
        <input type="text" id="nombre" name="nombre" placeholder="Ej. Pomada Modeladora" value="<?php echo s($producto->nombre ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="categoria_producto_id">Categoría</label>
        <select id="categoria_producto_id" name="categoria_producto_id">
            <option value="">-- Selecciona --</option>
            <?php foreach($categorias as $categoria) { ?>
                <option value="<?php echo (int)$categoria->id; ?>" <?php echo ((string)($producto->categoria_producto_id ?? '') === (string)$categoria->id) ? 'selected' : ''; ?>>
                    <?php echo s($categoria->nombre); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="campo campo--stack">
        <label for="marca">Marca</label>
        <input type="text" id="marca" name="marca" placeholder="Ej. Barber Pro" value="<?php echo s($producto->marca ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="codigo_barras">Código de barras</label>
        <input type="text" id="codigo_barras" name="codigo_barras" placeholder="Opcional" value="<?php echo s($producto->codigo_barras ?? ''); ?>">
    </div>

    <div class="campo campo--stack form-grid__full">
        <label for="descripcion">Descripción</label>
        <input type="text" id="descripcion" name="descripcion" placeholder="Descripción breve" value="<?php echo s($producto->descripcion ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="unidad_medida">Unidad de medida</label>
        <input type="text" id="unidad_medida" name="unidad_medida" placeholder="pieza" value="<?php echo s($producto->unidad_medida ?? 'pieza'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="stock_minimo">Stock mínimo</label>
        <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" placeholder="5" value="<?php echo s($producto->stock_minimo ?? '0'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="costo_referencia_sin_iva">Costo referencia sin IVA</label>
        <input type="number" id="costo_referencia_sin_iva" name="costo_referencia_sin_iva" min="0" step="0.01" placeholder="95.00" value="<?php echo s($producto->costo_referencia_sin_iva ?? '0.00'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="factor_utilidad">Factor utilidad</label>
        <input type="number" id="factor_utilidad" name="factor_utilidad" min="0.01" step="0.01" placeholder="1.30" value="<?php echo s($producto->factor_utilidad ?? '1.30'); ?>">
        <small>Ejemplo: costo 100 x factor 1.30 = precio sugerido 130 sin IVA.</small>
    </div>

    <div class="campo campo--stack">
        <label for="precio_venta_sin_iva">Precio venta sin IVA</label>
        <input type="number" id="precio_venta_sin_iva" name="precio_venta_sin_iva" min="0" step="0.01" placeholder="155.17" value="<?php echo s($producto->precio_venta_sin_iva ?? '0.00'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="iva_porcentaje">IVA %</label>
        <input type="number" id="iva_porcentaje" name="iva_porcentaje" min="0" step="0.01" placeholder="16.00" value="<?php echo s($producto->iva_porcentaje ?? '16.00'); ?>">
    </div>

    <div class="campo campo--stack form-grid__full">
        <label for="imagen_url">URL imagen</label>
        <input type="text" id="imagen_url" name="imagen_url" placeholder="/build/img/productos/producto.webp" value="<?php echo s($producto->imagen_url ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="activo">Estado</label>
        <select id="activo" name="activo">
            <option value="1" <?php echo ((string)($producto->activo ?? '1') === '1') ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo ((string)($producto->activo ?? '1') === '0') ? 'selected' : ''; ?>>Inactivo</option>
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
    const costo = document.querySelector('#costo_referencia_sin_iva');
    const factor = document.querySelector('#factor_utilidad');
    const precio = document.querySelector('#precio_venta_sin_iva');
    const iva = document.querySelector('#iva_porcentaje');
    const total = document.querySelector('#preview-total');
    const detail = document.querySelector('#preview-detail');

    function sugerirPrecio() {
        const costoValor = parseFloat(costo.value || 0);
        const factorValor = parseFloat(factor.value || 0);
        const precioSugerido = costoValor * factorValor;

        if(precioSugerido > 0 && (!precio.value || parseFloat(precio.value) === 0)) {
            precio.value = precioSugerido.toFixed(2);
        }

        actualizarPreview();
    }

    function actualizarPreview() {
        const precioSinIva = parseFloat(precio.value || 0);
        const ivaPorcentaje = parseFloat(iva.value || 0);
        const ivaMonto = precioSinIva * (ivaPorcentaje / 100);
        const precioFinal = precioSinIva + ivaMonto;

        total.textContent = '$' + precioFinal.toFixed(2) + ' MXN';
        detail.textContent = '$' + precioSinIva.toFixed(2) + ' sin IVA + $' + ivaMonto.toFixed(2) + ' IVA';
    }

    if(costo && factor && precio && iva) {
        costo.addEventListener('input', sugerirPrecio);
        factor.addEventListener('input', sugerirPrecio);
        precio.addEventListener('input', actualizarPreview);
        iva.addEventListener('input', actualizarPreview);
        actualizarPreview();
    }
})();
</script>

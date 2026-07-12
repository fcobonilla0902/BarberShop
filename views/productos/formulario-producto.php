<div class="form-grid">
    <div class="campo campo--stack">
        <label for="nombre">Nombre del producto</label>
        <input type="text" id="nombre" name="nombre" placeholder="Ej. Pomada Modeladora" maxlength="90" required value="<?php echo s($producto->nombre ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="categoria_producto_id">Categoría</label>
        <select id="categoria_producto_id" name="categoria_producto_id" required>
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
        <input type="text" id="marca" name="marca" placeholder="Ej. Barber Pro" maxlength="70" value="<?php echo s($producto->marca ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="codigo_barras">Código de barras</label>
        <input type="text" id="codigo_barras" name="codigo_barras" placeholder="Opcional" inputmode="numeric" maxlength="20" value="<?php echo s($producto->codigo_barras ?? ''); ?>">
        <small>Solo números.</small>
    </div>

    <div class="campo campo--stack form-grid__full">
        <label for="descripcion">Descripción</label>
        <input type="text" id="descripcion" name="descripcion" placeholder="Descripción breve" maxlength="180" value="<?php echo s($producto->descripcion ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="unidad_medida">Unidad de medida</label>
        <input type="text" id="unidad_medida" name="unidad_medida" placeholder="pieza" maxlength="30" required value="<?php echo s($producto->unidad_medida ?? 'pieza'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="stock_minimo">Stock mínimo</label>
        <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" placeholder="5" required value="<?php echo s($producto->stock_minimo ?? '0'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="costo_referencia_sin_iva">Costo referencia sin IVA</label>
        <input type="number" id="costo_referencia_sin_iva" name="costo_referencia_sin_iva" min="0.01" step="0.01" placeholder="95.00" required value="<?php echo s($producto->costo_referencia_sin_iva ?? '0.00'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="factor_utilidad">Factor utilidad</label>
        <input type="number" id="factor_utilidad" name="factor_utilidad" min="1.01" step="0.01" placeholder="1.30" required value="<?php echo s($producto->factor_utilidad ?? '1.30'); ?>">
        <small>Ejemplo: costo 100 x factor 1.30 = precio sugerido 130 sin IVA.</small>
    </div>

    <div class="campo campo--stack">
        <label for="precio_venta_sin_iva">Precio venta sin IVA</label>
        <input type="number" id="precio_venta_sin_iva" name="precio_venta_sin_iva" min="0.01" step="0.01" placeholder="155.17" required value="<?php echo s($producto->precio_venta_sin_iva ?? '0.00'); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="iva_porcentaje">IVA %</label>
        <input type="number" id="iva_porcentaje" name="iva_porcentaje" min="0" max="100" step="0.01" placeholder="16.00" required value="<?php echo s($producto->iva_porcentaje ?? '16.00'); ?>">
    </div>

    <div class="campo campo--stack form-grid__full">
        <label for="imagen_url">URL imagen</label>
        <input type="text" id="imagen_url" name="imagen_url" placeholder="/build/img/productos/producto.webp" maxlength="255" value="<?php echo s($producto->imagen_url ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="activo">Estado</label>
        <select id="activo" name="activo" required>
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
    const form = document.currentScript.closest('form');
    const nombre = document.querySelector('#nombre');
    const categoria = document.querySelector('#categoria_producto_id');
    const marca = document.querySelector('#marca');
    const codigoBarras = document.querySelector('#codigo_barras');
    const descripcion = document.querySelector('#descripcion');
    const unidad = document.querySelector('#unidad_medida');
    const stockMinimo = document.querySelector('#stock_minimo');
    const costo = document.querySelector('#costo_referencia_sin_iva');
    const factor = document.querySelector('#factor_utilidad');
    const precio = document.querySelector('#precio_venta_sin_iva');
    const iva = document.querySelector('#iva_porcentaje');
    const imagen = document.querySelector('#imagen_url');
    const total = document.querySelector('#preview-total');
    const detail = document.querySelector('#preview-detail');

    const textoProducto = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s.,+&()\/-]/g;
    const soloLetras = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g;

    function limpiar(input, regex) {
        if(!input) return;
        input.addEventListener('input', function() {
            const limpio = input.value.replace(regex, '');
            if(input.value !== limpio) input.value = limpio;
        });
    }

    function numero(input) {
        return Number.parseFloat(input?.value || '0');
    }

    function entero(input) {
        return Number.parseInt(input?.value || '0', 10);
    }

    function sugerirPrecio() {
        const costoValor = numero(costo);
        const factorValor = numero(factor);
        const precioSugerido = costoValor * factorValor;

        if(precioSugerido > 0 && (!precio.value || Number.parseFloat(precio.value) === 0)) {
            precio.value = precioSugerido.toFixed(2);
        }

        actualizarPreview();
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

    function validarProducto(event) {
        const errores = [];
        const nombreValor = nombre.value.trim();
        const marcaValor = marca.value.trim();
        const codigoValor = codigoBarras.value.trim();
        const descripcionValor = descripcion.value.trim();
        const unidadValor = unidad.value.trim();
        const stockValor = entero(stockMinimo);
        const costoValor = numero(costo);
        const factorValor = numero(factor);
        const precioValor = numero(precio);
        const ivaValor = numero(iva);
        const imagenValor = imagen.value.trim();

        if(nombreValor.length < 3) {
            errores.push('El nombre del producto debe tener al menos 3 caracteres.');
        }

        if(!categoria.value) {
            errores.push('Selecciona una categoría para el producto.');
        }

        if(marcaValor !== '' && marcaValor.length < 2) {
            errores.push('La marca debe tener al menos 2 caracteres o dejarse vacía.');
        }

        if(codigoValor !== '' && !/^\d{6,20}$/.test(codigoValor)) {
            errores.push('El código de barras debe contener solo números y tener entre 6 y 20 dígitos.');
        }

        if(descripcionValor.length > 180) {
            errores.push('La descripción no debe superar 180 caracteres.');
        }

        if(unidadValor.length < 2 || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(unidadValor)) {
            errores.push('La unidad de medida solo debe contener letras.');
        }

        if(!Number.isInteger(stockValor) || stockValor < 0) {
            errores.push('El stock mínimo debe ser un número entero igual o mayor a 0.');
        }

        if(!Number.isFinite(costoValor) || costoValor <= 0) {
            errores.push('El costo referencia sin IVA debe ser mayor a 0.');
        }

        if(!Number.isFinite(factorValor) || factorValor <= 1) {
            errores.push('El factor de utilidad debe ser mayor a 1.');
        }

        if(!Number.isFinite(precioValor) || precioValor <= 0) {
            errores.push('El precio de venta sin IVA debe ser mayor a 0.');
        }

        if(costoValor > 0 && precioValor > 0 && precioValor <= costoValor) {
            errores.push('El precio de venta debe ser mayor que el costo de referencia.');
        }

        if(!Number.isFinite(ivaValor) || ivaValor < 0 || ivaValor > 100) {
            errores.push('El IVA debe estar entre 0 y 100.');
        }

        if(imagenValor !== '' && !/^(\/|https?:\/\/)/i.test(imagenValor)) {
            errores.push('La URL de imagen debe iniciar con /, http:// o https://.');
        }

        if(errores.length > 0) {
            event.preventDefault();
            mostrarErrores(errores);
        } else {
            limpiarAlertas();
        }
    }

    limpiar(nombre, textoProducto);
    limpiar(marca, textoProducto);
    limpiar(descripcion, textoProducto);
    limpiar(unidad, soloLetras);

    if(codigoBarras) {
        codigoBarras.addEventListener('input', function() {
            codigoBarras.value = codigoBarras.value.replace(/\D/g, '');
        });
    }

    if(costo && factor && precio && iva) {
        costo.addEventListener('input', sugerirPrecio);
        factor.addEventListener('input', sugerirPrecio);
        precio.addEventListener('input', actualizarPreview);
        iva.addEventListener('input', actualizarPreview);
        actualizarPreview();
    }

    if(form) {
        form.addEventListener('submit', validarProducto);
    }
})();
</script>

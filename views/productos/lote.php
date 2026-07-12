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

    <form action="/productos/lote" method="POST" class="formulario form-panel">
        <div class="form-grid">
            <div class="campo campo--stack form-grid__full">
                <label for="producto_id">Producto</label>
                <select id="producto_id" name="producto_id">
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
                <select id="estado_lote_id" name="estado_lote_id">
                    <?php foreach($estados as $estado) { ?>
                        <option value="<?php echo (int)$estado->id; ?>" <?php echo ((string)($lote->estado_lote_id ?? '1') === (string)$estado->id) ? 'selected' : ''; ?>>
                            <?php echo s($estado->nombre); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="campo campo--stack">
                <label for="codigo_lote">Código de lote</label>
                <input type="text" id="codigo_lote" name="codigo_lote" placeholder="Ej. POM-2026-001" value="<?php echo s($lote->codigo_lote ?? ''); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="fecha_entrada">Fecha de entrada</label>
                <input type="date" id="fecha_entrada" name="fecha_entrada" value="<?php echo s($lote->fecha_entrada ?? date('Y-m-d')); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="fecha_caducidad">Fecha de caducidad</label>
                <input type="date" id="fecha_caducidad" name="fecha_caducidad" value="<?php echo s($lote->fecha_caducidad ?? ''); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="cantidad_inicial">Cantidad inicial</label>
                <input type="number" id="cantidad_inicial" name="cantidad_inicial" min="0" step="1" placeholder="10" value="<?php echo s($lote->cantidad_inicial ?? ''); ?>">
            </div>

            <div class="campo campo--stack">
                <label for="costo_unitario_sin_iva">Costo unitario sin IVA</label>
                <input type="number" id="costo_unitario_sin_iva" name="costo_unitario_sin_iva" min="0" step="0.01" placeholder="95.00" value="<?php echo s($lote->costo_unitario_sin_iva ?? ''); ?>">
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

<section class="admin-page product-lots-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Inventario por lotes</span>
            <h1>Detalle de lotes</h1>
            <p>Consulta producto, lote, caducidad, cantidades, estado y proveedor.</p>
        </div>

        <div class="page-actions">
            <a href="/productos" class="btn btn--soft">Ver productos</a>
            <a href="/productos/lote" class="btn btn--primary">Agregar lote</a>
        </div>
    </div>

    <form method="GET" action="/productos/lotes" class="lots-filters">
        <div class="campo campo--stack">
            <label for="producto_id">Producto</label>
            <select id="producto_id" name="producto_id">
                <option value="">Todos</option>
                <?php foreach($productos as $producto) { ?>
                    <option value="<?php echo (int)$producto->id; ?>" <?php echo ((int)$productoId === (int)$producto->id) ? 'selected' : ''; ?>>
                        <?php echo s($producto->nombre); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="campo campo--stack">
            <label for="estado_id">Estado</label>
            <select id="estado_id" name="estado_id">
                <option value="">Todos</option>
                <?php foreach($estados as $estado) { ?>
                    <option value="<?php echo (int)$estado->id; ?>" <?php echo ((int)$estadoId === (int)$estado->id) ? 'selected' : ''; ?>>
                        <?php echo s($estado->nombre); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="campo campo--stack">
            <label for="caducidad">Caducidad</label>
            <select id="caducidad" name="caducidad">
                <option value="">Todas</option>
                <option value="vigentes" <?php echo $caducidad === 'vigentes' ? 'selected' : ''; ?>>Vigentes</option>
                <option value="por_caducar" <?php echo $caducidad === 'por_caducar' ? 'selected' : ''; ?>>Por caducar</option>
                <option value="caducados" <?php echo $caducidad === 'caducados' ? 'selected' : ''; ?>>Caducados</option>
                <option value="sin_caducidad" <?php echo $caducidad === 'sin_caducidad' ? 'selected' : ''; ?>>Sin caducidad</option>
            </select>
        </div>

        <div class="campo campo--stack">
            <label for="q">Buscar</label>
            <input type="text" id="q" name="q" placeholder="Producto, lote, proveedor..." value="<?php echo s($q); ?>">
        </div>

        <div class="lots-filters__actions">
            <button type="submit" class="btn btn--primary">Filtrar</button>
            <a href="/productos/lotes" class="btn btn--soft">Limpiar</a>
        </div>
    </form>

    <div class="kpi-grid product-lots-kpis">
        <article class="kpi-card">
            <span>Lotes</span>
            <strong><?php echo (int)$metricas['total_lotes']; ?></strong>
            <small>Lotes encontrados</small>
        </article>

        <article class="kpi-card">
            <span>Cantidad inicial</span>
            <strong><?php echo (int)$metricas['cantidad_inicial']; ?></strong>
            <small>Piezas registradas</small>
        </article>

        <article class="kpi-card">
            <span>Cantidad actual</span>
            <strong><?php echo (int)$metricas['cantidad_actual']; ?></strong>
            <small>Stock disponible en lotes</small>
        </article>

        <article class="kpi-card">
            <span>Riesgo</span>
            <strong><?php echo (int)$metricas['por_caducar']; ?></strong>
            <small>Por caducar · <?php echo (int)$metricas['caducados']; ?> caducado(s)</small>
        </article>
    </div>

    <section class="product-lots-list">
        <?php if(empty($lotes)) { ?>
            <article class="lot-card lot-card--empty">
                <span class="screen-tag">Sin resultados</span>
                <h2>No hay lotes con esos filtros</h2>
                <p>Agrega un lote nuevo o cambia los filtros de búsqueda.</p>
                <a href="/productos/lote" class="btn btn--primary">Agregar lote</a>
            </article>
        <?php } ?>

        <?php foreach($lotes as $lote) { ?>
            <?php
                $caducidadClass = 'lot-expiration';

                if($lote['estado_caducidad'] === 'Caducado') {
                    $caducidadClass .= ' lot-expiration--danger';
                } elseif($lote['estado_caducidad'] === 'Por caducar') {
                    $caducidadClass .= ' lot-expiration--warning';
                } elseif($lote['estado_caducidad'] === 'Sin caducidad') {
                    $caducidadClass .= ' lot-expiration--neutral';
                } else {
                    $caducidadClass .= ' lot-expiration--success';
                }

                $stockClass = 'lot-stock';
                if((int)$lote['cantidad_actual'] <= 0) {
                    $stockClass .= ' lot-stock--empty';
                } elseif((int)$lote['cantidad_actual'] <= 3) {
                    $stockClass .= ' lot-stock--low';
                }

                $fechaCaducidad = $lote['fecha_caducidad']
                    ? date('d/m/Y', strtotime($lote['fecha_caducidad']))
                    : 'Sin caducidad';
            ?>

            <article class="lot-card">
                <div class="lot-card__head">
                    <div>
                        <span class="service-chip"><?php echo s($lote['categoria']); ?></span>
                        <h2><?php echo s($lote['producto']); ?></h2>
                        <p>
                            <?php echo s($lote['marca'] ?: 'Sin marca'); ?>
                            · Proveedor: <?php echo s($lote['proveedor']); ?>
                        </p>
                    </div>

                    <span class="<?php echo $caducidadClass; ?>">
                        <?php echo s($lote['estado_caducidad']); ?>
                    </span>
                </div>

                <div class="lot-card__grid">
                    <div>
                        <span>Lote</span>
                        <strong><?php echo s($lote['codigo_lote']); ?></strong>
                    </div>

                    <div>
                        <span>Entrada</span>
                        <strong><?php echo s(date('d/m/Y', strtotime($lote['fecha_entrada']))); ?></strong>
                    </div>

                    <div>
                        <span>Caducidad</span>
                        <strong><?php echo s($fechaCaducidad); ?></strong>
                    </div>

                    <div>
                        <span>Cantidad inicial</span>
                        <strong><?php echo (int)$lote['cantidad_inicial']; ?></strong>
                    </div>

                    <div>
                        <span>Cantidad actual</span>
                        <strong class="<?php echo $stockClass; ?>"><?php echo (int)$lote['cantidad_actual']; ?></strong>
                    </div>

                    <div>
                        <span>Estado</span>
                        <strong><?php echo s($lote['estado']); ?></strong>
                    </div>

                    <div>
                        <span>Costo unitario</span>
                        <strong>$<?php echo number_format((float)$lote['costo_unitario_sin_iva'], 2); ?></strong>
                    </div>
                </div>

                <div class="lot-card__actions">
                    <a href="/productos/lotes?producto_id=<?php echo (int)$lote['producto_id']; ?>" class="btn btn--soft">Ver producto</a>
                    <a href="/productos/lote?producto_id=<?php echo (int)$lote['producto_id']; ?>" class="btn btn--soft">Agregar otro lote</a>
                </div>
            </article>
        <?php } ?>
    </section>
</section>

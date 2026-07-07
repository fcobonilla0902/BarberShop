<section class="admin-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Inventario</span>
            <h1>Productos</h1>
            <p>Productos vendibles con precio, IVA y stock calculado desde lotes.</p>
        </div>

        <div class="page-actions">
            <a href="/productos/lotes" class="btn btn--soft">Ver lotes</a>
            <a href="/productos/lote" class="btn btn--soft">Agregar lote</a>
            <a href="/productos/crear" class="btn btn--primary">Agregar producto</a>
        </div>
    </div>

    <section class="product-ranking-grid">
        <article class="data-card product-ranking-card product-ranking-card--top">
            <div class="data-card__header">
                <div>
                    <h2>Productos más vendidos</h2>
                    <p>Ranking por unidades vendidas en tickets registrados.</p>
                </div>
                <span class="status-badge status-badge--success">Top 5</span>
            </div>

            <div class="product-ranking-list">
                <?php if(empty($productosMasVendidos)) { ?>
                    <div class="empty-state">Aún no hay ventas de productos registradas.</div>
                <?php } ?>

                <?php foreach($productosMasVendidos as $index => $productoRanking) { ?>
                    <div class="product-ranking-item">
                        <div class="product-ranking-item__place">#<?php echo $index + 1; ?></div>

                        <div class="product-ranking-item__body">
                            <strong><?php echo s($productoRanking['nombre']); ?></strong>
                            <small>
                                <?php echo s($productoRanking['marca'] ?: 'Sin marca'); ?>
                                · <?php echo s($productoRanking['categoria']); ?>
                            </small>
                        </div>

                        <div class="product-ranking-item__stats">
                            <span><?php echo (int)$productoRanking['unidades_vendidas']; ?> uds.</span>
                            <strong>$<?php echo number_format((float)$productoRanking['total_vendido'], 2); ?></strong>
                            <small><?php echo (int)$productoRanking['tickets']; ?> ticket(s)</small>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </article>

        <article class="data-card product-ranking-card product-ranking-card--low">
            <div class="data-card__header">
                <div>
                    <h2>Productos menos vendidos</h2>
                    <p>Incluye productos activos con pocas ventas o sin ventas.</p>
                </div>
                <span class="status-badge status-badge--warning">Bottom 5</span>
            </div>

            <div class="product-ranking-list">
                <?php if(empty($productosMenosVendidos)) { ?>
                    <div class="empty-state">No hay productos activos para comparar.</div>
                <?php } ?>

                <?php foreach($productosMenosVendidos as $index => $productoRanking) { ?>
                    <div class="product-ranking-item">
                        <div class="product-ranking-item__place">#<?php echo $index + 1; ?></div>

                        <div class="product-ranking-item__body">
                            <strong><?php echo s($productoRanking['nombre']); ?></strong>
                            <small>
                                <?php echo s($productoRanking['marca'] ?: 'Sin marca'); ?>
                                · <?php echo s($productoRanking['categoria']); ?>
                            </small>
                        </div>

                        <div class="product-ranking-item__stats">
                            <span><?php echo (int)$productoRanking['unidades_vendidas']; ?> uds.</span>
                            <strong>$<?php echo number_format((float)$productoRanking['total_vendido'], 2); ?></strong>
                            <small><?php echo (int)$productoRanking['tickets']; ?> ticket(s)</small>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </article>
    </section>

    <section class="data-card">
        <div class="data-card__header">
            <div>
                <h2>Inventario por producto</h2>
                <p>El stock total se calcula sumando la cantidad actual de sus lotes.</p>
            </div>

            <span class="status-badge">Lotes</span>
        </div>

        <div class="table-wrap">
            <table class="table-modern product-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio sin IVA</th>
                        <th>IVA</th>
                        <th>Precio final</th>
                        <th>Stock total</th>
                        <th>Estado stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(empty($productos)) { ?>
                        <tr>
                            <td colspan="9">No hay productos registrados.</td>
                        </tr>
                    <?php } ?>

                    <?php foreach($productos as $producto) { ?>
                        <?php
                            $stock = (int)$producto->stock_total;
                            $stockMinimo = (int)$producto->stock_minimo;

                            $stockTexto = 'Disponible';
                            $stockClass = 'status-badge--success';

                            if($stock <= 0) {
                                $stockTexto = 'Agotado';
                                $stockClass = 'status-badge--danger';
                            } elseif($stock <= $stockMinimo) {
                                $stockTexto = 'Bajo stock';
                                $stockClass = 'status-badge--warning';
                            }
                        ?>

                        <tr>
                            <td>
                                <strong><?php echo s($producto->nombre); ?></strong>
                                <br>
                                <small>
                                    <?php echo s($producto->marca ?: 'Sin marca'); ?>
                                    <?php if($producto->codigo_barras) { ?>
                                        · CB: <?php echo s($producto->codigo_barras); ?>
                                    <?php } ?>
                                </small>
                            </td>

                            <td><?php echo s($producto->categoria); ?></td>

                            <td>$<?php echo number_format((float)$producto->precio_venta_sin_iva, 2); ?></td>

                            <td>
                                <?php echo number_format((float)$producto->iva_porcentaje, 2); ?>%
                                <br>
                                <small>$<?php echo number_format((float)$producto->iva_monto, 2); ?></small>
                            </td>

                            <td>
                                <strong>$<?php echo number_format((float)$producto->precio_final, 2); ?> MXN</strong>
                            </td>

                            <td>
                                <strong><?php echo $stock; ?></strong>
                                <small> / mínimo <?php echo $stockMinimo; ?></small>
                            </td>

                            <td>
                                <span class="status-badge <?php echo $stockClass; ?>">
                                    <?php echo $stockTexto; ?>
                                </span>
                            </td>

                            <td>
                                <?php if((string)$producto->activo === '1') { ?>
                                    <span class="status-badge status-badge--success">Activo</span>
                                <?php } else { ?>
                                    <span class="status-badge status-badge--warning">Inactivo</span>
                                <?php } ?>
                            </td>

                            <td>
                                <div class="service-actions-inline">
                                    <a href="/productos/lotes?producto_id=<?php echo (int)$producto->id; ?>" class="btn btn--soft">Ver lotes</a>
                                    <a href="/productos/lote?producto_id=<?php echo (int)$producto->id; ?>" class="btn btn--soft">Agregar lote</a>

                                    <?php if((string)$producto->activo === '1') { ?>
                                        <form action="/productos/desactivar" method="POST" onsubmit="return confirm('¿Desactivar este producto?');">
                                            <input type="hidden" name="id" value="<?php echo (int)$producto->id; ?>">
                                            <input type="submit" value="Desactivar" class="btn btn--danger">
                                        </form>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</section>

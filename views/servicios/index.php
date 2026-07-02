<section class="admin-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Catálogo</span>
            <h1>Servicios</h1>
            <p>Servicios disponibles para agenda y ventas.</p>
        </div>

        <a href="/servicios/crear" class="btn btn--primary">Nuevo Servicio</a>
    </div>

    <section class="data-card">
        <div class="data-card__header">
            <div>
                <h2>Listado de servicios</h2>
                <p>Incluye categoría, duración, precio sin IVA, IVA y precio final.</p>
            </div>

            <span class="status-badge">Activos primero</span>
        </div>

        <div class="table-wrap">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Categoría</th>
                        <th>Duración</th>
                        <th>Precio sin IVA</th>
                        <th>IVA</th>
                        <th>Precio final</th>
                        <th>Costo estimado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(empty($servicios)) { ?>
                        <tr>
                            <td colspan="9">No hay servicios registrados.</td>
                        </tr>
                    <?php } ?>

                    <?php foreach($servicios as $servicio) { ?>
                        <?php
                            $precioSinIva = (float)$servicio->precio_base_sin_iva;
                            $ivaMonto = (float)$servicio->iva_monto;
                            $precioFinal = (float)$servicio->precio_final;
                        ?>

                        <tr>
                            <td>
                                <strong><?php echo s($servicio->nombre); ?></strong>
                                <br>
                                <small><?php echo s($servicio->descripcion ?: 'Sin descripción'); ?></small>
                            </td>

                            <td><?php echo s($servicio->categoria); ?></td>

                            <td><?php echo (int)$servicio->duracion_minutos; ?> min</td>

                            <td>$<?php echo number_format($precioSinIva, 2); ?></td>

                            <td>
                                <?php echo number_format((float)$servicio->iva_porcentaje, 2); ?>%
                                <br>
                                <small>$<?php echo number_format($ivaMonto, 2); ?></small>
                            </td>

                            <td>
                                <strong>$<?php echo number_format($precioFinal, 2); ?> MXN</strong>
                            </td>

                            <td>$<?php echo number_format((float)$servicio->costo_estimado_sin_iva, 2); ?></td>

                            <td>
                                <?php if((string)$servicio->activo === '1') { ?>
                                    <span class="status-badge status-badge--success">Activo</span>
                                <?php } else { ?>
                                    <span class="status-badge status-badge--warning">Inactivo</span>
                                <?php } ?>
                            </td>

                            <td>
                                <div class="service-actions-inline">
                                    <a href="/servicios/actualizar?id=<?php echo (int)$servicio->id; ?>" class="btn btn--soft">Editar</a>

                                    <?php if((string)$servicio->activo === '1') { ?>
                                        <form action="/servicios/eliminar" method="POST" onsubmit="return confirm('¿Desactivar este servicio?');">
                                            <input type="hidden" name="id" value="<?php echo (int)$servicio->id; ?>">
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

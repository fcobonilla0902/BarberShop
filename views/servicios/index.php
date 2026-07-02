<section class="admin-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Catálogo</span>
            <h1>Servicios</h1>
            <p>Administración de servicios de barbería.</p>
        </div>

        <a href="/servicios/crear" class="btn btn--primary">Nuevo Servicio</a>
    </div>

    <div class="service-grid">
        <?php foreach($servicios as $servicio) { ?>
            <?php
                $precioSinIva = (float)($servicio->precio_base_sin_iva ?? 0);
                $iva = (float)($servicio->iva_porcentaje ?? 16);
                $total = $precioSinIva + ($precioSinIva * ($iva / 100));
            ?>
            <article class="service-card-admin">
                <div>
                    <span class="screen-tag"><?php echo (int)($servicio->duracion_minutos ?? 0); ?> min</span>
                    <h3><?php echo s($servicio->nombre); ?></h3>
                    <p><?php echo s($servicio->descripcion ?? 'Servicio de barbería'); ?></p>
                </div>

                <div class="service-card-admin__price">
                    <span>Precio final</span>
                    <strong>$<?php echo number_format($total, 2); ?> MXN</strong>
                </div>

                <div class="service-card-admin__actions">
                    <a href="/servicios/actualizar?id=<?php echo (int)$servicio->id; ?>" class="btn btn--soft">Editar</a>

                    <form action="/servicios/eliminar" method="POST">
                        <input type="hidden" name="id" value="<?php echo (int)$servicio->id; ?>">
                        <input type="submit" value="Eliminar" class="btn btn--danger">
                    </form>
                </div>
            </article>
        <?php } ?>
    </div>
</section>

<section class="admin-page admin-page--narrow">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Inventario</span>
            <h1>Agregar producto</h1>
            <p>Registra un producto vendible. El stock se agrega después por lote.</p>
        </div>

        <a href="/productos" class="btn btn--soft">Volver</a>
    </div>

    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <form action="/productos/crear" method="POST" class="formulario form-panel">
        <?php include_once __DIR__ . '/formulario-producto.php'; ?>

        <div class="form-actions">
            <a href="/productos" class="btn btn--soft">Cancelar</a>
            <input type="submit" class="btn btn--primary" value="Guardar producto">
        </div>
    </form>
</section>

<section class="admin-page admin-page--narrow">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Catálogo</span>
            <h1>Nuevo Servicio</h1>
            <p>Registra nombre, categoría, duración, precio e IVA.</p>
        </div>

        <a href="/servicios" class="btn btn--soft">Volver</a>
    </div>

    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <form action="/servicios/crear" method="POST" class="formulario form-panel">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <div class="form-actions">
            <a href="/servicios" class="btn btn--soft">Cancelar</a>
            <input type="submit" class="btn btn--primary" value="Guardar servicio">
        </div>
    </form>
</section>

<section class="admin-page admin-page--narrow">
    <?php
        include_once __DIR__ . '/../templates/barra.php';
        include_once __DIR__ . '/../templates/alertas.php';
    ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Catálogo</span>
            <h1>Actualizar Servicio</h1>
            <p>Modifica los datos del servicio seleccionado.</p>
        </div>
    </div>

    <form method="POST" class="formulario form-panel">
        <?php include_once __DIR__ . '/formulario.php'; ?>
        <input type="submit" class="btn btn--primary btn--full" value="Actualizar">
    </form>
</section>

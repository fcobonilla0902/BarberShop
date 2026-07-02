<div class="client-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <section class="client-heading">
        <span class="screen-tag">Reservación</span>
        <h1>Crear Nueva Cita</h1>
        <p>Elige tus servicios, selecciona fecha y revisa el resumen antes de confirmar.</p>
    </section>

    <div class="appointment-layout">
        <div class="appointment-main">
            <nav class="tabs tabs-modern">
                <button class="actual" type="button" data-paso="1">Servicios</button>
                <button type="button" data-paso="2">Información Cita</button>
                <button type="button" data-paso="3">Resumen</button>
            </nav>

            <div class="seccion" id="paso-1">
                <div class="section-heading">
                    <h2>Servicios disponibles</h2>
                    <p>Selecciona uno o varios servicios para tu cita.</p>
                </div>

                <div id="servicios" class="listado-servicios listado-servicios--cards"></div>
            </div>

            <div class="seccion" id="paso-2">
                <div class="section-heading">
                    <h2>Datos de la cita</h2>
                    <p>Selecciona fecha, hora y agrega una nota si lo necesitas.</p>
                </div>

                <form class="formulario appointment-form">
                    <div class="campo campo--stack">
                        <label for="nombre">Cliente</label>
                        <input type="text" id="nombre" value="<?php echo s($nombre); ?>" disabled>
                    </div>

                    <div class="campo campo--stack">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <div class="campo campo--stack">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" step="900">
                    </div>

                    <div class="campo campo--stack">
                        <label for="notas">Notas</label>
                        <input type="text" id="notas" placeholder="Ej. prefiero corte bajo">
                    </div>

                    <input type="hidden" id="id" value="<?php echo s($id); ?>">
                </form>
            </div>

            <div class="seccion contenido-resumen" id="paso-3">
                <div class="section-heading">
                    <h2>Resumen de cita</h2>
                    <p>Verifica que la información sea correcta.</p>
                </div>
            </div>

            <div class="paginacion pagination-modern">
                <button id="anterior" class="btn btn--soft">&laquo; Anterior</button>
                <button id="siguiente" class="btn btn--primary">Siguiente &raquo;</button>
            </div>
        </div>

        <aside class="appointment-summary">
            <span class="screen-tag">Resumen</span>
            <h3>Tu cita</h3>
            <p>Los servicios seleccionados se mostrarán antes de confirmar.</p>

            <div class="summary-item">
                <span>Cliente</span>
                <strong><?php echo s($nombre); ?></strong>
            </div>

            <div class="summary-item">
                <span>Estado</span>
                <strong>En captura</strong>
            </div>

            <div class="summary-note">
                El total final se calcula con IVA incluido en el resumen.
            </div>
        </aside>
    </div>
</div>

<?php
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='/build/js/app.js'></script>
    ";
?>

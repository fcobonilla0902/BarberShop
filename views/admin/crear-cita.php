<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<section class="admin-page admin-create-appointment-page">
    <div class="page-heading admin-appointments-heading">
        <div>
            <span class="screen-tag">Nueva cita</span>
            <h1>Agendar cita desde administración</h1>
            <p>Crea citas para clientes registrados o para personas sin cuenta usando un nombre/alias de contacto.</p>
        </div>

        <div class="page-actions">
            <a href="/admin/citas" class="btn btn--soft">Volver a citas</a>
        </div>
    </div>

    <?php if(!empty($alertas)) { ?>
        <?php foreach($alertas as $tipo => $mensajes) { ?>
            <?php foreach($mensajes as $mensaje) { ?>
                <div class="alerta <?php echo s($tipo); ?>">
                    <?php echo s($mensaje); ?>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>

    <form method="POST" action="/admin/citas/crear" class="admin-create-appointment-layout" id="admin-create-appointment-form">
        <section class="form-panel admin-create-appointment-form">
            <div class="data-card__header">
                <div>
                    <h2>Datos de la cita</h2>
                    <p>El sistema asigna automáticamente el primer barbero activo y bloquea su agenda.</p>
                </div>
                <span class="status-badge">Agenda</span>
            </div>

            <div class="form-grid">
                <div class="campo campo--stack form-grid__full">
                    <label for="tipo_cliente">Tipo de cliente</label>
                    <select id="tipo_cliente" name="tipo_cliente">
                        <option value="registrado" <?php echo ($form['tipo_cliente'] ?? '') === 'registrado' ? 'selected' : ''; ?>>Cliente registrado</option>
                        <option value="alias" <?php echo ($form['tipo_cliente'] ?? '') === 'alias' ? 'selected' : ''; ?>>Persona sin cuenta / alias</option>
                    </select>
                </div>

                <div class="campo campo--stack form-grid__full" data-client-section="registrado">
                    <label for="cliente_id">Cliente registrado</label>
                    <select id="cliente_id" name="cliente_id">
                        <option value="">Selecciona cliente</option>
                        <?php foreach($clientes as $cliente) { ?>
                            <option value="<?php echo (int)$cliente['id']; ?>" <?php echo (string)($form['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : ''; ?>>
                                <?php echo s($cliente['nombre']); ?> · <?php echo s($cliente['email'] ?: 'Sin correo'); ?> · <?php echo s($cliente['telefono'] ?: 'Sin teléfono'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <small>Usa esta opción cuando el cliente ya tenga cuenta en el sistema.</small>
                </div>

                <div class="campo campo--stack" data-client-section="alias">
                    <label for="alias_nombre">Nombre o alias</label>
                    <input type="text" id="alias_nombre" name="alias_nombre" maxlength="120" placeholder="Ej. Juan Pérez / Cliente mostrador" value="<?php echo s($form['alias_nombre'] ?? ''); ?>">
                </div>

                <div class="campo campo--stack" data-client-section="alias">
                    <label for="alias_telefono">Teléfono</label>
                    <input type="text" id="alias_telefono" name="alias_telefono" maxlength="20" placeholder="Opcional" value="<?php echo s($form['alias_telefono'] ?? ''); ?>">
                </div>

                <div class="campo campo--stack" data-client-section="alias">
                    <label for="alias_email">Correo</label>
                    <input type="email" id="alias_email" name="alias_email" maxlength="120" placeholder="Opcional" value="<?php echo s($form['alias_email'] ?? ''); ?>">
                </div>

                <div class="campo campo--stack">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo s($form['fecha'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="campo campo--stack">
                    <label for="hora">Hora de inicio</label>
                    <input type="time" id="hora" name="hora" min="10:00" max="18:00" step="900" value="<?php echo s($form['hora'] ?? ''); ?>">
                    <small>Horario permitido: 10:00 a 18:00. La hora fin no debe pasar de 19:00.</small>
                </div>

                <div class="campo campo--stack form-grid__full">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3" maxlength="180" placeholder="Notas internas de la cita"><?php echo s($form['observaciones'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="admin-create-services">
                <div class="data-card__header">
                    <div>
                        <h2>Servicios</h2>
                        <p>Selecciona uno o más servicios. La duración total define los bloques ocupados.</p>
                    </div>
                    <span class="status-badge status-badge--success">Servicios activos</span>
                </div>

                <div class="admin-create-services__grid">
                    <?php if(empty($servicios)) { ?>
                        <div class="empty-state">No hay servicios activos disponibles.</div>
                    <?php } ?>

                    <?php foreach($servicios as $servicio) { ?>
                        <?php
                            $servicioId = (int)$servicio['id'];
                            $seleccionado = in_array((string)$servicioId, array_map('strval', $form['servicios'] ?? []), true);
                            $precioFinal = (float)$servicio['precio_final'];
                        ?>

                        <label class="admin-service-option">
                            <input
                                type="checkbox"
                                name="servicios[]"
                                value="<?php echo $servicioId; ?>"
                                data-nombre="<?php echo s($servicio['nombre']); ?>"
                                data-duracion="<?php echo (int)$servicio['duracion_minutos']; ?>"
                                data-precio="<?php echo number_format($precioFinal, 2, '.', ''); ?>"
                                <?php echo $seleccionado ? 'checked' : ''; ?>
                            >
                            <span>
                                <b><?php echo s($servicio['nombre']); ?></b>
                                <small><?php echo s($servicio['categoria']); ?> · <?php echo (int)$servicio['duracion_minutos']; ?> min</small>
                                <strong>$<?php echo number_format($precioFinal, 2); ?> MXN</strong>
                            </span>
                        </label>
                    <?php } ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="/admin/citas" class="btn btn--soft">Cancelar</a>
                <button type="submit" class="btn btn--primary">Crear cita</button>
            </div>
        </section>

        <aside class="config-summary admin-create-summary">
            <span class="screen-tag">Resumen</span>
            <h2>Cita a crear</h2>
            <p>Revisa el cálculo antes de guardar.</p>

            <div class="config-summary__box">
                <span>Cliente</span>
                <strong id="summary-client">Pendiente</strong>
                <small id="summary-client-type">Selecciona cliente registrado o alias.</small>
            </div>

            <div class="config-summary__agenda">
                <span>Horario</span>
                <strong id="summary-time">--:--</strong>
                <p id="summary-date">Fecha pendiente</p>
            </div>

            <div class="config-summary__box">
                <span>Servicios</span>
                <strong id="summary-services">0 seleccionado(s)</strong>
                <small id="summary-duration">0 min</small>
            </div>

            <div class="config-summary__box">
                <span>Total estimado</span>
                <strong id="summary-total">$0.00 MXN</strong>
                <small>Incluye IVA según snapshot del servicio.</small>
            </div>
        </aside>
    </form>
</section>

<script>
(function() {
    const tipo = document.querySelector('#tipo_cliente');
    const clienteSelect = document.querySelector('#cliente_id');
    const aliasNombre = document.querySelector('#alias_nombre');
    const aliasTelefono = document.querySelector('#alias_telefono');
    const aliasEmail = document.querySelector('#alias_email');
    const fecha = document.querySelector('#fecha');
    const hora = document.querySelector('#hora');
    const servicios = document.querySelectorAll('input[name="servicios[]"]');

    const summaryClient = document.querySelector('#summary-client');
    const summaryClientType = document.querySelector('#summary-client-type');
    const summaryTime = document.querySelector('#summary-time');
    const summaryDate = document.querySelector('#summary-date');
    const summaryServices = document.querySelector('#summary-services');
    const summaryDuration = document.querySelector('#summary-duration');
    const summaryTotal = document.querySelector('#summary-total');

    function syncClientSections() {
        const isAlias = tipo.value === 'alias';
        document.querySelectorAll('[data-client-section]').forEach(section => {
            section.style.display = section.dataset.clientSection === tipo.value ? '' : 'none';
        });

        if(clienteSelect) clienteSelect.required = !isAlias;
        if(aliasNombre) aliasNombre.required = isAlias;

        updateSummary();
    }

    function getSelectedClientLabel() {
        if(tipo.value === 'alias') {
            return aliasNombre.value.trim() || 'Cliente sin cuenta';
        }

        if(!clienteSelect || !clienteSelect.value) {
            return 'Cliente registrado pendiente';
        }

        return clienteSelect.options[clienteSelect.selectedIndex].textContent.trim();
    }

    function updateSummary() {
        let total = 0;
        let duracion = 0;
        let count = 0;

        servicios.forEach(servicio => {
            if(servicio.checked) {
                count++;
                total += parseFloat(servicio.dataset.precio || 0);
                duracion += parseInt(servicio.dataset.duracion || 0, 10);
            }
        });

        summaryClient.textContent = getSelectedClientLabel();
        summaryClientType.textContent = tipo.value === 'alias' ? 'Persona sin cuenta.' : 'Cliente registrado.';
        summaryTime.textContent = hora.value || '--:--';
        summaryDate.textContent = fecha.value || 'Fecha pendiente';
        summaryServices.textContent = `${count} seleccionado(s)`;
        summaryDuration.textContent = `${duracion} min de duración total`;
        summaryTotal.textContent = `$${total.toFixed(2)} MXN`;
    }

    [tipo, clienteSelect, aliasNombre, aliasTelefono, aliasEmail, fecha, hora].forEach(input => {
        if(input) {
            input.addEventListener('input', updateSummary);
            input.addEventListener('change', updateSummary);
        }
    });

    servicios.forEach(servicio => servicio.addEventListener('change', updateSummary));

    if(tipo) tipo.addEventListener('change', syncClientSections);

    syncClientSections();
    updateSummary();
})();
</script>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<section class="admin-page admin-appointments-page">
    <div class="page-heading admin-appointments-heading">
        <div>
            <span class="screen-tag">Agenda operativa</span>
            <h1>Administrar citas</h1>
            <p>Controla las citas reservadas, atendidas, canceladas y solicitudes de cancelación.</p>
        </div>

        <div class="page-actions">
            <a href="/admin/citas/crear" class="btn btn--primary">Nueva cita</a>
            <a href="/admin" class="btn btn--soft">Volver al dashboard</a>
        </div>
    </div>

    <?php if($mensaje) { ?>
        <div class="alerta <?php echo s($mensaje['tipo']); ?>">
            <?php echo s($mensaje['texto']); ?>
        </div>
    <?php } ?>

    <form method="GET" action="/admin/citas" class="admin-filter-card">
        <div class="campo campo--stack">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo s($fecha); ?>">
        </div>

        <div class="campo campo--stack">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                <?php foreach($estados as $estado) { ?>
                    <option value="<?php echo s($estado['nombre']); ?>" <?php echo $estadoSeleccionado === $estado['nombre'] ? 'selected' : ''; ?>>
                        <?php echo s($estado['nombre']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="campo campo--stack">
            <label for="q">Cliente / correo / teléfono</label>
            <input type="text" id="q" name="q" placeholder="Buscar cliente" value="<?php echo s($busqueda); ?>">
        </div>

        <div class="admin-filter-card__actions">
            <button type="submit" class="btn btn--primary">Filtrar</button>
            <a href="/admin/citas?fecha=todas" class="btn btn--soft">Ver todas</a>
        </div>
    </form>

    <div class="kpi-grid admin-appointments-kpis">
        <article class="kpi-card">
            <span>Total filtrado</span>
            <strong><?php echo (int)$metricas['total']; ?></strong>
            <small>Citas encontradas</small>
        </article>

        <article class="kpi-card">
            <span>Reservadas</span>
            <strong><?php echo (int)$metricas['reservadas']; ?></strong>
            <small>Bloques ocupados</small>
        </article>

        <article class="kpi-card">
            <span>Cancelación solicitada</span>
            <strong><?php echo (int)$metricas['cancelacion_solicitada']; ?></strong>
            <small>Requieren decisión</small>
        </article>

        <article class="kpi-card">
            <span>Atendidas</span>
            <strong><?php echo (int)$metricas['atendidas']; ?></strong>
            <small>Servicios completados</small>
        </article>
    </div>

    <section class="admin-appointment-list">
        <?php if(empty($citas)) { ?>
            <article class="admin-appointment-card admin-appointment-card--empty">
                <span class="screen-tag">Sin resultados</span>
                <h2>No hay citas con esos filtros</h2>
                <p>Cambia la fecha, el estado o la búsqueda para revisar otra parte de la agenda.</p>
            </article>
        <?php } ?>

        <?php foreach($citas as $cita) { ?>
            <?php
                $estado = $cita['estado'];
                $estadoClass = 'appointment-status';

                if($estado === 'Reservada') {
                    $estadoClass .= ' appointment-status--reserved';
                } elseif($estado === 'Atendida') {
                    $estadoClass .= ' appointment-status--done';
                } elseif($estado === 'Cancelada') {
                    $estadoClass .= ' appointment-status--cancelled';
                } else {
                    $estadoClass .= ' appointment-status--pending-cancel';
                }

                $fechaLegible = date('d M Y', strtotime($cita['fecha']));
                $horaInicio = substr($cita['hora_inicio'], 0, 5);
                $horaFin = substr($cita['hora_fin'], 0, 5);

                $redirect = '/admin/citas?' . http_build_query([
                    'fecha' => $fecha ?: 'todas',
                    'estado' => $estadoSeleccionado,
                    'q' => $busqueda
                ]);
            ?>

            <article class="admin-appointment-card">
                <div class="admin-appointment-card__time">
                    <span><?php echo s($fechaLegible); ?></span>
                    <strong><?php echo s($horaInicio); ?></strong>
                    <small><?php echo s($horaFin); ?></small>
                </div>

                <div class="admin-appointment-card__body">
                    <div class="admin-appointment-card__top">
                        <div>
                            <h2><?php echo s($cita['cliente']); ?></h2>
                            <p><?php echo s($cita['cliente_email']); ?> · <?php echo s($cita['cliente_telefono']); ?></p>
                            <span class="status-badge status-badge--info"><?php echo s($cita['cliente_tipo'] ?? 'Registrado'); ?></span>
                        </div>

                        <span class="<?php echo $estadoClass; ?>">
                            <?php echo s($estado); ?>
                        </span>
                    </div>

                    <div class="admin-appointment-details">
                        <div>
                            <span>Servicios</span>
                            <strong><?php echo s($cita['servicios']); ?></strong>
                        </div>

                        <div>
                            <span>Barbero</span>
                            <strong><?php echo s($cita['barbero']); ?></strong>
                        </div>

                        <div>
                            <span>Total</span>
                            <strong>$<?php echo number_format((float)$cita['total_con_iva'], 2); ?> MXN</strong>
                        </div>

                        <div>
                            <span>Duración / bloques</span>
                            <strong><?php echo (int)$cita['duracion_total_minutos']; ?> min · <?php echo (int)$cita['bloques_ocupados']; ?> bloque(s)</strong>
                        </div>
                    </div>

                    <?php if(!empty($cita['observaciones'])) { ?>
                        <div class="admin-appointment-note">
                            <?php echo s($cita['observaciones']); ?>
                        </div>
                    <?php } ?>

                    <div class="admin-appointment-actions">
                        <?php if($estado === 'Reservada') { ?>
                            <form method="POST" action="/admin/citas/estado" onsubmit="return confirm('¿Marcar esta cita como atendida?');">
                                <input type="hidden" name="id" value="<?php echo (int)$cita['id']; ?>">
                                <input type="hidden" name="accion" value="atender">
                                <input type="hidden" name="redirect" value="<?php echo s($redirect); ?>">
                                <button type="submit" class="btn btn--primary">Marcar atendida</button>
                            </form>

                            <form method="POST" action="/admin/citas/estado" onsubmit="return confirm('¿Cancelar esta cita y liberar sus bloques?');">
                                <input type="hidden" name="id" value="<?php echo (int)$cita['id']; ?>">
                                <input type="hidden" name="accion" value="cancelar">
                                <input type="hidden" name="redirect" value="<?php echo s($redirect); ?>">
                                <button type="submit" class="btn btn--danger">Cancelar</button>
                            </form>

                            <details class="postpone-panel">
                                <summary class="postpone-panel__trigger">
                                    Posponer
                                </summary>

                                <form method="POST" action="/admin/citas/posponer" class="postpone-panel__form" onsubmit="return confirm('¿Posponer esta cita?');">
                                    <input type="hidden" name="id" value="<?php echo (int)$cita['id']; ?>">
                                    <input type="hidden" name="redirect" value="<?php echo s($redirect); ?>">

                                    <div class="postpone-panel__row">
                                        <div class="campo campo--stack">
                                            <label for="nueva_fecha_<?php echo (int)$cita['id']; ?>">Nueva fecha</label>
                                            <input type="date" id="nueva_fecha_<?php echo (int)$cita['id']; ?>" name="fecha" required>
                                        </div>

                                        <div class="campo campo--stack">
                                            <label for="nueva_hora_<?php echo (int)$cita['id']; ?>">Nueva hora</label>
                                            <input type="time" id="nueva_hora_<?php echo (int)$cita['id']; ?>" name="hora" required>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn--primary postpone-panel__submit">Confirmar pospuesta</button>
                                </form>
                            </details>
                        <?php } ?>

                        <?php if($estado === 'Cancelación solicitada') { ?>
                            <form method="POST" action="/admin/citas/estado" onsubmit="return confirm('¿Aprobar cancelación y liberar bloques?');">
                                <input type="hidden" name="id" value="<?php echo (int)$cita['id']; ?>">
                                <input type="hidden" name="accion" value="aprobar_cancelacion">
                                <input type="hidden" name="redirect" value="<?php echo s($redirect); ?>">
                                <button type="submit" class="btn btn--danger">Aprobar cancelación</button>
                            </form>

                            <form method="POST" action="/admin/citas/estado" onsubmit="return confirm('¿Rechazar la solicitud y regresar a reservada?');">
                                <input type="hidden" name="id" value="<?php echo (int)$cita['id']; ?>">
                                <input type="hidden" name="accion" value="rechazar_cancelacion">
                                <input type="hidden" name="redirect" value="<?php echo s($redirect); ?>">
                                <button type="submit" class="btn btn--soft">Rechazar solicitud</button>
                            </form>
                        <?php } ?>

                        <?php if($estado === 'Atendida' || $estado === 'Cancelada') { ?>
                            <span class="admin-appointment-locked">Sin acciones pendientes</span>
                        <?php } ?>
                    </div>
                </div>
            </article>
        <?php } ?>
    </section>
</section>

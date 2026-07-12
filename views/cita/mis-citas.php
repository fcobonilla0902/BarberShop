<div class="client-page client-page--wide">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <section class="client-heading">
        <span class="screen-tag">Historial</span>
        <h1>Mis citas</h1>
        <p>Consulta tus citas registradas y solicita cancelación cuando la cita siga reservada.</p>
    </section>

    <?php if($mensaje) { ?>
        <div class="alerta <?php echo s($mensaje['tipo']); ?>">
            <?php echo s($mensaje['texto']); ?>
        </div>
    <?php } ?>

    <section class="appointments-list">
        <?php if(empty($citas)) { ?>
            <article class="appointment-card empty-appointment">
                <div>
                    <span class="screen-tag">Sin registros</span>
                    <h2>Aún no tienes citas</h2>
                    <p>Crea una nueva cita para que aparezca en este listado.</p>
                </div>

                <a href="/cita" class="btn btn--primary">Crear cita</a>
            </article>
        <?php } ?>

        <?php foreach($citas as $cita) { ?>
            <?php
                $estado = $cita['estado'];
                $estadoClass = 'status-badge';

                if($estado === 'Reservada') {
                    $estadoClass .= ' status-badge--info';
                } elseif($estado === 'Atendida') {
                    $estadoClass .= ' status-badge--success';
                } elseif($estado === 'Cancelada') {
                    $estadoClass .= ' status-badge--danger';
                } else {
                    $estadoClass .= ' status-badge--warning';
                }

                $fechaLegible = date('d M Y', strtotime($cita['fecha']));
                $horaInicio = substr($cita['hora_inicio'], 0, 5);
                $horaFin = substr($cita['hora_fin'], 0, 5);
            ?>

            <article class="appointment-card">
                <div class="appointment-card__main">
                    <div class="appointment-card__date">
                        <strong><?php echo s($fechaLegible); ?></strong>
                        <span><?php echo s($horaInicio); ?> - <?php echo s($horaFin); ?></span>
                    </div>

                    <div class="appointment-card__services">
                        <span class="label-muted">Servicios</span>
                        <h2><?php echo s($cita['servicios']); ?></h2>

                        <?php if(!empty($cita['observaciones'])) { ?>
                            <p><?php echo s($cita['observaciones']); ?></p>
                        <?php } ?>
                    </div>
                </div>

                <div class="appointment-card__side">
                    <strong class="appointment-total">
                        $<?php echo number_format((float)$cita['total_con_iva'], 2); ?> MXN
                    </strong>

                    <span class="<?php echo $estadoClass; ?>">
                        <?php echo s($estado); ?>
                    </span>

                    <span class="label-muted">
                        Duración: <?php echo (int)$cita['duracion_total']; ?> min
                    </span>

                    <?php if((int)$cita['estado_cita_id'] === 1) { ?>
                        <form action="/mis-citas/cancelar" method="POST" onsubmit="return confirm('¿Solicitar cancelación de esta cita?');">
                            <input type="hidden" name="id" value="<?php echo (int)$cita['id']; ?>">
                            <input type="submit" class="btn btn--danger" value="Solicitar cancelación">
                        </form>
                    <?php } ?>
                </div>
            </article>
        <?php } ?>
    </section>
</div>

<section class="admin-page clientes-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Clientes</span>
            <h1>Administrar clientes</h1>
            <p>Consulta clientes registrados, datos de contacto y actividad de citas.</p>
        </div>

        <a href="/admin/citas" class="btn btn--soft">Ver citas</a>
    </div>

    <form method="GET" action="/clientes" class="clients-filters">
        <div class="campo campo--stack">
            <label for="estado">Estado de cuenta</label>
            <select id="estado" name="estado">
                <option value="" <?php echo $estado === '' ? 'selected' : ''; ?>>Todos</option>
                <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
            </select>
        </div>

        <div class="campo campo--stack">
            <label for="q">Buscar cliente</label>
            <input type="text" id="q" name="q" placeholder="Nombre, correo o teléfono" value="<?php echo s($q); ?>">
        </div>

        <div class="clients-filters__actions">
            <button type="submit" class="btn btn--primary">Filtrar</button>
            <a href="/clientes" class="btn btn--soft">Limpiar</a>
        </div>
    </form>

    <div class="kpi-grid clientes-kpis">
        <article class="kpi-card">
            <span>Total clientes</span>
            <strong><?php echo (int)$metricas['total']; ?></strong>
            <small>Clientes encontrados</small>
        </article>

        <article class="kpi-card">
            <span>Cuentas activas</span>
            <strong><?php echo (int)$metricas['activos']; ?></strong>
            <small>Clientes con cuenta activa</small>
        </article>

        <article class="kpi-card">
            <span>Cuentas confirmadas</span>
            <strong><?php echo (int)$metricas['confirmados']; ?></strong>
            <small>Correos confirmados</small>
        </article>

        <article class="kpi-card">
            <span>Citas próximas</span>
            <strong><?php echo (int)$metricas['citas_proximas']; ?></strong>
            <small>Reservadas desde hoy</small>
        </article>
    </div>

    <section class="clientes-list">
        <?php if(empty($clientes)) { ?>
            <article class="cliente-card cliente-card--empty">
                <span class="screen-tag">Sin resultados</span>
                <h2>No hay clientes con esos filtros</h2>
                <p>Intenta buscar por nombre, correo o teléfono.</p>
            </article>
        <?php } ?>

        <?php foreach($clientes as $cliente) { ?>
            <?php
                $nombreCompleto = trim($cliente['nombre'] . ' ' . $cliente['apellido_paterno'] . ' ' . ($cliente['apellido_materno'] ?? ''));
                $activo = (int)$cliente['activo'] === 1;
                $confirmado = (int)$cliente['cuenta_confirmada'] === 1;
                $fechaNacimiento = $cliente['fecha_nacimiento'] ? date('d/m/Y', strtotime($cliente['fecha_nacimiento'])) : 'Sin fecha';
                $ultimoAcceso = $cliente['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($cliente['ultimo_acceso'])) : 'Sin acceso';
                $ultimaCita = $cliente['ultima_cita'] ? date('d/m/Y', strtotime($cliente['ultima_cita'])) : 'Sin citas';
            ?>

            <article class="cliente-card">
                <div class="cliente-card__avatar">
                    <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                </div>

                <div class="cliente-card__body">
                    <div class="cliente-card__top">
                        <div>
                            <h2><?php echo s($nombreCompleto); ?></h2>
                            <p><?php echo s($cliente['email']); ?> · <?php echo s($cliente['telefono']); ?></p>
                        </div>

                        <div class="cliente-card__badges">
                            <?php if($activo) { ?>
                                <span class="status-badge status-badge--success">Cuenta activa</span>
                            <?php } else { ?>
                                <span class="status-badge status-badge--danger">Cuenta inactiva</span>
                            <?php } ?>

                            <?php if($confirmado) { ?>
                                <span class="status-badge status-badge--info">Confirmada</span>
                            <?php } else { ?>
                                <span class="status-badge status-badge--warning">Sin confirmar</span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="cliente-card__grid">
                        <div>
                            <span>Fecha nacimiento</span>
                            <strong><?php echo s($fechaNacimiento); ?></strong>
                        </div>

                        <div>
                            <span>Registro</span>
                            <strong><?php echo s(date('d/m/Y', strtotime($cliente['created_at']))); ?></strong>
                        </div>

                        <div>
                            <span>Último acceso</span>
                            <strong><?php echo s($ultimoAcceso); ?></strong>
                        </div>

                        <div>
                            <span>Total citas</span>
                            <strong><?php echo (int)$cliente['total_citas']; ?></strong>
                        </div>

                        <div>
                            <span>Citas próximas</span>
                            <strong><?php echo (int)$cliente['citas_proximas']; ?></strong>
                        </div>

                        <div>
                            <span>Cancelaciones solicitadas</span>
                            <strong><?php echo (int)$cliente['cancelaciones_solicitadas']; ?></strong>
                        </div>

                        <div>
                            <span>Última cita</span>
                            <strong><?php echo s($ultimaCita); ?></strong>
                        </div>

                        <div>
                            <span>Total agendado</span>
                            <strong>$<?php echo number_format((float)$cliente['total_agendado'], 2); ?> MXN</strong>
                        </div>
                    </div>

                    <div class="cliente-card__actions">
                        <a href="/admin/citas?q=<?php echo urlencode($cliente['email']); ?>&fecha=todas" class="btn btn--soft">
                            Ver citas
                        </a>
                    </div>
                </div>
            </article>
        <?php } ?>
    </section>
</section>

<section class="admin-page config-page">
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="page-heading">
        <div>
            <span class="screen-tag">Configuración</span>
            <h1>Sucursal</h1>
            <p>Administra los datos principales del negocio y el bloque de agenda.</p>
        </div>

        <a href="/admin" class="btn btn--soft">Volver al dashboard</a>
    </div>

    <?php if($mensaje) { ?>
        <div class="alerta <?php echo s($mensaje['tipo']); ?>">
            <?php echo s($mensaje['texto']); ?>
        </div>
    <?php } ?>

    <?php if(!empty($alertas)) { ?>
        <?php foreach($alertas as $tipo => $mensajes) { ?>
            <?php foreach($mensajes as $mensajeAlerta) { ?>
                <div class="alerta <?php echo s($tipo); ?>">
                    <?php echo s($mensajeAlerta); ?>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>

    <div class="config-layout">
        <form method="POST" action="/configuracion" class="formulario form-panel config-form">
            <div class="data-card__header">
                <div>
                    <h2>Datos del negocio</h2>
                    <p>Estos datos alimentan la configuración principal de la sucursal.</p>
                </div>

                <span class="status-badge status-badge--success">Sucursal activa</span>
            </div>

            <div class="form-grid">
                <div class="campo campo--stack">
                    <label for="nombre_comercial">Nombre del negocio</label>
                    <input
                        type="text"
                        id="nombre_comercial"
                        name="nombre_comercial"
                        value="<?php echo s($sucursal['nombre_comercial'] ?? ''); ?>"
                        placeholder="BarberShop Pro"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="razon_social">Razón social</label>
                    <input
                        type="text"
                        id="razon_social"
                        name="razon_social"
                        value="<?php echo s($sucursal['razon_social'] ?? ''); ?>"
                        placeholder="Opcional"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="telefono">Teléfono</label>
                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        value="<?php echo s($sucursal['telefono'] ?? ''); ?>"
                        placeholder="8112345678"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="email">Correo de contacto</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo s($sucursal['email'] ?? ''); ?>"
                        placeholder="contacto@barbershop.com"
                    >
                </div>

                <div class="campo campo--stack form-grid__full">
                    <label for="bloque_agenda_minutos">Bloque de agenda</label>
                    <select id="bloque_agenda_minutos" name="bloque_agenda_minutos">
                        <option value="15" <?php echo ((int)($sucursal['bloque_agenda_minutos'] ?? 15) === 15) ? 'selected' : ''; ?>>
                            15 minutos
                        </option>
                        <option value="20" <?php echo ((int)($sucursal['bloque_agenda_minutos'] ?? 15) === 20) ? 'selected' : ''; ?>>
                            20 minutos
                        </option>
                    </select>
                    <small>Este valor se usa al crear citas y generar bloques ocupados en agenda.</small>
                </div>

                <div class="config-section-title form-grid__full">
                    <span>Dirección</span>
                    <p>Información física de la sucursal.</p>
                </div>

                <div class="campo campo--stack">
                    <label for="calle">Calle</label>
                    <input
                        type="text"
                        id="calle"
                        name="calle"
                        value="<?php echo s($sucursal['calle'] ?? ''); ?>"
                        placeholder="Av. Principal"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="numero_exterior">Número exterior</label>
                    <input
                        type="text"
                        id="numero_exterior"
                        name="numero_exterior"
                        value="<?php echo s($sucursal['numero_exterior'] ?? ''); ?>"
                        placeholder="123"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="numero_interior">Número interior</label>
                    <input
                        type="text"
                        id="numero_interior"
                        name="numero_interior"
                        value="<?php echo s($sucursal['numero_interior'] ?? ''); ?>"
                        placeholder="Opcional"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="colonia">Colonia</label>
                    <input
                        type="text"
                        id="colonia"
                        name="colonia"
                        value="<?php echo s($sucursal['colonia'] ?? ''); ?>"
                        placeholder="Centro"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="municipio">Municipio</label>
                    <input
                        type="text"
                        id="municipio"
                        name="municipio"
                        value="<?php echo s($sucursal['municipio'] ?? ''); ?>"
                        placeholder="Monterrey"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="estado">Estado</label>
                    <input
                        type="text"
                        id="estado"
                        name="estado"
                        value="<?php echo s($sucursal['estado'] ?? ''); ?>"
                        placeholder="Nuevo León"
                    >
                </div>

                <div class="campo campo--stack">
                    <label for="codigo_postal">Código postal</label>
                    <input
                        type="text"
                        id="codigo_postal"
                        name="codigo_postal"
                        value="<?php echo s($sucursal['codigo_postal'] ?? ''); ?>"
                        placeholder="64000"
                        maxlength="5"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Guardar configuración</button>
            </div>
        </form>

        <aside class="config-summary">
            <span class="screen-tag">Vista rápida</span>
            <h2><?php echo s($sucursal['nombre_comercial'] ?? 'BarberShop'); ?></h2>
            <p><?php echo s($sucursal['razon_social'] ?? ''); ?></p>

            <div class="config-summary__box">
                <span>Teléfono</span>
                <strong><?php echo s($sucursal['telefono'] ?? 'Sin teléfono'); ?></strong>
            </div>

            <div class="config-summary__box">
                <span>Correo</span>
                <strong><?php echo s($sucursal['email'] ?? 'Sin correo'); ?></strong>
            </div>

            <div class="config-summary__box">
                <span>Soporte técnico</span>
                <strong>8112345678</strong>
                <small>Para cualquier issue técnico escribe a uprivado2022@gmail.com</small>
            </div>

            <div class="config-summary__box">
                <span>Dirección</span>
                <strong>
                    <?php echo s(trim(($sucursal['calle'] ?? '') . ' ' . ($sucursal['numero_exterior'] ?? ''))); ?>
                    <?php if(!empty($sucursal['numero_interior'])) { ?>
                        Int. <?php echo s($sucursal['numero_interior']); ?>
                    <?php } ?>
                </strong>
                <small>
                    <?php echo s(($sucursal['colonia'] ?? '') . ', ' . ($sucursal['municipio'] ?? '') . ', ' . ($sucursal['estado'] ?? '') . ' C.P. ' . ($sucursal['codigo_postal'] ?? '')); ?>
                </small>
            </div>

            <div class="config-summary__agenda">
                <span>Bloque de agenda</span>
                <strong><?php echo (int)($sucursal['bloque_agenda_minutos'] ?? 15); ?> min</strong>
                <p>
                    Si cambias esto, las nuevas citas se validarán y generarán bloques con esta duración.
                </p>
            </div>
            <div class="config-summary__box">
                <span>Versión del sistema</span>
                <strong>3.1</strong>
            </div>
        </aside>
    </div>
</section>

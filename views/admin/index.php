<h1 class="nombre-pagina">Dashboard</h1>
<p class="descripcion-pagina">Resumen de actividad del día</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div class="busqueda">
    <form class="formulario" method="GET" action="/admin">
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo s($fecha); ?>">
        </div>

        <input type="submit" class="boton" value="Filtrar">
    </form>
</div>

<div class="servicios">
    <li>
        <p>Citas de hoy: <span><?php echo (int)$metricas['citas_hoy']; ?></span></p>
    </li>
    <li>
        <p>Ventas del día: <span>$<?php echo number_format($metricas['ventas_dia'], 2); ?> MXN</span></p>
    </li>
    <li>
        <p>Productos bajo stock: <span><?php echo (int)$metricas['productos_bajo_stock']; ?></span></p>
    </li>
    <li>
        <p>Clientes registrados: <span><?php echo (int)$metricas['clientes_registrados']; ?></span></p>
    </li>
</div>

<h2>Próximas citas</h2>

<ul class="citas">
    <?php if(empty($proximasCitas)) { ?>
        <li>
            <p>No hay citas para esta fecha</p>
        </li>
    <?php } ?>

    <?php foreach($proximasCitas as $cita) { ?>
        <li>
            <p>Hora: <span><?php echo s(substr($cita['hora_inicio'], 0, 5)); ?></span></p>
            <p>Cliente: <span><?php echo s($cita['cliente']); ?></span></p>
            <p>Servicios: <span><?php echo s($cita['servicios']); ?></span></p>
            <p>Estado: <span><?php echo s($cita['estado']); ?></span></p>
        </li>
    <?php } ?>
</ul>

<h2>Productos con bajo stock</h2>

<ul class="citas">
    <?php if(empty($productosBajoStock)) { ?>
        <li>
            <p>No hay productos bajo stock</p>
        </li>
    <?php } ?>

    <?php foreach($productosBajoStock as $producto) { ?>
        <li>
            <p>Producto: <span><?php echo s($producto['nombre']); ?></span></p>
            <p>Stock actual: <span><?php echo (int)$producto['stock_total']; ?></span></p>
            <p>Stock mínimo: <span><?php echo (int)$producto['stock_minimo']; ?></span></p>
        </li>
    <?php } ?>
</ul>

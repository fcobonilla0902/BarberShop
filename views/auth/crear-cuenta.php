<h1 class="nombre-pagina">Crear Cuenta</h1>
<p class="descripcion-pagina">Llena el formulario para registrarte como cliente</p>

<?php include_once __DIR__ . "/../templates/alertas.php"; ?>

<form action="/crear-cuenta" method="POST" class="formulario">

    <div class="campo">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" value="<?php echo s($cliente->nombre ?? ''); ?>">
    </div>

    <div class="campo">
        <label for="apellido_paterno">Apellido paterno</label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" placeholder="Apellido paterno" value="<?php echo s($cliente->apellido_paterno ?? ''); ?>">
    </div>

    <div class="campo">
        <label for="apellido_materno">Apellido materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno" placeholder="Apellido materno opcional" value="<?php echo s($cliente->apellido_materno ?? ''); ?>">
    </div>

    <div class="campo">
        <label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" placeholder="Tu teléfono" value="<?php echo s($cliente->telefono ?? ''); ?>">
    </div>

    <div class="campo">
        <label for="fecha_nacimiento">Fecha nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo s($cliente->fecha_nacimiento ?? ''); ?>">
    </div>

    <div class="campo">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" value="<?php echo s($cuenta->email ?? ''); ?>">
    </div>

    <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres">
    </div>

    <div class="campo">
        <label for="password2">Confirmar</label>
        <input type="password" id="password2" name="password2" placeholder="Repite tu contraseña">
    </div>

    <input type="submit" class="boton" value="Crear Cuenta">
</form>

<div class="acciones">
    <a href="/">¿Ya tienes una cuenta? Inicia sesión</a>
    <a href="/olvide">¿Olvidaste tu password?</a>
</div>

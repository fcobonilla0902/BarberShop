<div class="auth-header">
    <span class="screen-tag">Registro</span>
    <h1>Crear Cuenta</h1>
    <p>Regístrate como cliente para poder agendar tus citas.</p>
</div>

<?php include_once __DIR__ . "/../templates/alertas.php"; ?>

<form action="/crear-cuenta" method="POST" class="formulario auth-form form-grid">

    <div class="campo campo--stack">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" value="<?php echo s($cliente->nombre ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="apellido_paterno">Apellido paterno</label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" placeholder="Apellido paterno" value="<?php echo s($cliente->apellido_paterno ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="apellido_materno">Apellido materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno" placeholder="Opcional" value="<?php echo s($cliente->apellido_materno ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" placeholder="8112345678" value="<?php echo s($cliente->telefono ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="fecha_nacimiento">Fecha nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo s($cliente->fecha_nacimiento ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" value="<?php echo s($cuenta->email ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres">
    </div>

    <div class="campo campo--stack">
        <label for="password2">Confirmar contraseña</label>
        <input type="password" id="password2" name="password2" placeholder="Repite tu contraseña">
    </div>

    <input type="submit" class="btn btn--primary btn--full form-grid__full" value="Crear Cuenta">
</form>

<div class="auth-links">
    <a href="/">¿Ya tienes cuenta? Inicia sesión</a>
    <a href="/olvide">¿Olvidaste tu password?</a>
</div>

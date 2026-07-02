<div class="auth-header">
    <span class="screen-tag">Recuperación</span>
    <h1>Olvidé mi password</h1>
    <p>Escribe tu correo para recibir instrucciones.</p>
</div>

<?php include_once __DIR__ . "/../templates/alertas.php"; ?>

<form action="/olvide" method="POST" class="formulario auth-form">
    <div class="campo campo--stack">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com">
    </div>

    <input type="submit" class="btn btn--primary btn--full" value="Enviar instrucciones">
</form>

<div class="auth-links">
    <a href="/">Iniciar sesión</a>
    <a href="/crear-cuenta">Crear cuenta</a>
</div>

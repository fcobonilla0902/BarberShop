<div class="auth-header">
    <span class="screen-tag">Acceso</span>
    <h1>Iniciar sesión</h1>
    <p>Entra como cliente o administrador para continuar.</p>
</div>

<?php include_once __DIR__ . "/../templates/alertas.php"; ?>

<form action="/" method="POST" class="formulario auth-form">
    <div class="campo campo--stack">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com">
    </div>

    <div class="campo campo--stack">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" class="js-password-field" placeholder="Tu contraseña">
    </div>

    <label class="password-toggle">
        <input type="checkbox" class="js-toggle-password">
        <span>Mostrar contraseña</span>
    </label>

    <input type="submit" class="btn btn--primary btn--full" value="Entrar">

    <div class="demo-actions">
        <button class="btn btn--soft" type="submit" name="demo" value="cliente">Demo Cliente</button>
        <button class="btn btn--soft" type="submit" name="demo" value="admin">Demo Admin</button>
    </div>
</form>

<div class="auth-links">
    <a href="/crear-cuenta">Crear cuenta</a>
    <a href="/olvide">Recuperar contraseña</a>
</div>

<script>
(function() {
    const toggle = document.querySelector('.js-toggle-password');
    const fields = document.querySelectorAll('.js-password-field');

    if(!toggle || fields.length === 0) return;

    toggle.addEventListener('change', function() {
        fields.forEach(field => {
            field.type = toggle.checked ? 'text' : 'password';
        });
    });
})();
</script>

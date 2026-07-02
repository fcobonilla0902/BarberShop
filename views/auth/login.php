<h1 class="nombre-pagina">Login</h1>
<p class="descripcion-pagina">Inicia sesión con tus datos</p>

<?php include_once __DIR__ . "/../templates/alertas.php"; ?>

<form action="/" method="POST" class="formulario">
    <div class="campo">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com">
    </div>

    <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Tu contraseña">
    </div>

    <input type="submit" class="boton" value="Iniciar Sesión">

    <div class="acciones">
        <button class="boton" type="submit" name="demo" value="cliente">Demo Cliente</button>
        <button class="boton" type="submit" name="demo" value="admin">Demo Admin</button>
    </div>
</form>

<div class="acciones">
    <a href="/crear-cuenta">Crear una cuenta</a>
    <a href="/olvide">¿Olvidaste tu password?</a>
</div>

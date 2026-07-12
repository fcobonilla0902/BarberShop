<div class="auth-header">
    <span class="screen-tag">Registro</span>
    <h1>Crear cuenta</h1>
    <p>Regístrate como cliente para poder agendar tus citas.</p>
</div>

<?php include_once __DIR__ . "/../templates/alertas.php"; ?>

<form action="/crear-cuenta" method="POST" class="formulario auth-form form-grid" id="crear-cuenta-form">

    <div class="campo campo--stack">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" maxlength="60" required value="<?php echo s($cliente->nombre ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="apellido_paterno">Apellido paterno</label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" placeholder="Apellido paterno" maxlength="60" required value="<?php echo s($cliente->apellido_paterno ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="apellido_materno">Apellido materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno" placeholder="Opcional" maxlength="60" value="<?php echo s($cliente->apellido_materno ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" placeholder="8112345678" inputmode="numeric" maxlength="10" required value="<?php echo s($cliente->telefono ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="fecha_nacimiento">Fecha nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo date('Y-m-d'); ?>" required value="<?php echo s($cliente->fecha_nacimiento ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" maxlength="120" required value="<?php echo s($cuenta->email ?? ''); ?>">
    </div>

    <div class="campo campo--stack">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" class="js-password-field" placeholder="Mínimo 6 caracteres" minlength="6" required>
    </div>

    <div class="campo campo--stack">
        <label for="password2">Confirmar contraseña</label>
        <input type="password" id="password2" name="password2" class="js-password-field" placeholder="Repite tu contraseña" minlength="6" required>
    </div>

    <label class="password-toggle form-grid__full">
        <input type="checkbox" class="js-toggle-password">
        <span>Mostrar contraseñas</span>
    </label>

    <input type="submit" class="btn btn--primary btn--full form-grid__full" value="Crear cuenta">
</form>

<div class="auth-links">
    <a href="/">¿Ya tienes cuenta? Inicia sesión</a>
    <a href="/olvide">Recuperar contraseña</a>
</div>

<script>
(function() {
    const form = document.querySelector('#crear-cuenta-form');
    const toggle = document.querySelector('.js-toggle-password');
    const fields = document.querySelectorAll('.js-password-field');
    const nombre = document.querySelector('#nombre');
    const apellidoPaterno = document.querySelector('#apellido_paterno');
    const apellidoMaterno = document.querySelector('#apellido_materno');
    const telefono = document.querySelector('#telefono');
    const fechaNacimiento = document.querySelector('#fecha_nacimiento');
    const email = document.querySelector('#email');
    const password = document.querySelector('#password');
    const password2 = document.querySelector('#password2');

    const soloLetras = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g;

    if(toggle && fields.length > 0) {
        toggle.addEventListener('change', function() {
            fields.forEach(field => {
                field.type = toggle.checked ? 'text' : 'password';
            });
        });
    }

    function limpiarLetras(input) {
        if(!input) return;
        input.addEventListener('input', function() {
            const limpio = input.value.replace(soloLetras, '');
            if(input.value !== limpio) input.value = limpio;
        });
    }

    function limpiarAlertas() {
        form?.querySelectorAll('.alerta-js').forEach(alerta => alerta.remove());
    }

    function mostrarErrores(errores) {
        limpiarAlertas();
        if(!form || errores.length === 0) return;

        const alerta = document.createElement('div');
        alerta.className = 'alerta error alerta-js form-grid__full';
        alerta.innerHTML = errores.map(error => `<div>${error}</div>`).join('');
        form.prepend(alerta);
        alerta.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function fechaValida(valor) {
        if(!valor) return false;
        const fecha = new Date(valor + 'T00:00:00');
        return !Number.isNaN(fecha.getTime());
    }

    function validarNombre(valor, campo, requerido = true) {
        const texto = valor.trim();

        if(!requerido && texto === '') return null;

        if(texto.length < 2) {
            return `${campo} debe tener al menos 2 letras.`;
        }

        if(!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(texto)) {
            return `${campo} solo debe contener letras y espacios.`;
        }

        return null;
    }

    function validarFormulario(event) {
        const errores = [];
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        [
            validarNombre(nombre.value, 'El nombre'),
            validarNombre(apellidoPaterno.value, 'El apellido paterno'),
            validarNombre(apellidoMaterno.value, 'El apellido materno', false)
        ].forEach(error => {
            if(error) errores.push(error);
        });

        if(!/^\d{10}$/.test(telefono.value.trim())) {
            errores.push('El teléfono debe tener exactamente 10 dígitos.');
        }

        if(!fechaValida(fechaNacimiento.value)) {
            errores.push('La fecha de nacimiento no es válida.');
        } else {
            const fecha = new Date(fechaNacimiento.value + 'T00:00:00');
            const fechaMinima = new Date();
            fechaMinima.setFullYear(fechaMinima.getFullYear() - 100);
            fechaMinima.setHours(0, 0, 0, 0);

            if(fecha > hoy) {
                errores.push('La fecha de nacimiento no puede ser futura.');
            }

            if(fecha < fechaMinima) {
                errores.push('La fecha de nacimiento no parece válida.');
            }
        }

        if(!email.value.trim() || !email.checkValidity()) {
            errores.push('Escribe un correo electrónico válido.');
        }

        if(password.value.length < 6) {
            errores.push('La contraseña debe tener al menos 6 caracteres.');
        }

        if(password.value !== password2.value) {
            errores.push('Las contraseñas no coinciden.');
        }

        if(errores.length > 0) {
            event.preventDefault();
            mostrarErrores(errores);
        } else {
            limpiarAlertas();
        }
    }

    [nombre, apellidoPaterno, apellidoMaterno].forEach(limpiarLetras);

    if(telefono) {
        telefono.addEventListener('input', function() {
            telefono.value = telefono.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    if(email) {
        email.addEventListener('input', function() {
            email.value = email.value.trim().toLowerCase();
        });
    }

    if(form) {
        form.addEventListener('submit', validarFormulario);
    }
})();
</script>

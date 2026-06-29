# BarberShop / AppSalon PHP MVC

Proyecto PHP MVC para administración de citas de barbería/salón.

## Requisitos

Antes de correr el proyecto, tener instalado:

* Docker Desktop
* Docker Compose
* Git

No es necesario instalar PHP, Composer, MySQL ni Node directamente en la máquina, porque se levantan con Docker.

---

## Puertos usados

| Servicio               | URL / Puerto          |
| ---------------------- | --------------------- |
| App PHP                | http://localhost:8000 |
| phpMyAdmin             | http://localhost:8080 |
| MySQL desde host       | localhost:3307        |
| MySQL dentro de Docker | db:3306               |

---

## Variables de entorno

Crear el archivo:

```bash
includes/.env
```

Contenido mínimo:

```env
DB_HOST=db
DB_USER=appsalon
DB_PASS=appsalon123
DB_NAME=appsalon_mvc

APP_URL=http://localhost:8000

EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=tu_correo@gmail.com
EMAIL_PASS=tu_password_de_aplicacion_google
EMAIL_FROM=tu_correo@gmail.com
EMAIL_FROM_NAME=BarberShop
```

Notas:

* `DB_HOST` debe ser `db`, no `localhost`, porque PHP corre dentro de Docker.
* No subir `includes/.env` al repositorio.
* Para correos con Gmail, usar una contraseña de aplicación, no la contraseña normal de Gmail.
* Si no se configurará correo todavía, se puede confirmar usuarios manualmente con SQL.

---

## Levantar el proyecto

Primera vez:

```bash
docker compose up --build
```

Después, para levantar normal:

```bash
docker compose up
```

O en segundo plano:

```bash
docker compose up -d
```

Para apagar:

```bash
docker compose down
```

Para apagar y borrar la base local completamente:

```bash
docker compose down -v
```

Cuidado: `-v` borra el volumen de MySQL y se pierde la data local.

---

## Acceder a la app

Abrir:

```txt
http://localhost:8000
```

phpMyAdmin:

```txt
http://localhost:8080
```

Credenciales phpMyAdmin:

```txt
Servidor: db
Usuario: appsalon
Password: appsalon123
```

También se puede entrar como root:

```txt
Usuario: root
Password: root
```

---

## Base de datos

El proyecto usa la base:

```txt
appsalon_mvc
```

El archivo `database.sql` se importa automáticamente la primera vez que se crea el volumen de MySQL.

Importante: si ya existe el volumen `db_data`, Docker no vuelve a importar `database.sql`.

Para reiniciar la base desde cero:

```bash
docker compose down -v
docker compose up --build
```

---

## Script recomendado después de importar la base

El campo `telefono` viene como `VARCHAR(10)`, por eso puede fallar si se captura un teléfono con más de 10 caracteres.

Ejecutar en phpMyAdmin o en MySQL:

```sql
ALTER TABLE usuarios 
MODIFY telefono VARCHAR(20) DEFAULT NULL;
```

---

## Crear usuario manualmente

La app permite crear usuarios desde:

```txt
http://localhost:8000/crear-cuenta
```

Pero por defecto el usuario queda sin confirmar:

```txt
confirmado = 0
admin = 0
```

Si el correo no está configurado, confirmar manualmente:

```sql
UPDATE usuarios
SET confirmado = 1, token = ''
WHERE email = 'correo@ejemplo.com';
```

Para hacerlo admin:

```sql
UPDATE usuarios
SET admin = 1
WHERE email = 'correo@ejemplo.com';
```

También se puede hacer ambas cosas en una sola consulta:

```sql
UPDATE usuarios
SET confirmado = 1, admin = 1, token = ''
WHERE email = 'correo@ejemplo.com';
```

---

## Crear admin local directo

Password: `123456`

```sql
INSERT INTO usuarios 
(nombre, apellido, email, password, telefono, admin, confirmado, token)
VALUES
(
  'Admin',
  'Local',
  'admin.local@correo.com',
  '$2y$12$1SH/FqJgwPu2oS94yo3J4.0//w9MwpJ8dIQ8CkTdo3VJEJr.bEPQG',
  '8111111111',
  1,
  1,
  ''
);
```

Login:

```txt
Email: admin.local@correo.com
Password: 123456
```

---

## Resetear contraseña de un usuario

Password nuevo: `123456`

```sql
UPDATE usuarios
SET password = '$2y$12$1SH/FqJgwPu2oS94yo3J4.0//w9MwpJ8dIQ8CkTdo3VJEJr.bEPQG'
WHERE email = 'correo@ejemplo.com';
```

---

## Confirmar usuario creado desde la app

Después de crear cuenta desde `/crear-cuenta`, si no llega correo:

```sql
UPDATE usuarios
SET confirmado = 1, token = ''
WHERE email = 'correo@ejemplo.com';
```

Si también debe ser admin:

```sql
UPDATE usuarios
SET confirmado = 1, admin = 1, token = ''
WHERE email = 'correo@ejemplo.com';
```

---

## Correos con Gmail

Para que funcione el envío de confirmación y recuperación de password:

1. Activar verificación en dos pasos en la cuenta de Google.
2. Crear una contraseña de aplicación.
3. Usar esa contraseña en `EMAIL_PASS`.

Ejemplo:

```env
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=tu_correo@gmail.com
EMAIL_PASS=clave_de_aplicacion_google
EMAIL_FROM=tu_correo@gmail.com
EMAIL_FROM_NAME=BarberShop
```

Si se modificó `Email.php` para Gmail, verificar que use STARTTLS y que el `setFrom` use variables de entorno:

```php
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = $_ENV['EMAIL_HOST'];
$mail->SMTPAuth = true;
$mail->Port = $_ENV['EMAIL_PORT'];
$mail->Username = $_ENV['EMAIL_USER'];
$mail->Password = $_ENV['EMAIL_PASS'];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

$mail->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME'] ?? 'BarberShop');
```

---

## Desarrollo local con cambios en vivo

El `docker-compose.yml` debe tener bind mount:

```yaml
volumes:
  - .:/var/www/html
  - /var/www/html/vendor
```

Con eso, los cambios en archivos PHP se reflejan con solo refrescar el navegador.

No es necesario correr `docker compose down` y `docker compose up --build` por cada cambio.

### Cambios en PHP

Modificar archivos como:

```txt
controllers/
models/
views/
classes/
includes/
```

Luego refrescar navegador.

### Cambios en SCSS o JS

El proyecto usa Gulp.

Si el servicio `assets` está configurado en Docker Compose, correr:

```bash
docker compose up
```

Y Gulp quedará observando cambios.

Para compilar manualmente:

```bash
docker compose exec assets npx gulp css js imagenes
```

---

## Cuándo reconstruir Docker

Reconstruir solo si cambian cosas como:

* `Dockerfile`
* `composer.json`
* extensiones PHP
* dependencias del sistema
* configuración fuerte del contenedor

Comando:

```bash
docker compose up --build
```

---

## Problemas comunes

### Docker daemon no está corriendo

Error:

```txt
Cannot connect to the Docker daemon
```

Solución:

Abrir Docker Desktop y esperar a que esté activo.

---

### Composer falla por zip/unzip

Error parecido a:

```txt
The zip extension and unzip/7z commands are both missing
```

Solución:

Verificar que el Dockerfile instale:

```dockerfile
unzip
zip
libzip-dev
git
```

Y extensiones:

```dockerfile
docker-php-ext-install mysqli zip
```

---

### Teléfono demasiado largo

Error:

```txt
Data too long for column 'telefono'
```

Solución:

```sql
ALTER TABLE usuarios 
MODIFY telefono VARCHAR(20) DEFAULT NULL;
```

---

### Usuario no puede entrar

Revisar que tenga:

```txt
confirmado = 1
```

Consulta rápida:

```sql
UPDATE usuarios
SET confirmado = 1, token = ''
WHERE email = 'correo@ejemplo.com';
```

Si debe ser admin:

```sql
UPDATE usuarios
SET confirmado = 1, admin = 1, token = ''
WHERE email = 'correo@ejemplo.com';
```

---

### Reimportar database.sql

Si se cambió `database.sql`, recordar que Docker no lo reimporta si el volumen ya existe.

Para forzar reimportación:

```bash
docker compose down -v
docker compose up --build
```

---

## Comandos útiles

Ver contenedores:

```bash
docker compose ps
```

Ver logs de la app:

```bash
docker compose logs -f app
```

Ver logs de MySQL:

```bash
docker compose logs -f db
```

Entrar al contenedor PHP:

```bash
docker compose exec app bash
```

Entrar a MySQL desde terminal:

```bash
docker compose exec db mysql -uappsalon -pappsalon123 appsalon_mvc
```

Ejecutar SQL rápido desde terminal:

```bash
docker compose exec db mysql -uappsalon -pappsalon123 appsalon_mvc -e "SELECT id, nombre, email, admin, confirmado FROM usuarios;"
```

---

## Credenciales locales recomendadas

Admin local recomendado:

```txt
Email: admin.local@correo.com
Password: 123456
```

Si no existe, crearlo con el script de la sección “Crear admin local directo”.

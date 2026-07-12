<?php

namespace Model;

class Cuenta extends ActiveRecord {
    protected static $tabla = 'cuentas';
    protected static $columnasDB = [
        'id',
        'email',
        'password_hash',
        'activa',
        'cuenta_confirmada',
        'ultimo_acceso',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $email;
    public $password_hash;
    public $activa;
    public $cuenta_confirmada;
    public $ultimo_acceso;
    public $created_at;
    public $updated_at;

    // Campos de formulario, no pertenecen a la tabla
    public $password;
    public $password2;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->email = $args['email'] ?? '';
        $this->password_hash = $args['password_hash'] ?? '';
        $this->activa = $args['activa'] ?? '1';
        $this->cuenta_confirmada = $args['cuenta_confirmada'] ?? '0';
        $this->ultimo_acceso = $args['ultimo_acceso'] ?? null;
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');

        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
    }

    public function validarLogin() {
        static::$alertas = [];

        if(!$this->email) {
            static::$alertas['error'][] = 'El correo electrónico es obligatorio';
        }

        if(!$this->password) {
            static::$alertas['error'][] = 'La contraseña es obligatoria';
        }

        return static::$alertas;
    }

    public function validarNuevaCuenta() {
        static::$alertas = [];

        if(!$this->email) {
            static::$alertas['error'][] = 'El correo electrónico es obligatorio';
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            static::$alertas['error'][] = 'El correo electrónico no es válido';
        }

        if(!$this->password) {
            static::$alertas['error'][] = 'La contraseña es obligatoria';
        }

        if(strlen($this->password) < 6) {
            static::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if(!$this->password2) {
            static::$alertas['error'][] = 'La confirmación de contraseña es obligatoria';
        }

        if($this->password !== $this->password2) {
            static::$alertas['error'][] = 'Las contraseñas no coinciden';
        }

        return static::$alertas;
    }

    public function validarEmail() {
        static::$alertas = [];

        if(!$this->email) {
            static::$alertas['error'][] = 'El correo electrónico es obligatorio';
        }

        if($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            static::$alertas['error'][] = 'El correo electrónico no es válido';
        }

        return static::$alertas;
    }

    public function existeEmail() {
        $email = self::$db->escape_string($this->email);
        $query = "SELECT id FROM " . static::$tabla . " WHERE email = '{$email}' LIMIT 1";
        $resultado = self::$db->query($query);

        if($resultado && $resultado->num_rows) {
            static::$alertas['error'][] = 'El correo electrónico ya está registrado';
        }

        return $resultado;
    }

    public function hashPassword() {
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function comprobarPasswordAndVerificado($password) {
        $resultado = password_verify($password, $this->password_hash);

        if(!$resultado) {
            static::$alertas['error'][] = 'Contraseña incorrecta';
            return false;
        }

        if((string)$this->activa !== '1') {
            static::$alertas['error'][] = 'La cuenta está desactivada';
            return false;
        }

        if((string)$this->cuenta_confirmada !== '1') {
            static::$alertas['error'][] = 'Aún no has confirmado tu cuenta';
            return false;
        }

        return true;
    }

    public function actualizarUltimoAcceso() {
        $this->ultimo_acceso = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->guardar();
    }

    public static function crearTokenPlano() {
        return bin2hex(random_bytes(32));
    }

    public static function hashToken($token) {
        return hash('sha256', $token);
    }
}

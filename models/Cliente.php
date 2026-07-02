<?php

namespace Model;

class Cliente extends ActiveRecord {
    protected static $tabla = 'clientes';
    protected static $columnasDB = [
        'id',
        'cuenta_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'fecha_nacimiento',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $cuenta_id;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $fecha_nacimiento;
    public $created_at;
    public $updated_at;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cuenta_id = $args['cuenta_id'] ?? '';
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido_paterno = $args['apellido_paterno'] ?? '';
        $this->apellido_materno = $args['apellido_materno'] ?? null;
        $this->telefono = $args['telefono'] ?? '';
        $this->fecha_nacimiento = $args['fecha_nacimiento'] ?? null;
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function validarNuevaCuenta() {
        static::$alertas = [];

        if(!$this->nombre) {
            static::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->apellido_paterno) {
            static::$alertas['error'][] = 'El apellido paterno es obligatorio';
        }

        if(!$this->telefono) {
            static::$alertas['error'][] = 'El teléfono es obligatorio';
        }

        if($this->telefono && strlen($this->telefono) > 20) {
            static::$alertas['error'][] = 'El teléfono no debe superar 20 caracteres';
        }

        return static::$alertas;
    }

    public function nombreCompleto() {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . ($this->apellido_materno ?? ''));
    }
}

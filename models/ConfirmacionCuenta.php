<?php

namespace Model;

class ConfirmacionCuenta extends ActiveRecord {
    protected static $tabla = 'confirmaciones_cuenta';
    protected static $columnasDB = [
        'id',
        'cuenta_id',
        'token_hash',
        'fecha_creacion',
        'fecha_expiracion',
        'fecha_confirmacion',
        'usado'
    ];

    public $id;
    public $cuenta_id;
    public $token_hash;
    public $fecha_creacion;
    public $fecha_expiracion;
    public $fecha_confirmacion;
    public $usado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cuenta_id = $args['cuenta_id'] ?? '';
        $this->token_hash = $args['token_hash'] ?? '';
        $this->fecha_creacion = $args['fecha_creacion'] ?? date('Y-m-d H:i:s');
        $this->fecha_expiracion = $args['fecha_expiracion'] ?? date('Y-m-d H:i:s', strtotime('+1 day'));
        $this->fecha_confirmacion = $args['fecha_confirmacion'] ?? null;
        $this->usado = $args['usado'] ?? '0';
    }

    public function estaVigente() {
        return (string)$this->usado !== '1' && strtotime($this->fecha_expiracion) >= time();
    }
}

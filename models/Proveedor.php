<?php

namespace Model;

class Proveedor extends ActiveRecord {
    protected static $tabla = 'proveedores';
    protected static $columnasDB = [
        'id',
        'nombre_comercial',
        'telefono',
        'email',
        'activo',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $nombre_comercial;
    public $telefono;
    public $email;
    public $activo;
    public $created_at;
    public $updated_at;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre_comercial = $args['nombre_comercial'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->activo = $args['activo'] ?? '1';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public static function activos() {
        return self::SQL("SELECT * FROM " . static::$tabla . " WHERE activo = 1 ORDER BY nombre_comercial ASC");
    }
}

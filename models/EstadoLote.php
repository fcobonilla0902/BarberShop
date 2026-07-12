<?php

namespace Model;

class EstadoLote extends ActiveRecord {
    protected static $tabla = 'estados_lote';
    protected static $columnasDB = [
        'id',
        'nombre',
        'descripcion',
        'activo'
    ];

    public $id;
    public $nombre;
    public $descripcion;
    public $activo;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->activo = $args['activo'] ?? '1';
    }

    public static function activos() {
        return self::SQL("SELECT * FROM " . static::$tabla . " WHERE activo = 1 ORDER BY id ASC");
    }
}

<?php

namespace Model;

class CategoriaServicio extends ActiveRecord {
    protected static $tabla = 'categorias_servicio';
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

    public static function activas() {
        $query = "SELECT * FROM " . static::$tabla . " WHERE activo = 1 ORDER BY nombre ASC";
        return self::SQL($query);
    }
}

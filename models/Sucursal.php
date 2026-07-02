<?php

namespace Model;

class Sucursal extends ActiveRecord {
    protected static $tabla = 'sucursales';
    protected static $columnasDB = [
        'id',
        'nombre_comercial',
        'razon_social',
        'telefono',
        'email',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'municipio',
        'estado',
        'codigo_postal',
        'bloque_agenda_minutos',
        'activo',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $nombre_comercial;
    public $razon_social;
    public $telefono;
    public $email;
    public $calle;
    public $numero_exterior;
    public $numero_interior;
    public $colonia;
    public $municipio;
    public $estado;
    public $codigo_postal;
    public $bloque_agenda_minutos;
    public $activo;
    public $created_at;
    public $updated_at;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre_comercial = $args['nombre_comercial'] ?? '';
        $this->razon_social = $args['razon_social'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->calle = $args['calle'] ?? '';
        $this->numero_exterior = $args['numero_exterior'] ?? '';
        $this->numero_interior = $args['numero_interior'] ?? null;
        $this->colonia = $args['colonia'] ?? '';
        $this->municipio = $args['municipio'] ?? '';
        $this->estado = $args['estado'] ?? '';
        $this->codigo_postal = $args['codigo_postal'] ?? '';
        $this->bloque_agenda_minutos = $args['bloque_agenda_minutos'] ?? 15;
        $this->activo = $args['activo'] ?? '1';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public static function principal() {
        $query = "SELECT * FROM " . static::$tabla . " WHERE activo = 1 ORDER BY id ASC LIMIT 1";
        $resultado = self::SQL($query);
        return array_shift($resultado);
    }
}

<?php

namespace Model;

class Servicio extends ActiveRecord {
    protected static $tabla = 'servicios';
    protected static $columnasDB = [
        'id',
        'categoria_servicio_id',
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio_base_sin_iva',
        'costo_estimado_sin_iva',
        'iva_porcentaje',
        'activo',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $categoria_servicio_id;
    public $nombre;
    public $descripcion;
    public $duracion_minutos;
    public $precio_base_sin_iva;
    public $costo_estimado_sin_iva;
    public $iva_porcentaje;
    public $activo;
    public $created_at;
    public $updated_at;

    // Compatibilidad temporal con vistas viejas
    public $precio;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->categoria_servicio_id = $args['categoria_servicio_id'] ?? '';
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->duracion_minutos = $args['duracion_minutos'] ?? 30;
        $this->precio_base_sin_iva = $args['precio_base_sin_iva'] ?? '0.00';
        $this->costo_estimado_sin_iva = $args['costo_estimado_sin_iva'] ?? '0.00';
        $this->iva_porcentaje = $args['iva_porcentaje'] ?? '16.00';
        $this->activo = $args['activo'] ?? '1';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');

        $this->precio = $args['precio'] ?? '';
    }

    public function validar() {
        static::$alertas = [];

        if(!$this->nombre) {
            static::$alertas['error'][] = 'El nombre del servicio es obligatorio';
        }

        if(!$this->categoria_servicio_id) {
            static::$alertas['error'][] = 'La categoría del servicio es obligatoria';
        }

        if(!$this->duracion_minutos || !is_numeric($this->duracion_minutos)) {
            static::$alertas['error'][] = 'La duración no es válida';
        }

        if(!is_numeric($this->precio_base_sin_iva)) {
            static::$alertas['error'][] = 'El precio no es válido';
        }

        return static::$alertas;
    }
}

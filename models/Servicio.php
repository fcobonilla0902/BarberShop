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

    // Campos calculados/extra para consultas JOIN y compatibilidad
    public $categoria;
    public $precio_final;
    public $iva_monto;
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

        $this->categoria = $args['categoria'] ?? '';
        $this->precio_final = $args['precio_final'] ?? null;
        $this->iva_monto = $args['iva_monto'] ?? null;
        $this->precio = $args['precio'] ?? '';
    }

    public function validar() {
        static::$alertas = [];

        if(!$this->categoria_servicio_id) {
            static::$alertas['error'][] = 'La categoría es obligatoria';
        }

        if(!$this->nombre) {
            static::$alertas['error'][] = 'El nombre del servicio es obligatorio';
        }

        if(!$this->duracion_minutos || !is_numeric($this->duracion_minutos)) {
            static::$alertas['error'][] = 'La duración debe ser numérica';
        }

        if((int)$this->duracion_minutos <= 0) {
            static::$alertas['error'][] = 'La duración debe ser mayor a cero';
        }

        if(((int)$this->duracion_minutos % 15) !== 0) {
            static::$alertas['error'][] = 'La duración debe respetar bloques de 15 minutos';
        }

        if($this->precio_base_sin_iva === '' || !is_numeric($this->precio_base_sin_iva)) {
            static::$alertas['error'][] = 'El precio sin IVA no es válido';
        }

        if((float)$this->precio_base_sin_iva < 0) {
            static::$alertas['error'][] = 'El precio sin IVA no puede ser negativo';
        }

        if($this->costo_estimado_sin_iva === '' || !is_numeric($this->costo_estimado_sin_iva)) {
            static::$alertas['error'][] = 'El costo estimado no es válido';
        }

        if((float)$this->costo_estimado_sin_iva < 0) {
            static::$alertas['error'][] = 'El costo estimado no puede ser negativo';
        }

        if($this->iva_porcentaje === '' || !is_numeric($this->iva_porcentaje)) {
            static::$alertas['error'][] = 'El IVA no es válido';
        }

        if((float)$this->iva_porcentaje < 0) {
            static::$alertas['error'][] = 'El IVA no puede ser negativo';
        }

        return static::$alertas;
    }

    public static function activosConCategoria() {
        $query = "
            SELECT
                s.*,
                c.nombre AS categoria,
                ROUND(s.precio_base_sin_iva * (s.iva_porcentaje / 100), 2) AS iva_monto,
                ROUND(s.precio_base_sin_iva + (s.precio_base_sin_iva * (s.iva_porcentaje / 100)), 2) AS precio_final
            FROM servicios s
            INNER JOIN categorias_servicio c ON c.id = s.categoria_servicio_id
            WHERE s.activo = 1
            ORDER BY c.nombre ASC, s.nombre ASC
        ";

        return self::SQL($query);
    }

    public static function todosConCategoria() {
        $query = "
            SELECT
                s.*,
                c.nombre AS categoria,
                ROUND(s.precio_base_sin_iva * (s.iva_porcentaje / 100), 2) AS iva_monto,
                ROUND(s.precio_base_sin_iva + (s.precio_base_sin_iva * (s.iva_porcentaje / 100)), 2) AS precio_final
            FROM servicios s
            INNER JOIN categorias_servicio c ON c.id = s.categoria_servicio_id
            ORDER BY s.activo DESC, c.nombre ASC, s.nombre ASC
        ";

        return self::SQL($query);
    }

    public function calcularIvaMonto() {
        return round((float)$this->precio_base_sin_iva * ((float)$this->iva_porcentaje / 100), 2);
    }

    public function calcularPrecioFinal() {
        return round((float)$this->precio_base_sin_iva + $this->calcularIvaMonto(), 2);
    }
}

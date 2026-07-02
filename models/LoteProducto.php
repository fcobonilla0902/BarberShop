<?php

namespace Model;

class LoteProducto extends ActiveRecord {
    protected static $tabla = 'lotes_producto';
    protected static $columnasDB = [
        'id',
        'producto_id',
        'proveedor_id',
        'estado_lote_id',
        'codigo_lote',
        'fecha_entrada',
        'fecha_caducidad',
        'cantidad_inicial',
        'cantidad_actual',
        'costo_unitario_sin_iva',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $producto_id;
    public $proveedor_id;
    public $estado_lote_id;
    public $codigo_lote;
    public $fecha_entrada;
    public $fecha_caducidad;
    public $cantidad_inicial;
    public $cantidad_actual;
    public $costo_unitario_sin_iva;
    public $created_at;
    public $updated_at;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->producto_id = $args['producto_id'] ?? '';
        $this->proveedor_id = $args['proveedor_id'] ?? null;
        $this->estado_lote_id = $args['estado_lote_id'] ?? '1';
        $this->codigo_lote = $args['codigo_lote'] ?? '';
        $this->fecha_entrada = $args['fecha_entrada'] ?? date('Y-m-d');
        $this->fecha_caducidad = $args['fecha_caducidad'] ?? null;
        $this->cantidad_inicial = $args['cantidad_inicial'] ?? '0';
        $this->cantidad_actual = $args['cantidad_actual'] ?? $this->cantidad_inicial;
        $this->costo_unitario_sin_iva = $args['costo_unitario_sin_iva'] ?? '0.00';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function validar() {
        static::$alertas = [];

        if(!$this->producto_id) {
            static::$alertas['error'][] = 'El producto es obligatorio';
        }

        if(!$this->estado_lote_id) {
            static::$alertas['error'][] = 'El estado del lote es obligatorio';
        }

        if(!$this->codigo_lote) {
            static::$alertas['error'][] = 'El código de lote es obligatorio';
        }

        if(!$this->fecha_entrada) {
            static::$alertas['error'][] = 'La fecha de entrada es obligatoria';
        }

        if($this->cantidad_inicial === '' || !is_numeric($this->cantidad_inicial)) {
            static::$alertas['error'][] = 'La cantidad inicial no es válida';
        }

        if((int)$this->cantidad_inicial < 0) {
            static::$alertas['error'][] = 'La cantidad inicial no puede ser negativa';
        }

        if($this->costo_unitario_sin_iva === '' || !is_numeric($this->costo_unitario_sin_iva)) {
            static::$alertas['error'][] = 'El costo unitario no es válido';
        }

        if((float)$this->costo_unitario_sin_iva < 0) {
            static::$alertas['error'][] = 'El costo unitario no puede ser negativo';
        }

        return static::$alertas;
    }
}

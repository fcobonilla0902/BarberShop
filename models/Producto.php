<?php

namespace Model;

class Producto extends ActiveRecord {
    protected static $tabla = 'productos';
    protected static $columnasDB = [
        'id',
        'categoria_producto_id',
        'nombre',
        'marca',
        'descripcion',
        'codigo_barras',
        'imagen_url',
        'unidad_medida',
        'costo_referencia_sin_iva',
        'factor_utilidad',
        'precio_venta_sin_iva',
        'iva_porcentaje',
        'stock_minimo',
        'activo',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $categoria_producto_id;
    public $nombre;
    public $marca;
    public $descripcion;
    public $codigo_barras;
    public $imagen_url;
    public $unidad_medida;
    public $costo_referencia_sin_iva;
    public $factor_utilidad;
    public $precio_venta_sin_iva;
    public $iva_porcentaje;
    public $stock_minimo;
    public $activo;
    public $created_at;
    public $updated_at;

    // Campos calculados/JOIN
    public $categoria;
    public $stock_total;
    public $iva_monto;
    public $precio_final;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->categoria_producto_id = $args['categoria_producto_id'] ?? '';
        $this->nombre = $args['nombre'] ?? '';
        $this->marca = $args['marca'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->codigo_barras = $args['codigo_barras'] ?? null;
        $this->imagen_url = $args['imagen_url'] ?? null;
        $this->unidad_medida = $args['unidad_medida'] ?? 'pieza';
        $this->costo_referencia_sin_iva = $args['costo_referencia_sin_iva'] ?? '0.00';
        $this->factor_utilidad = $args['factor_utilidad'] ?? '1.30';
        $this->precio_venta_sin_iva = $args['precio_venta_sin_iva'] ?? '0.00';
        $this->iva_porcentaje = $args['iva_porcentaje'] ?? '16.00';
        $this->stock_minimo = $args['stock_minimo'] ?? '0';
        $this->activo = $args['activo'] ?? '1';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');

        $this->categoria = $args['categoria'] ?? '';
        $this->stock_total = $args['stock_total'] ?? 0;
        $this->iva_monto = $args['iva_monto'] ?? 0;
        $this->precio_final = $args['precio_final'] ?? 0;
    }

    public function validar() {
        static::$alertas = [];

        if(!$this->categoria_producto_id) {
            static::$alertas['error'][] = 'La categoría es obligatoria';
        }

        if(!$this->nombre) {
            static::$alertas['error'][] = 'El nombre del producto es obligatorio';
        }

        if($this->precio_venta_sin_iva === '' || !is_numeric($this->precio_venta_sin_iva)) {
            static::$alertas['error'][] = 'El precio de venta no es válido';
        }

        if((float)$this->precio_venta_sin_iva < 0) {
            static::$alertas['error'][] = 'El precio de venta no puede ser negativo';
        }

        if($this->costo_referencia_sin_iva === '' || !is_numeric($this->costo_referencia_sin_iva)) {
            static::$alertas['error'][] = 'El costo de referencia no es válido';
        }

        if((float)$this->costo_referencia_sin_iva < 0) {
            static::$alertas['error'][] = 'El costo de referencia no puede ser negativo';
        }

        if($this->factor_utilidad === '' || !is_numeric($this->factor_utilidad)) {
            static::$alertas['error'][] = 'El factor de utilidad no es válido';
        }

        if((float)$this->factor_utilidad <= 0) {
            static::$alertas['error'][] = 'El factor de utilidad debe ser mayor a cero';
        }

        if($this->iva_porcentaje === '' || !is_numeric($this->iva_porcentaje)) {
            static::$alertas['error'][] = 'El IVA no es válido';
        }

        if((float)$this->iva_porcentaje < 0) {
            static::$alertas['error'][] = 'El IVA no puede ser negativo';
        }

        if($this->stock_minimo === '' || !is_numeric($this->stock_minimo)) {
            static::$alertas['error'][] = 'El stock mínimo no es válido';
        }

        if((int)$this->stock_minimo < 0) {
            static::$alertas['error'][] = 'El stock mínimo no puede ser negativo';
        }

        return static::$alertas;
    }

    public static function todosConStock() {
        $query = "
            SELECT
                p.*,
                cp.nombre AS categoria,
                COALESCE(SUM(lp.cantidad_actual), 0) AS stock_total,
                ROUND(p.precio_venta_sin_iva * (p.iva_porcentaje / 100), 2) AS iva_monto,
                ROUND(p.precio_venta_sin_iva + (p.precio_venta_sin_iva * (p.iva_porcentaje / 100)), 2) AS precio_final
            FROM productos p
            INNER JOIN categorias_producto cp ON cp.id = p.categoria_producto_id
            LEFT JOIN lotes_producto lp ON lp.producto_id = p.id
            GROUP BY
                p.id,
                cp.nombre
            ORDER BY p.activo DESC, p.nombre ASC
        ";

        return self::SQL($query);
    }
}

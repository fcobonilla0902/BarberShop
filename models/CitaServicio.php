<?php

namespace Model;

class CitaServicio extends ActiveRecord {
    protected static $tabla = 'citas_servicios';
    protected static $columnasDB = [
        'id',
        'cita_id',
        'servicio_id',
        'orden',
        'duracion_minutos_snapshot',
        'precio_sin_iva_snapshot',
        'iva_porcentaje_snapshot',
        'total_con_iva_snapshot'
    ];

    public $id;
    public $cita_id;
    public $servicio_id;
    public $orden;
    public $duracion_minutos_snapshot;
    public $precio_sin_iva_snapshot;
    public $iva_porcentaje_snapshot;
    public $total_con_iva_snapshot;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cita_id = $args['cita_id'] ?? '';
        $this->servicio_id = $args['servicio_id'] ?? '';
        $this->orden = $args['orden'] ?? 1;
        $this->duracion_minutos_snapshot = $args['duracion_minutos_snapshot'] ?? '';
        $this->precio_sin_iva_snapshot = $args['precio_sin_iva_snapshot'] ?? '';
        $this->iva_porcentaje_snapshot = $args['iva_porcentaje_snapshot'] ?? '';
        $this->total_con_iva_snapshot = $args['total_con_iva_snapshot'] ?? '';
    }
}

<?php

namespace Model;

class Cita extends ActiveRecord {
    protected static $tabla = 'citas';
    protected static $columnasDB = [
        'id',
        'cliente_id',
        'colaborador_id',
        'sucursal_id',
        'estado_cita_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'duracion_total_minutos',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $cliente_id;
    public $colaborador_id;
    public $sucursal_id;
    public $estado_cita_id;
    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    public $duracion_total_minutos;
    public $observaciones;
    public $created_at;
    public $updated_at;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cliente_id = $args['cliente_id'] ?? '';
        $this->colaborador_id = $args['colaborador_id'] ?? '';
        $this->sucursal_id = $args['sucursal_id'] ?? '';
        $this->estado_cita_id = $args['estado_cita_id'] ?? '1';
        $this->fecha = $args['fecha'] ?? '';
        $this->hora_inicio = $args['hora_inicio'] ?? '';
        $this->hora_fin = $args['hora_fin'] ?? '';
        $this->duracion_total_minutos = $args['duracion_total_minutos'] ?? '';
        $this->observaciones = $args['observaciones'] ?? null;
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');
    }
}

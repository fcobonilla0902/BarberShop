<?php

namespace Model;

class BloqueAgenda extends ActiveRecord {
    protected static $tabla = 'bloques_agenda';
    protected static $columnasDB = [
        'id',
        'cita_id',
        'sucursal_id',
        'colaborador_id',
        'fecha',
        'hora_inicio',
        'duracion_bloque_minutos'
    ];

    public $id;
    public $cita_id;
    public $sucursal_id;
    public $colaborador_id;
    public $fecha;
    public $hora_inicio;
    public $duracion_bloque_minutos;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cita_id = $args['cita_id'] ?? '';
        $this->sucursal_id = $args['sucursal_id'] ?? '';
        $this->colaborador_id = $args['colaborador_id'] ?? '';
        $this->fecha = $args['fecha'] ?? '';
        $this->hora_inicio = $args['hora_inicio'] ?? '';
        $this->duracion_bloque_minutos = $args['duracion_bloque_minutos'] ?? 15;
    }
}

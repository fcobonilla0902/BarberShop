<?php

namespace Model;

class Colaborador extends ActiveRecord {
    protected static $tabla = 'colaboradores';
    protected static $columnasDB = [
        'id',
        'cuenta_id',
        'rol_colaborador_id',
        'sucursal_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'telefono',
        'fecha_nacimiento',
        'fecha_contratacion',
        'salario_mensual',
        'activo',
        'created_at',
        'updated_at'
    ];

    public $id;
    public $cuenta_id;
    public $rol_colaborador_id;
    public $sucursal_id;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $curp;
    public $telefono;
    public $fecha_nacimiento;
    public $fecha_contratacion;
    public $salario_mensual;
    public $activo;
    public $created_at;
    public $updated_at;

    // Campos calculados por consultas JOIN
    public $rol;
    public $sucursal;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cuenta_id = $args['cuenta_id'] ?? null;
        $this->rol_colaborador_id = $args['rol_colaborador_id'] ?? '';
        $this->sucursal_id = $args['sucursal_id'] ?? '';
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido_paterno = $args['apellido_paterno'] ?? '';
        $this->apellido_materno = $args['apellido_materno'] ?? null;
        $this->curp = $args['curp'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->fecha_nacimiento = $args['fecha_nacimiento'] ?? null;
        $this->fecha_contratacion = $args['fecha_contratacion'] ?? date('Y-m-d');
        $this->salario_mensual = $args['salario_mensual'] ?? '0.00';
        $this->activo = $args['activo'] ?? '1';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');

        $this->rol = $args['rol'] ?? '';
        $this->sucursal = $args['sucursal'] ?? '';
    }

    public static function buscarPorCuenta($cuentaId) {
        $cuentaId = self::$db->escape_string($cuentaId);

        $query = "SELECT c.*, r.nombre AS rol, s.nombre_comercial AS sucursal ";
        $query .= "FROM colaboradores c ";
        $query .= "INNER JOIN roles_colaborador r ON r.id = c.rol_colaborador_id ";
        $query .= "INNER JOIN sucursales s ON s.id = c.sucursal_id ";
        $query .= "WHERE c.cuenta_id = '{$cuentaId}' AND c.activo = 1 ";
        $query .= "LIMIT 1";

        $resultado = self::SQL($query);
        return array_shift($resultado);
    }

    public function nombreCompleto() {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . ($this->apellido_materno ?? ''));
    }
}

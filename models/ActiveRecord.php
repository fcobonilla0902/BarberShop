<?php

namespace Model;

class ActiveRecord {

    // Base de datos
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    // Alertas y mensajes
    protected static $alertas = [];

    public static function setDB($database) {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    public static function consultarSQL($query) {
        $resultado = self::$db->query($query);

        if(!$resultado) {
            return [];
        }

        $array = [];

        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        $resultado->free();

        return $array;
    }

    public static function SQL($query) {
        return static::consultarSQL($query);
    }

    protected static function crearObjeto($registro) {
        $objeto = new static;

        foreach($registro as $key => $value) {
            if(property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    public function atributos() {
        $atributos = [];

        foreach(static::$columnasDB as $columna) {
            if($columna === 'id') continue;

            if(property_exists($this, $columna)) {
                $atributos[$columna] = $this->$columna;
            }
        }

        return $atributos;
    }

    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];

        foreach($atributos as $key => $value) {
            if($value === null) {
                $sanitizado[$key] = null;
            } else {
                $sanitizado[$key] = self::$db->escape_string((string)$value);
            }
        }

        return $sanitizado;
    }

    private static function valorSQL($value) {
        if($value === null) {
            return "NULL";
        }

        return "'" . $value . "'";
    }

    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    public function guardar() {
        if(!is_null($this->id)) {
            return $this->actualizar();
        }

        return $this->crear();
    }

    public static function all() {
        $query = "SELECT * FROM " . static::$tabla;
        return static::consultarSQL($query);
    }

    public static function get($limite) {
        $limite = (int)$limite;
        $query = "SELECT * FROM " . static::$tabla . " LIMIT {$limite}";
        return static::consultarSQL($query);
    }

    public static function find($id) {
        $id = (int)$id;
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id} LIMIT 1";
        $resultado = static::consultarSQL($query);

        return array_shift($resultado);
    }

    public static function where($columna, $valor) {
        $columna = self::$db->escape_string($columna);

        if($valor === null) {
            $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} IS NULL LIMIT 1";
        } else {
            $valor = self::$db->escape_string((string)$valor);
            $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}' LIMIT 1";
        }

        $resultado = static::consultarSQL($query);

        return array_shift($resultado);
    }

    public static function belongsTo($columna, $valor) {
        $columna = self::$db->escape_string($columna);
        $valor = self::$db->escape_string((string)$valor);

        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}'";
        return static::consultarSQL($query);
    }

    public function crear() {
        $atributos = $this->sanitizarAtributos();

        $columnas = array_keys($atributos);
        $valores = array_map(function($value) {
            return self::valorSQL($value);
        }, array_values($atributos));

        $query = "INSERT INTO " . static::$tabla . " (";
        $query .= join(', ', $columnas);
        $query .= ") VALUES (";
        $query .= join(', ', $valores);
        $query .= ")";

        $resultado = self::$db->query($query);

        return [
            'resultado' => $resultado,
            'id' => self::$db->insert_id
        ];
    }

    public function actualizar() {
        $atributos = $this->sanitizarAtributos();
        $valores = [];

        foreach($atributos as $key => $value) {
            $valores[] = "{$key} = " . self::valorSQL($value);
        }

        $id = (int)$this->id;

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= join(', ', $valores);
        $query .= " WHERE id = {$id} ";
        $query .= " LIMIT 1";

        $resultado = self::$db->query($query);

        return [
            'resultado' => $resultado,
            'id' => $this->id
        ];
    }

    public function eliminar() {
        $id = (int)$this->id;

        $query = "DELETE FROM " . static::$tabla . " WHERE id = {$id} LIMIT 1";
        $resultado = self::$db->query($query);

        return $resultado;
    }
}

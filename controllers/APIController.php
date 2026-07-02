<?php

namespace Controllers;

use Model\Servicio;
use Model\Sucursal;
use Model\Colaborador;

class APIController {
    public static function index() {
        header('Content-Type: application/json; charset=utf-8');

        $servicios = Servicio::activosConCategoria();
        $respuesta = [];

        foreach($servicios as $servicio) {
            $precioSinIva = (float)$servicio->precio_base_sin_iva;
            $ivaPorcentaje = (float)$servicio->iva_porcentaje;
            $ivaMonto = round($precioSinIva * ($ivaPorcentaje / 100), 2);
            $precioFinal = round($precioSinIva + $ivaMonto, 2);

            $respuesta[] = [
                'id' => (int)$servicio->id,
                'nombre' => $servicio->nombre,
                'descripcion' => $servicio->descripcion,
                'categoria_servicio_id' => (int)$servicio->categoria_servicio_id,
                'categoria' => $servicio->categoria,
                'duracion_minutos' => (int)$servicio->duracion_minutos,
                'precio_base_sin_iva' => $precioSinIva,
                'iva_porcentaje' => $ivaPorcentaje,
                'iva_monto' => $ivaMonto,
                'precio_final' => $precioFinal,

                // Compatibilidad con app.js
                'precio' => $precioFinal
            ];
        }

        echo json_encode($respuesta);
    }

    public static function guardar() {
        header('Content-Type: application/json; charset=utf-8');

        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if(!isset($_SESSION['login']) || ($_SESSION['tipo_usuario'] ?? '') !== 'cliente') {
            http_response_code(401);
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Debes iniciar sesión como cliente para crear una cita.'
            ]);
            return;
        }

        global $db;

        $clienteId = (int)($_SESSION['cliente_id'] ?? $_POST['cliente_id'] ?? $_POST['usuarioId'] ?? 0);
        $fecha = trim($_POST['fecha'] ?? '');
        $hora = trim($_POST['hora'] ?? $_POST['hora_inicio'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');
        $serviciosRaw = trim($_POST['servicios'] ?? '');

        if(!$clienteId || !$fecha || !$hora || !$serviciosRaw) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Faltan datos para crear la cita.'
            ]);
            return;
        }

        if(!self::fechaValida($fecha)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'La fecha no es válida.'
            ]);
            return;
        }

        if(!self::horaValida($hora)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'La hora no es válida.'
            ]);
            return;
        }

        if(self::esFinDeSemana($fecha)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'No se permiten citas en fin de semana.'
            ]);
            return;
        }

        if(!self::horaDentroDeHorario($hora)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'El horario disponible es de 10:00 a 18:00.'
            ]);
            return;
        }

        $idsServicios = array_values(array_unique(array_filter(array_map('intval', explode(',', $serviciosRaw)))));

        if(empty($idsServicios)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Selecciona al menos un servicio.'
            ]);
            return;
        }

        $sucursal = Sucursal::principal();

        if(!$sucursal) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'No hay sucursal activa configurada.'
            ]);
            return;
        }

        $colaborador = self::obtenerBarberoDisponibleBase();

        if(!$colaborador) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'No hay barbero activo para asignar la cita.'
            ]);
            return;
        }

        $servicios = self::obtenerServiciosPorIds($idsServicios);

        if(count($servicios) !== count($idsServicios)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Uno o más servicios no existen o están inactivos.'
            ]);
            return;
        }

        $duracionTotal = 0;
        $subtotalSinIva = 0;
        $ivaTotal = 0;
        $totalConIva = 0;

        foreach($servicios as $servicio) {
            $duracionTotal += (int)$servicio['duracion_minutos'];

            $precioSinIva = (float)$servicio['precio_base_sin_iva'];
            $ivaPorcentaje = (float)$servicio['iva_porcentaje'];
            $ivaMonto = round($precioSinIva * ($ivaPorcentaje / 100), 2);
            $totalLinea = round($precioSinIva + $ivaMonto, 2);

            $subtotalSinIva += $precioSinIva;
            $ivaTotal += $ivaMonto;
            $totalConIva += $totalLinea;
        }

        $bloqueMinutos = (int)$sucursal->bloque_agenda_minutos;
        if($bloqueMinutos <= 0) $bloqueMinutos = 15;

        if(($duracionTotal % $bloqueMinutos) !== 0) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'La duración total no respeta los bloques de agenda configurados.'
            ]);
            return;
        }

        $horaInicio = self::normalizarHora($hora);
        $horaFin = self::sumarMinutosHora($horaInicio, $duracionTotal);

        if(!self::horaFinDentroDeHorario($horaFin)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'La cita termina fuera del horario permitido.'
            ]);
            return;
        }

        $bloques = self::generarBloques($horaInicio, $duracionTotal, $bloqueMinutos);

        if(empty($bloques)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'No se pudieron generar los bloques de agenda.'
            ]);
            return;
        }

        $conflictos = self::buscarConflictos(
            (int)$sucursal->id,
            (int)$colaborador->id,
            $fecha,
            $bloques
        );

        if(!empty($conflictos)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Ese horario ya está ocupado. Selecciona otra hora.',
                'conflictos' => $conflictos
            ]);
            return;
        }

        $fechaSQL = $db->escape_string($fecha);
        $horaInicioSQL = $db->escape_string($horaInicio);
        $horaFinSQL = $db->escape_string($horaFin);
        $observacionesSQL = $observaciones !== '' ? "'" . $db->escape_string($observaciones) . "'" : "NULL";

        $db->begin_transaction();

        try {
            $queryCita = "
                INSERT INTO citas
                (
                    cliente_id,
                    colaborador_id,
                    sucursal_id,
                    estado_cita_id,
                    fecha,
                    hora_inicio,
                    hora_fin,
                    duracion_total_minutos,
                    observaciones,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    {$clienteId},
                    {$colaborador->id},
                    {$sucursal->id},
                    1,
                    '{$fechaSQL}',
                    '{$horaInicioSQL}',
                    '{$horaFinSQL}',
                    {$duracionTotal},
                    {$observacionesSQL},
                    NOW(),
                    NOW()
                )
            ";

            if(!$db->query($queryCita)) {
                throw new \Exception($db->error);
            }

            $citaId = $db->insert_id;
            $orden = 1;

            foreach($servicios as $servicio) {
                $servicioId = (int)$servicio['id'];
                $duracion = (int)$servicio['duracion_minutos'];
                $precioSinIva = (float)$servicio['precio_base_sin_iva'];
                $ivaPorcentaje = (float)$servicio['iva_porcentaje'];
                $totalLinea = round($precioSinIva + ($precioSinIva * ($ivaPorcentaje / 100)), 2);

                $queryServicio = "
                    INSERT INTO citas_servicios
                    (
                        cita_id,
                        servicio_id,
                        orden,
                        duracion_minutos_snapshot,
                        precio_sin_iva_snapshot,
                        iva_porcentaje_snapshot,
                        total_con_iva_snapshot
                    )
                    VALUES
                    (
                        {$citaId},
                        {$servicioId},
                        {$orden},
                        {$duracion},
                        " . self::numeroSQL($precioSinIva) . ",
                        " . self::numeroSQL($ivaPorcentaje) . ",
                        " . self::numeroSQL($totalLinea) . "
                    )
                ";

                if(!$db->query($queryServicio)) {
                    throw new \Exception($db->error);
                }

                $orden++;
            }

            foreach($bloques as $bloqueHora) {
                $bloqueHoraSQL = $db->escape_string($bloqueHora);

                $queryBloque = "
                    INSERT INTO bloques_agenda
                    (
                        cita_id,
                        sucursal_id,
                        colaborador_id,
                        fecha,
                        hora_inicio,
                        duracion_bloque_minutos
                    )
                    VALUES
                    (
                        {$citaId},
                        {$sucursal->id},
                        {$colaborador->id},
                        '{$fechaSQL}',
                        '{$bloqueHoraSQL}',
                        {$bloqueMinutos}
                    )
                ";

                if(!$db->query($queryBloque)) {
                    throw new \Exception('El horario se ocupó antes de guardar. Intenta con otra hora.');
                }
            }

            $db->commit();

            echo json_encode([
                'resultado' => true,
                'mensaje' => 'Cita registrada correctamente.',
                'cita' => [
                    'id' => (int)$citaId,
                    'fecha' => $fecha,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'duracion_total_minutos' => $duracionTotal,
                    'subtotal_sin_iva' => round($subtotalSinIva, 2),
                    'iva_total' => round($ivaTotal, 2),
                    'total_con_iva' => round($totalConIva, 2),
                    'bloques_generados' => $bloques,
                    'barbero' => trim($colaborador->nombre . ' ' . $colaborador->apellido_paterno)
                ]
            ]);
            return;

        } catch(\Throwable $e) {
            $db->rollback();

            echo json_encode([
                'resultado' => false,
                'mensaje' => $e->getMessage() ?: 'No se pudo guardar la cita.'
            ]);
            return;
        }
    }

    public static function eliminar() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        global $db;

        if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
            header('Location: /');
            exit;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if($id > 0) {
                $db->begin_transaction();

                try {
                    $db->query("DELETE FROM bloques_agenda WHERE cita_id = {$id}");
                    $db->query("UPDATE citas SET estado_cita_id = 3, updated_at = NOW() WHERE id = {$id} LIMIT 1");
                    $db->commit();
                } catch(\Throwable $e) {
                    $db->rollback();
                }
            }
        }

        header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/admin'));
        exit;
    }

    private static function obtenerServiciosPorIds($idsServicios) {
        global $db;

        $ids = implode(',', array_map('intval', $idsServicios));

        $query = "
            SELECT
                id,
                nombre,
                duracion_minutos,
                precio_base_sin_iva,
                iva_porcentaje,
                costo_estimado_sin_iva
            FROM servicios
            WHERE activo = 1
            AND id IN ({$ids})
        ";

        $resultado = $db->query($query);
        $servicios = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $servicios[(int)$row['id']] = $row;
            }
        }

        $ordenados = [];
        foreach($idsServicios as $id) {
            if(isset($servicios[$id])) {
                $ordenados[] = $servicios[$id];
            }
        }

        return $ordenados;
    }

    private static function obtenerBarberoDisponibleBase() {
        $colaboradores = Colaborador::SQL("
            SELECT c.*
            FROM colaboradores c
            INNER JOIN roles_colaborador r ON r.id = c.rol_colaborador_id
            WHERE c.activo = 1
            AND r.nombre = 'Barbero'
            ORDER BY c.id ASC
            LIMIT 1
        ");

        return array_shift($colaboradores);
    }

    private static function buscarConflictos($sucursalId, $colaboradorId, $fecha, $bloques) {
        global $db;

        $bloquesSQL = array_map(function($bloque) use ($db) {
            return "'" . $db->escape_string($bloque) . "'";
        }, $bloques);

        $fechaSQL = $db->escape_string($fecha);
        $listaBloques = implode(',', $bloquesSQL);

        $query = "
            SELECT hora_inicio
            FROM bloques_agenda
            WHERE sucursal_id = {$sucursalId}
            AND colaborador_id = {$colaboradorId}
            AND fecha = '{$fechaSQL}'
            AND hora_inicio IN ({$listaBloques})
        ";

        $resultado = $db->query($query);
        $conflictos = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $conflictos[] = substr($row['hora_inicio'], 0, 5);
            }
        }

        return $conflictos;
    }

    private static function generarBloques($horaInicio, $duracionTotal, $bloqueMinutos) {
        $bloques = [];
        $actual = strtotime($horaInicio);
        $cantidad = (int)($duracionTotal / $bloqueMinutos);

        for($i = 0; $i < $cantidad; $i++) {
            $bloques[] = date('H:i:s', $actual);
            $actual = strtotime("+{$bloqueMinutos} minutes", $actual);
        }

        return $bloques;
    }

    private static function sumarMinutosHora($hora, $minutos) {
        return date('H:i:s', strtotime("+{$minutos} minutes", strtotime($hora)));
    }

    private static function normalizarHora($hora) {
        if(strlen($hora) === 5) {
            return $hora . ':00';
        }

        return $hora;
    }

    private static function fechaValida($fecha) {
        $partes = explode('-', $fecha);

        if(count($partes) !== 3) {
            return false;
        }

        return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
    }

    private static function horaValida($hora) {
        return preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora) === 1;
    }

    private static function esFinDeSemana($fecha) {
        $dia = (int)date('N', strtotime($fecha));
        return $dia >= 6;
    }

    private static function horaDentroDeHorario($hora) {
        $hora = self::normalizarHora($hora);
        return $hora >= '10:00:00' && $hora <= '18:00:00';
    }

    private static function horaFinDentroDeHorario($horaFin) {
        return $horaFin <= '19:00:00';
    }

    private static function numeroSQL($numero) {
        return number_format((float)$numero, 2, '.', '');
    }
}

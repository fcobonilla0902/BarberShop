<?php

namespace Controllers;

use MVC\Router;

class MisCitasController {

    public static function index(Router $router) {
        isAuth();

        if(($_SESSION['tipo_usuario'] ?? '') !== 'cliente') {
            header('Location: /admin');
            exit;
        }

        global $db;

        $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
        $mensaje = $_SESSION['mensaje_mis_citas'] ?? null;
        unset($_SESSION['mensaje_mis_citas']);

        $query = "
            SELECT
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.estado_cita_id,
                ec.nombre AS estado,
                c.observaciones,
                GROUP_CONCAT(s.nombre ORDER BY cs.orden SEPARATOR ' + ') AS servicios,
                SUM(cs.total_con_iva_snapshot) AS total_con_iva,
                SUM(cs.duracion_minutos_snapshot) AS duracion_total
            FROM citas c
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            INNER JOIN citas_servicios cs ON cs.cita_id = c.id
            INNER JOIN servicios s ON s.id = cs.servicio_id
            WHERE c.cliente_id = {$clienteId}
            GROUP BY
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.estado_cita_id,
                ec.nombre,
                c.observaciones
            ORDER BY c.fecha DESC, c.hora_inicio DESC
        ";

        $resultado = $db->query($query);
        $citas = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $citas[] = $row;
            }
        }

        $router->render('cita/mis-citas', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'citas' => $citas,
            'mensaje' => $mensaje
        ]);
    }

    public static function solicitarCancelacion() {
        isAuth();

        if(($_SESSION['tipo_usuario'] ?? '') !== 'cliente') {
            header('Location: /admin');
            exit;
        }

        global $db;

        $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
        $citaId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if(!$citaId) {
            $_SESSION['mensaje_mis_citas'] = [
                'tipo' => 'error',
                'texto' => 'La cita no es válida.'
            ];

            header('Location: /mis-citas');
            exit;
        }

        $queryValidar = "
            SELECT id, estado_cita_id
            FROM citas
            WHERE id = {$citaId}
            AND cliente_id = {$clienteId}
            LIMIT 1
        ";

        $resultado = $db->query($queryValidar);
        $cita = $resultado ? $resultado->fetch_assoc() : null;

        if(!$cita) {
            $_SESSION['mensaje_mis_citas'] = [
                'tipo' => 'error',
                'texto' => 'No se encontró la cita.'
            ];

            header('Location: /mis-citas');
            exit;
        }

        if((int)$cita['estado_cita_id'] !== 1) {
            $_SESSION['mensaje_mis_citas'] = [
                'tipo' => 'error',
                'texto' => 'Solo se puede solicitar cancelación de citas reservadas.'
            ];

            header('Location: /mis-citas');
            exit;
        }

        $queryActualizar = "
            UPDATE citas
            SET estado_cita_id = 4,
                updated_at = NOW()
            WHERE id = {$citaId}
            AND cliente_id = {$clienteId}
            LIMIT 1
        ";

        $db->query($queryActualizar);

        $_SESSION['mensaje_mis_citas'] = [
            'tipo' => 'exito',
            'texto' => 'Solicitud de cancelación registrada correctamente.'
        ];

        header('Location: /mis-citas');
        exit;
    }
}

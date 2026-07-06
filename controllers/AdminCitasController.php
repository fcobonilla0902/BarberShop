<?php

namespace Controllers;

use MVC\Router;

class AdminCitasController {

    public static function index(Router $router) {
        isAdmin();

        global $db;

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $estado = trim($_GET['estado'] ?? '');
        $busqueda = trim($_GET['q'] ?? '');

        if($fecha === 'todas' || $fecha === '') {
            $fecha = '';
        } else {
            $partesFecha = explode('-', $fecha);
            if(count($partesFecha) !== 3 || !checkdate((int)$partesFecha[1], (int)$partesFecha[2], (int)$partesFecha[0])) {
                $fecha = date('Y-m-d');
            }
        }

        $estados = self::obtenerEstados();
        $citas = self::obtenerCitas($fecha, $estado, $busqueda);
        $metricas = self::obtenerMetricas($fecha, $estado, $busqueda);

        $mensaje = $_SESSION['mensaje_admin_citas'] ?? null;
        unset($_SESSION['mensaje_admin_citas']);

        $router->render('admin/citas', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'fecha' => $fecha,
            'estadoSeleccionado' => $estado,
            'busqueda' => $busqueda,
            'estados' => $estados,
            'citas' => $citas,
            'metricas' => $metricas,
            'mensaje' => $mensaje
        ]);
    }

    public static function cambiarEstado() {
        isAdmin();

        global $db;

        $citaId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $accion = $_POST['accion'] ?? '';
        $redirect = $_POST['redirect'] ?? '/admin/citas';

        if(!$citaId) {
            self::setMensaje('error', 'La cita no es válida.');
            header('Location: ' . $redirect);
            exit;
        }

        $acciones = [
            'atender' => [
                'estado' => 'Atendida',
                'fallback' => 2,
                'liberar_bloques' => false,
                'mensaje' => 'La cita fue marcada como atendida.'
            ],
            'cancelar' => [
                'estado' => 'Cancelada',
                'fallback' => 3,
                'liberar_bloques' => true,
                'mensaje' => 'La cita fue cancelada y sus bloques quedaron libres.'
            ],
            'aprobar_cancelacion' => [
                'estado' => 'Cancelada',
                'fallback' => 3,
                'liberar_bloques' => true,
                'mensaje' => 'La cancelación fue aprobada y sus bloques quedaron libres.'
            ],
            'rechazar_cancelacion' => [
                'estado' => 'Reservada',
                'fallback' => 1,
                'liberar_bloques' => false,
                'mensaje' => 'La solicitud de cancelación fue rechazada y la cita quedó reservada.'
            ]
        ];

        if(!isset($acciones[$accion])) {
            self::setMensaje('error', 'Acción no permitida.');
            header('Location: ' . $redirect);
            exit;
        }

        $cita = self::obtenerCitaSimple($citaId);

        if(!$cita) {
            self::setMensaje('error', 'No se encontró la cita.');
            header('Location: ' . $redirect);
            exit;
        }

        $config = $acciones[$accion];
        $estadoId = self::obtenerEstadoId($config['estado'], $config['fallback']);

        $db->begin_transaction();

        try {
            if($config['liberar_bloques']) {
                $db->query("DELETE FROM bloques_agenda WHERE cita_id = {$citaId}");
            }

            $query = "
                UPDATE citas
                SET estado_cita_id = {$estadoId},
                    updated_at = NOW()
                WHERE id = {$citaId}
                LIMIT 1
            ";

            if(!$db->query($query)) {
                throw new \Exception($db->error);
            }

            $db->commit();
            self::setMensaje('exito', $config['mensaje']);
        } catch(\Throwable $e) {
            $db->rollback();
            self::setMensaje('error', $e->getMessage() ?: 'No se pudo actualizar la cita.');
        }

        header('Location: ' . $redirect);
        exit;
    }

    private static function obtenerCitas($fecha, $estado, $busqueda) {
        global $db;

        [$where, $joinExtra] = self::construirFiltros($fecha, $estado, $busqueda);

        $query = "
            SELECT
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.duracion_total_minutos,
                c.observaciones,
                c.estado_cita_id,
                ec.nombre AS estado,
                CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', COALESCE(cl.apellido_materno, '')) AS cliente,
                cl.telefono AS cliente_telefono,
                cu.email AS cliente_email,
                CONCAT(co.nombre, ' ', co.apellido_paterno) AS barbero,
                scc.nombre_comercial AS sucursal,
                GROUP_CONCAT(s.nombre ORDER BY cs.orden SEPARATOR ' + ') AS servicios,
                SUM(cs.total_con_iva_snapshot) AS total_con_iva,
                (
                    SELECT COUNT(*)
                    FROM bloques_agenda ba
                    WHERE ba.cita_id = c.id
                ) AS bloques_ocupados
            FROM citas c
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            INNER JOIN colaboradores co ON co.id = c.colaborador_id
            INNER JOIN sucursales scc ON scc.id = c.sucursal_id
            INNER JOIN citas_servicios cs ON cs.cita_id = c.id
            INNER JOIN servicios s ON s.id = cs.servicio_id
            {$joinExtra}
            WHERE {$where}
            GROUP BY
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.duracion_total_minutos,
                c.observaciones,
                c.estado_cita_id,
                ec.nombre,
                cl.nombre,
                cl.apellido_paterno,
                cl.apellido_materno,
                cl.telefono,
                cu.email,
                co.nombre,
                co.apellido_paterno,
                scc.nombre_comercial
            ORDER BY c.fecha DESC, c.hora_inicio DESC
            LIMIT 100
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerMetricas($fecha, $estado, $busqueda) {
        global $db;

        [$where, $joinExtra] = self::construirFiltros($fecha, $estado, $busqueda);

        $query = "
            SELECT
                COUNT(DISTINCT c.id) AS total,
                SUM(CASE WHEN ec.nombre = 'Reservada' THEN 1 ELSE 0 END) AS reservadas,
                SUM(CASE WHEN ec.nombre = 'Atendida' THEN 1 ELSE 0 END) AS atendidas,
                SUM(CASE WHEN ec.nombre = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas,
                SUM(CASE WHEN ec.nombre = 'Cancelación solicitada' THEN 1 ELSE 0 END) AS cancelacion_solicitada
            FROM citas c
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            {$joinExtra}
            WHERE {$where}
        ";

        $resultado = $db->query($query);
        $row = $resultado ? $resultado->fetch_assoc() : [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'reservadas' => (int)($row['reservadas'] ?? 0),
            'atendidas' => (int)($row['atendidas'] ?? 0),
            'canceladas' => (int)($row['canceladas'] ?? 0),
            'cancelacion_solicitada' => (int)($row['cancelacion_solicitada'] ?? 0)
        ];
    }

    private static function construirFiltros($fecha, $estado, $busqueda) {
        global $db;

        $where = ["1 = 1"];
        $joinExtra = "";

        if($fecha !== '') {
            $fechaSQL = $db->escape_string($fecha);
            $where[] = "c.fecha = '{$fechaSQL}'";
        }

        if($estado !== '') {
            $estadoSQL = $db->escape_string($estado);
            $where[] = "ec.nombre = '{$estadoSQL}'";
        }

        if($busqueda !== '') {
            $busquedaSQL = $db->escape_string($busqueda);
            $where[] = "(
                cl.nombre LIKE '%{$busquedaSQL}%'
                OR cl.apellido_paterno LIKE '%{$busquedaSQL}%'
                OR cl.apellido_materno LIKE '%{$busquedaSQL}%'
                OR cl.telefono LIKE '%{$busquedaSQL}%'
                OR cu.email LIKE '%{$busquedaSQL}%'
            )";
        }

        return [implode(' AND ', $where), $joinExtra];
    }

    private static function obtenerEstados() {
        global $db;

        return self::fetchAll($db->query("
            SELECT id, nombre
            FROM estados_cita
            WHERE activo = 1
            ORDER BY id ASC
        "));
    }

    private static function obtenerEstadoId($nombre, $fallback) {
        global $db;

        $nombreSQL = $db->escape_string($nombre);
        $resultado = $db->query("SELECT id FROM estados_cita WHERE nombre = '{$nombreSQL}' LIMIT 1");

        if($resultado && $row = $resultado->fetch_assoc()) {
            return (int)$row['id'];
        }

        return (int)$fallback;
    }

    private static function obtenerCitaSimple($id) {
        global $db;

        $id = (int)$id;
        $resultado = $db->query("SELECT id, estado_cita_id FROM citas WHERE id = {$id} LIMIT 1");

        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function setMensaje($tipo, $texto) {
        $_SESSION['mensaje_admin_citas'] = [
            'tipo' => $tipo,
            'texto' => $texto
        ];
    }

    private static function fetchAll($resultado) {
        $rows = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

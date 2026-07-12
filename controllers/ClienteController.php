<?php

namespace Controllers;

use MVC\Router;

class ClienteController {

    public static function index(Router $router) {
        isAdmin();

        $q = trim($_GET['q'] ?? '');
        $estado = trim($_GET['estado'] ?? '');

        if(!in_array($estado, ['', 'activo', 'inactivo'], true)) {
            $estado = '';
        }

        $clientes = self::obtenerClientes($q, $estado);
        $metricas = self::obtenerMetricas($q, $estado);

        $router->render('clientes/index', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'clientes' => $clientes,
            'metricas' => $metricas,
            'q' => $q,
            'estado' => $estado
        ]);
    }

    private static function obtenerClientes($q, $estado) {
        global $db;

        $where = self::construirWhere($q, $estado);

        $query = "
            SELECT
                cl.id,
                cl.nombre,
                cl.apellido_paterno,
                cl.apellido_materno,
                cl.telefono,
                cl.fecha_nacimiento,
                cl.created_at,
                cu.email,
                cu.activa AS activo,
                cu.cuenta_confirmada AS cuenta_confirmada,
                cu.ultimo_acceso,
                COUNT(DISTINCT c.id) AS total_citas,
                COUNT(DISTINCT CASE WHEN ec.nombre = 'Reservada' AND c.fecha >= CURDATE() THEN c.id END) AS citas_proximas,
                COUNT(DISTINCT CASE WHEN ec.nombre = 'Cancelación solicitada' THEN c.id END) AS cancelaciones_solicitadas,
                MAX(c.fecha) AS ultima_cita,
                COALESCE(SUM(cs.total_con_iva_snapshot), 0) AS total_agendado
            FROM clientes cl
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            LEFT JOIN citas c ON c.cliente_id = cl.id
            LEFT JOIN estados_cita ec ON ec.id = c.estado_cita_id
            LEFT JOIN citas_servicios cs ON cs.cita_id = c.id
            WHERE {$where}
            GROUP BY
                cl.id,
                cl.nombre,
                cl.apellido_paterno,
                cl.apellido_materno,
                cl.telefono,
                cl.fecha_nacimiento,
                cl.created_at,
                cu.email,
                cu.activa,
                cu.cuenta_confirmada,
                cu.ultimo_acceso
            ORDER BY cl.created_at DESC, cl.id DESC
            LIMIT 150
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerMetricas($q, $estado) {
        global $db;

        $where = self::construirWhere($q, $estado);

        $query = "
            SELECT
                COUNT(DISTINCT cl.id) AS total,
                COUNT(DISTINCT CASE WHEN cu.activa = 1 THEN cl.id END) AS activos,
                COUNT(DISTINCT CASE WHEN cu.activa = 0 THEN cl.id END) AS inactivos,
                COUNT(DISTINCT CASE WHEN cu.cuenta_confirmada = 1 THEN cl.id END) AS confirmados
            FROM clientes cl
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            WHERE {$where}
        ";

        $resultado = $db->query($query);
        $row = $resultado ? $resultado->fetch_assoc() : [];

        $queryCitas = "
            SELECT COUNT(DISTINCT c.id) AS citas_proximas
            FROM clientes cl
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            INNER JOIN citas c ON c.cliente_id = cl.id
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            WHERE {$where}
            AND ec.nombre = 'Reservada'
            AND c.fecha >= CURDATE()
        ";

        $resultadoCitas = $db->query($queryCitas);
        $rowCitas = $resultadoCitas ? $resultadoCitas->fetch_assoc() : [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'activos' => (int)($row['activos'] ?? 0),
            'inactivos' => (int)($row['inactivos'] ?? 0),
            'confirmados' => (int)($row['confirmados'] ?? 0),
            'citas_proximas' => (int)($rowCitas['citas_proximas'] ?? 0)
        ];
    }

    private static function construirWhere($q, $estado) {
        global $db;

        $where = ["1 = 1"];

        if($estado === 'activo') {
            $where[] = "cu.activa = 1";
        }

        if($estado === 'inactivo') {
            $where[] = "cu.activa = 0";
        }

        if($q !== '') {
            $qSQL = $db->escape_string($q);
            $where[] = "(
                cl.nombre LIKE '%{$qSQL}%'
                OR cl.apellido_paterno LIKE '%{$qSQL}%'
                OR cl.apellido_materno LIKE '%{$qSQL}%'
                OR cl.telefono LIKE '%{$qSQL}%'
                OR cu.email LIKE '%{$qSQL}%'
            )";
        }

        return implode(' AND ', $where);
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

<?php

namespace Controllers;

use MVC\Router;

class AdminController {

    public static function index(Router $router) {
        isAdmin();

        global $db;

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $partesFecha = explode('-', $fecha);

        if(count($partesFecha) !== 3 || !checkdate((int)$partesFecha[1], (int)$partesFecha[2], (int)$partesFecha[0])) {
            $fecha = date('Y-m-d');
        }

        $fechaSQL = $db->escape_string($fecha);

        $metricas = [
            'citas_hoy' => 0,
            'ventas_dia' => 0,
            'productos_bajo_stock' => 0,
            'clientes_registrados' => 0,
            'tickets_dia' => 0,
            'utilidad_dia' => 0
        ];

        $resultado = $db->query("
            SELECT COUNT(*) AS total
            FROM citas
            WHERE fecha = '{$fechaSQL}'
        ");
        if($resultado) {
            $metricas['citas_hoy'] = (int)$resultado->fetch_assoc()['total'];
        }

        $resultado = $db->query("
            SELECT
                COALESCE(SUM(total_con_iva), 0) AS total_ventas,
                COALESCE(SUM(utilidad_total), 0) AS utilidad,
                COUNT(*) AS tickets
            FROM ventas
            WHERE DATE(fecha_venta) = '{$fechaSQL}'
        ");
        if($resultado) {
            $row = $resultado->fetch_assoc();
            $metricas['ventas_dia'] = (float)$row['total_ventas'];
            $metricas['utilidad_dia'] = (float)$row['utilidad'];
            $metricas['tickets_dia'] = (int)$row['tickets'];
        }

        $resultado = $db->query("
            SELECT COUNT(*) AS total
            FROM clientes
        ");
        if($resultado) {
            $metricas['clientes_registrados'] = (int)$resultado->fetch_assoc()['total'];
        }

        $resultado = $db->query("
            SELECT COUNT(*) AS total
            FROM (
                SELECT
                    p.id,
                    p.stock_minimo,
                    COALESCE(SUM(lp.cantidad_actual), 0) AS stock_total
                FROM productos p
                LEFT JOIN lotes_producto lp
                    ON lp.producto_id = p.id
                WHERE p.activo = 1
                GROUP BY p.id, p.stock_minimo
                HAVING stock_total <= p.stock_minimo
            ) AS productos_bajos
        ");
        if($resultado) {
            $metricas['productos_bajo_stock'] = (int)$resultado->fetch_assoc()['total'];
        }

        $proximasCitas = self::obtenerProximasCitas($fechaSQL);
        $productosBajoStock = self::obtenerProductosBajoStock();
        $ventasRecientes = self::obtenerVentasRecientes($fechaSQL);
        $productosCaducar = self::obtenerProductosPorCaducar();

        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'fecha' => $fecha,
            'metricas' => $metricas,
            'proximasCitas' => $proximasCitas,
            'productosBajoStock' => $productosBajoStock,
            'ventasRecientes' => $ventasRecientes,
            'productosCaducar' => $productosCaducar
        ]);
    }

    private static function obtenerProximasCitas($fechaSQL) {
        global $db;

        $query = "
            SELECT
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                ec.nombre AS estado,
                CASE
                    WHEN c.cliente_id IS NULL THEN COALESCE(NULLIF(c.cliente_alias_nombre, ''), 'Cliente sin cuenta')
                    ELSE TRIM(CONCAT(cl.nombre, ' ', cl.apellido_paterno))
                END AS cliente,
                CONCAT(co.nombre, ' ', co.apellido_paterno) AS barbero,
                GROUP_CONCAT(s.nombre ORDER BY cs.orden SEPARATOR ' + ') AS servicios,
                SUM(cs.total_con_iva_snapshot) AS total
            FROM citas c
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN colaboradores co ON co.id = c.colaborador_id
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            INNER JOIN citas_servicios cs ON cs.cita_id = c.id
            INNER JOIN servicios s ON s.id = cs.servicio_id
            WHERE c.fecha = '{$fechaSQL}'
            GROUP BY
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                ec.nombre,
                c.cliente_id,
                c.cliente_alias_nombre,
                cl.nombre,
                cl.apellido_paterno,
                barbero
            ORDER BY c.hora_inicio ASC
            LIMIT 8
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerProductosBajoStock() {
        global $db;

        $query = "
            SELECT
                p.id,
                p.nombre,
                p.stock_minimo,
                p.activo,
                COALESCE(SUM(lp.cantidad_actual), 0) AS stock_total,
                ROUND(p.precio_venta_sin_iva + (p.precio_venta_sin_iva * (p.iva_porcentaje / 100)), 2) AS precio_final
            FROM productos p
            LEFT JOIN lotes_producto lp
                ON lp.producto_id = p.id
            WHERE p.activo = 1
            GROUP BY
                p.id,
                p.nombre,
                p.stock_minimo,
                p.activo,
                p.precio_venta_sin_iva,
                p.iva_porcentaje
            HAVING stock_total <= p.stock_minimo
            ORDER BY stock_total ASC, p.nombre ASC
            LIMIT 8
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerVentasRecientes($fechaSQL) {
        global $db;

        $query = "
            SELECT
                v.id,
                v.folio,
                v.fecha_venta,
                v.total_con_iva,
                v.utilidad_total,
                mp.nombre AS metodo_pago,
                CONCAT(c.nombre, ' ', c.apellido_paterno) AS colaborador
            FROM ventas v
            INNER JOIN metodos_pago mp ON mp.id = v.metodo_pago_id
            INNER JOIN colaboradores c ON c.id = v.colaborador_id
            WHERE DATE(v.fecha_venta) = '{$fechaSQL}'
            ORDER BY v.fecha_venta DESC
            LIMIT 6
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerProductosPorCaducar() {
        global $db;

        $query = "
            SELECT
                p.nombre,
                lp.codigo_lote,
                lp.fecha_caducidad,
                lp.cantidad_actual
            FROM lotes_producto lp
            INNER JOIN productos p ON p.id = lp.producto_id
            WHERE lp.fecha_caducidad IS NOT NULL
            AND lp.cantidad_actual > 0
            AND lp.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 45 DAY)
            ORDER BY lp.fecha_caducidad ASC
            LIMIT 6
        ";

        return self::fetchAll($db->query($query));
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

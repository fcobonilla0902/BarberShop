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
            'clientes_registrados' => 0
        ];

        $resultado = $db->query("SELECT COUNT(*) AS total FROM citas WHERE fecha = '{$fechaSQL}'");
        if($resultado) $metricas['citas_hoy'] = (int)$resultado->fetch_assoc()['total'];

        $resultado = $db->query("SELECT COALESCE(SUM(total_con_iva), 0) AS total FROM ventas WHERE DATE(fecha_venta) = '{$fechaSQL}'");
        if($resultado) $metricas['ventas_dia'] = (float)$resultado->fetch_assoc()['total'];

        $resultado = $db->query("SELECT COUNT(*) AS total FROM clientes");
        if($resultado) $metricas['clientes_registrados'] = (int)$resultado->fetch_assoc()['total'];

        $queryStock = "
            SELECT COUNT(*) AS total
            FROM productos p
            LEFT JOIN lotes_producto lp ON lp.producto_id = p.id
            WHERE p.activo = 1
            GROUP BY p.id, p.stock_minimo
            HAVING COALESCE(SUM(lp.cantidad_actual), 0) <= p.stock_minimo
        ";
        $resultado = $db->query($queryStock);
        if($resultado) $metricas['productos_bajo_stock'] = $resultado->num_rows;

        $proximasCitas = [];
        $queryCitas = "
            SELECT 
                c.id,
                c.fecha,
                c.hora_inicio,
                ec.nombre AS estado,
                CONCAT(cl.nombre, ' ', cl.apellido_paterno) AS cliente,
                GROUP_CONCAT(s.nombre ORDER BY cs.orden SEPARATOR ' + ') AS servicios
            FROM citas c
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            INNER JOIN citas_servicios cs ON cs.cita_id = c.id
            INNER JOIN servicios s ON s.id = cs.servicio_id
            WHERE c.fecha = '{$fechaSQL}'
            GROUP BY c.id, c.fecha, c.hora_inicio, ec.nombre, cliente
            ORDER BY c.hora_inicio ASC
            LIMIT 8
        ";
        $resultado = $db->query($queryCitas);
        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $proximasCitas[] = $row;
            }
        }

        $productosBajoStock = [];
        $queryProductos = "
            SELECT
                p.id,
                p.nombre,
                p.stock_minimo,
                COALESCE(SUM(lp.cantidad_actual), 0) AS stock_total
            FROM productos p
            LEFT JOIN lotes_producto lp ON lp.producto_id = p.id
            WHERE p.activo = 1
            GROUP BY p.id, p.nombre, p.stock_minimo
            HAVING stock_total <= p.stock_minimo
            ORDER BY stock_total ASC
            LIMIT 8
        ";
        $resultado = $db->query($queryProductos);
        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $productosBajoStock[] = $row;
            }
        }

        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'fecha' => $fecha,
            'metricas' => $metricas,
            'proximasCitas' => $proximasCitas,
            'productosBajoStock' => $productosBajoStock
        ]);
    }
}

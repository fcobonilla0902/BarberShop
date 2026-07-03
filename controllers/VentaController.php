<?php

namespace Controllers;

use MVC\Router;

class VentaController {

    public static function index(Router $router) {
        isAdmin();

        $mensaje = $_SESSION['mensaje_ventas'] ?? null;
        $ticketId = $_SESSION['ultimo_ticket_id'] ?? null;
        unset($_SESSION['mensaje_ventas'], $_SESSION['ultimo_ticket_id']);

        $router->render('ventas/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'servicios' => self::obtenerServicios(),
            'productos' => self::obtenerProductosConStock(),
            'metodosPago' => self::obtenerMetodosPago(),
            'ticket' => $ticketId ? self::obtenerTicket($ticketId) : null,
            'mensaje' => $mensaje
        ]);
    }

    public static function historial(Router $router) {
        isAdmin();

        global $db;

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $q = trim($_GET['q'] ?? '');

        if(!self::fechaValida($fechaInicio)) {
            $fechaInicio = date('Y-m-01');
        }

        if(!self::fechaValida($fechaFin)) {
            $fechaFin = date('Y-m-d');
        }

        if(strtotime($fechaInicio) > strtotime($fechaFin)) {
            $tmp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $tmp;
        }

        $ventas = self::obtenerHistorialVentas($fechaInicio, $fechaFin, $q);
        $metricas = self::obtenerMetricasHistorial($fechaInicio, $fechaFin, $q);

        $router->render('ventas/historial', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'ventas' => $ventas,
            'metricas' => $metricas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'q' => $q
        ]);
    }

    public static function ticket(Router $router) {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /ventas/historial');
            exit;
        }

        $ticket = self::obtenerTicket($id);

        if(!$ticket) {
            header('Location: /ventas/historial');
            exit;
        }

        $router->render('ventas/ticket', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'ticket' => $ticket
        ]);
    }

    public static function crear() {
        isAdmin();
        global $db;

        $items = json_decode($_POST['items_json'] ?? '', true);
        $metodoPagoId = filter_var($_POST['metodo_pago_id'] ?? null, FILTER_VALIDATE_INT);

        if(!$metodoPagoId || !is_array($items) || empty($items)) {
            $_SESSION['mensaje_ventas'] = ['tipo' => 'error', 'texto' => 'Selecciona al menos un producto o servicio.'];
            header('Location: /ventas');
            exit;
        }

        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
        $colaboradorId = (int)($_SESSION['colaborador_id'] ?? $_SESSION['id'] ?? 1);

        $subtotalSinIva = 0;
        $ivaTotal = 0;
        $totalConIva = 0;
        $costoTotalSinIva = 0;
        $serviciosVenta = [];
        $productosVenta = [];

        $db->begin_transaction();

        try {
            foreach($items as $item) {
                $tipo = $item['tipo'] ?? '';
                $id = (int)($item['id'] ?? 0);
                $cantidad = max(1, (int)($item['cantidad'] ?? 1));

                if($tipo === 'servicio') {
                    $servicio = self::obtenerServicioPorId($id);
                    if(!$servicio) throw new \Exception('Uno de los servicios no existe o está inactivo.');

                    $precio = (float)$servicio['precio_base_sin_iva'];
                    $iva = (float)$servicio['iva_porcentaje'];
                    $costo = (float)$servicio['costo_estimado_sin_iva'];

                    $lineaSubtotal = $precio * $cantidad;
                    $lineaIva = round($lineaSubtotal * ($iva / 100), 2);
                    $lineaTotal = round($lineaSubtotal + $lineaIva, 2);
                    $lineaCosto = $costo * $cantidad;
                    $lineaUtilidad = round($lineaSubtotal - $lineaCosto, 2);

                    $subtotalSinIva += $lineaSubtotal;
                    $ivaTotal += $lineaIva;
                    $totalConIva += $lineaTotal;
                    $costoTotalSinIva += $lineaCosto;

                    $serviciosVenta[] = compact('id', 'cantidad') + [
                        'costo_unitario' => $costo,
                        'costo_total' => $lineaCosto,
                        'precio_unitario' => $precio,
                        'iva_porcentaje' => $iva,
                        'iva_monto' => $lineaIva,
                        'total_linea' => $lineaTotal,
                        'utilidad_linea' => $lineaUtilidad
                    ];
                }

                if($tipo === 'producto') {
                    $producto = self::obtenerProductoPorId($id);
                    if(!$producto) throw new \Exception('Uno de los productos no existe o está inactivo.');

                    $lote = self::obtenerLoteDisponible($id, $cantidad);
                    if(!$lote) throw new \Exception('No hay stock suficiente para: ' . $producto['nombre']);

                    $precio = (float)$producto['precio_venta_sin_iva'];
                    $iva = (float)$producto['iva_porcentaje'];
                    $costo = (float)$lote['costo_unitario_sin_iva'];

                    $lineaSubtotal = $precio * $cantidad;
                    $lineaIva = round($lineaSubtotal * ($iva / 100), 2);
                    $lineaTotal = round($lineaSubtotal + $lineaIva, 2);
                    $lineaCosto = $costo * $cantidad;
                    $lineaUtilidad = round($lineaSubtotal - $lineaCosto, 2);

                    $subtotalSinIva += $lineaSubtotal;
                    $ivaTotal += $lineaIva;
                    $totalConIva += $lineaTotal;
                    $costoTotalSinIva += $lineaCosto;

                    $productosVenta[] = compact('id', 'cantidad') + [
                        'lote_id' => (int)$lote['id'],
                        'costo_unitario' => $costo,
                        'costo_total' => $lineaCosto,
                        'precio_unitario' => $precio,
                        'iva_porcentaje' => $iva,
                        'iva_monto' => $lineaIva,
                        'total_linea' => $lineaTotal,
                        'utilidad_linea' => $lineaUtilidad
                    ];
                }
            }

            if(empty($serviciosVenta) && empty($productosVenta)) {
                throw new \Exception('El ticket no contiene artículos válidos.');
            }

            $utilidadTotal = round($subtotalSinIva - $costoTotalSinIva, 2);
            $folio = self::generarFolio();

            $queryVenta = "
                INSERT INTO ventas
                (
                    folio, sucursal_id, cliente_id, colaborador_id, cita_id, metodo_pago_id,
                    fecha_venta, subtotal_sin_iva, iva_total, total_con_iva,
                    costo_total_sin_iva, utilidad_total, created_at, updated_at
                )
                VALUES
                (
                    '{$folio}', {$sucursalId}, NULL, {$colaboradorId}, NULL, {$metodoPagoId},
                    NOW(), " . self::numeroSQL($subtotalSinIva) . ", " . self::numeroSQL($ivaTotal) . ",
                    " . self::numeroSQL($totalConIva) . ", " . self::numeroSQL($costoTotalSinIva) . ",
                    " . self::numeroSQL($utilidadTotal) . ", NOW(), NOW()
                )
            ";

            if(!$db->query($queryVenta)) throw new \Exception($db->error);
            $ventaId = $db->insert_id;

            foreach($serviciosVenta as $servicio) {
                $query = "
                    INSERT INTO venta_servicios
                    (
                        venta_id, servicio_id, cita_servicio_id, colaborador_id, cantidad,
                        costo_estimado_unitario_sin_iva_snapshot, costo_total_estimado_sin_iva,
                        precio_unitario_sin_iva, iva_porcentaje_aplicado, iva_monto,
                        total_linea_con_iva, utilidad_linea
                    )
                    VALUES
                    (
                        {$ventaId}, {$servicio['id']}, NULL, {$colaboradorId}, {$servicio['cantidad']},
                        " . self::numeroSQL($servicio['costo_unitario']) . ",
                        " . self::numeroSQL($servicio['costo_total']) . ",
                        " . self::numeroSQL($servicio['precio_unitario']) . ",
                        " . self::numeroSQL($servicio['iva_porcentaje']) . ",
                        " . self::numeroSQL($servicio['iva_monto']) . ",
                        " . self::numeroSQL($servicio['total_linea']) . ",
                        " . self::numeroSQL($servicio['utilidad_linea']) . "
                    )
                ";

                if(!$db->query($query)) throw new \Exception($db->error);
            }

            foreach($productosVenta as $producto) {
                $query = "
                    INSERT INTO venta_productos
                    (
                        venta_id, producto_id, lote_producto_id, cantidad,
                        costo_unitario_sin_iva_snapshot, costo_total_sin_iva,
                        precio_unitario_sin_iva, iva_porcentaje_aplicado, iva_monto,
                        total_linea_con_iva, utilidad_linea
                    )
                    VALUES
                    (
                        {$ventaId}, {$producto['id']}, {$producto['lote_id']}, {$producto['cantidad']},
                        " . self::numeroSQL($producto['costo_unitario']) . ",
                        " . self::numeroSQL($producto['costo_total']) . ",
                        " . self::numeroSQL($producto['precio_unitario']) . ",
                        " . self::numeroSQL($producto['iva_porcentaje']) . ",
                        " . self::numeroSQL($producto['iva_monto']) . ",
                        " . self::numeroSQL($producto['total_linea']) . ",
                        " . self::numeroSQL($producto['utilidad_linea']) . "
                    )
                ";

                if(!$db->query($query)) throw new \Exception($db->error);
                $ventaProductoId = $db->insert_id;

                $queryStock = "
                    UPDATE lotes_producto
                    SET cantidad_actual = cantidad_actual - {$producto['cantidad']},
                        estado_lote_id = CASE WHEN cantidad_actual - {$producto['cantidad']} <= 0 THEN 2 ELSE estado_lote_id END,
                        updated_at = NOW()
                    WHERE id = {$producto['lote_id']}
                    AND cantidad_actual >= {$producto['cantidad']}
                    LIMIT 1
                ";

                if(!$db->query($queryStock) || $db->affected_rows === 0) {
                    throw new \Exception('No se pudo descontar inventario.');
                }

                $queryMovimiento = "
                    INSERT INTO movimientos_inventario
                    (
                        producto_id, lote_producto_id, tipo_movimiento_inventario_id,
                        venta_producto_id, fecha_movimiento, cantidad, costo_unitario_sin_iva, motivo
                    )
                    VALUES
                    (
                        {$producto['id']}, {$producto['lote_id']}, 2, {$ventaProductoId}, NOW(),
                        -" . (int)$producto['cantidad'] . ",
                        " . self::numeroSQL($producto['costo_unitario']) . ",
                        'Salida por venta {$folio}'
                    )
                ";

                if(!$db->query($queryMovimiento)) throw new \Exception($db->error);
            }

            $db->commit();

            $_SESSION['ultimo_ticket_id'] = $ventaId;
            $_SESSION['mensaje_ventas'] = ['tipo' => 'exito', 'texto' => 'Venta registrada correctamente.'];
            header('Location: /ventas');
            exit;

        } catch(\Throwable $e) {
            $db->rollback();
            $_SESSION['mensaje_ventas'] = ['tipo' => 'error', 'texto' => $e->getMessage() ?: 'No se pudo registrar la venta.'];
            header('Location: /ventas');
            exit;
        }
    }

    private static function obtenerHistorialVentas($fechaInicio, $fechaFin, $q) {
        global $db;

        $fechaInicioSQL = $db->escape_string($fechaInicio);
        $fechaFinSQL = $db->escape_string($fechaFin);
        $where = [
            "DATE(v.fecha_venta) BETWEEN '{$fechaInicioSQL}' AND '{$fechaFinSQL}'"
        ];

        if($q !== '') {
            $qSQL = $db->escape_string($q);
            $where[] = "(
                v.folio LIKE '%{$qSQL}%'
                OR mp.nombre LIKE '%{$qSQL}%'
                OR col.nombre LIKE '%{$qSQL}%'
                OR col.apellido_paterno LIKE '%{$qSQL}%'
            )";
        }

        $whereSQL = implode(' AND ', $where);

        $query = "
            SELECT
                v.id,
                v.folio,
                v.fecha_venta,
                v.subtotal_sin_iva,
                v.iva_total,
                v.total_con_iva,
                v.costo_total_sin_iva,
                v.utilidad_total,
                mp.nombre AS metodo_pago,
                CONCAT(col.nombre, ' ', col.apellido_paterno) AS colaborador,
                (
                    SELECT COUNT(*)
                    FROM venta_productos vp
                    WHERE vp.venta_id = v.id
                ) AS productos_count,
                (
                    SELECT COUNT(*)
                    FROM venta_servicios vs
                    WHERE vs.venta_id = v.id
                ) AS servicios_count
            FROM ventas v
            INNER JOIN metodos_pago mp ON mp.id = v.metodo_pago_id
            INNER JOIN colaboradores col ON col.id = v.colaborador_id
            WHERE {$whereSQL}
            ORDER BY v.fecha_venta DESC
            LIMIT 150
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerMetricasHistorial($fechaInicio, $fechaFin, $q) {
        global $db;

        $fechaInicioSQL = $db->escape_string($fechaInicio);
        $fechaFinSQL = $db->escape_string($fechaFin);
        $where = [
            "DATE(v.fecha_venta) BETWEEN '{$fechaInicioSQL}' AND '{$fechaFinSQL}'"
        ];

        if($q !== '') {
            $qSQL = $db->escape_string($q);
            $where[] = "(
                v.folio LIKE '%{$qSQL}%'
                OR mp.nombre LIKE '%{$qSQL}%'
                OR col.nombre LIKE '%{$qSQL}%'
                OR col.apellido_paterno LIKE '%{$qSQL}%'
            )";
        }

        $whereSQL = implode(' AND ', $where);

        $resultado = $db->query("
            SELECT
                COUNT(*) AS tickets,
                COALESCE(SUM(v.total_con_iva), 0) AS total,
                COALESCE(SUM(v.costo_total_sin_iva), 0) AS costo,
                COALESCE(SUM(v.utilidad_total), 0) AS utilidad
            FROM ventas v
            INNER JOIN metodos_pago mp ON mp.id = v.metodo_pago_id
            INNER JOIN colaboradores col ON col.id = v.colaborador_id
            WHERE {$whereSQL}
        ");

        $row = $resultado ? $resultado->fetch_assoc() : [];

        return [
            'tickets' => (int)($row['tickets'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'costo' => (float)($row['costo'] ?? 0),
            'utilidad' => (float)($row['utilidad'] ?? 0)
        ];
    }

    private static function obtenerServicios() {
        global $db;
        $resultado = $db->query("
            SELECT id, nombre, duracion_minutos, precio_base_sin_iva, costo_estimado_sin_iva, iva_porcentaje,
                   ROUND(precio_base_sin_iva + (precio_base_sin_iva * (iva_porcentaje / 100)), 2) AS precio_final
            FROM servicios
            WHERE activo = 1
            ORDER BY nombre ASC
        ");

        return self::fetchAll($resultado);
    }

    private static function obtenerProductosConStock() {
        global $db;
        $resultado = $db->query("
            SELECT p.id, p.nombre, p.precio_venta_sin_iva, p.costo_referencia_sin_iva, p.iva_porcentaje,
                   ROUND(p.precio_venta_sin_iva + (p.precio_venta_sin_iva * (p.iva_porcentaje / 100)), 2) AS precio_final,
                   COALESCE(SUM(lp.cantidad_actual), 0) AS stock_total
            FROM productos p
            LEFT JOIN lotes_producto lp ON lp.producto_id = p.id AND lp.estado_lote_id = 1
            WHERE p.activo = 1
            GROUP BY p.id
            HAVING stock_total > 0
            ORDER BY p.nombre ASC
        ");

        return self::fetchAll($resultado);
    }

    private static function obtenerMetodosPago() {
        global $db;
        return self::fetchAll($db->query("SELECT id, nombre FROM metodos_pago WHERE activo = 1 ORDER BY id ASC"));
    }

    private static function obtenerServicioPorId($id) {
        global $db;
        $id = (int)$id;
        $resultado = $db->query("SELECT * FROM servicios WHERE id = {$id} AND activo = 1 LIMIT 1");
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function obtenerProductoPorId($id) {
        global $db;
        $id = (int)$id;
        $resultado = $db->query("SELECT * FROM productos WHERE id = {$id} AND activo = 1 LIMIT 1");
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function obtenerLoteDisponible($productoId, $cantidad) {
        global $db;
        $productoId = (int)$productoId;
        $cantidad = (int)$cantidad;

        $resultado = $db->query("
            SELECT *
            FROM lotes_producto
            WHERE producto_id = {$productoId}
            AND estado_lote_id = 1
            AND cantidad_actual >= {$cantidad}
            ORDER BY CASE WHEN fecha_caducidad IS NULL THEN 1 ELSE 0 END ASC, fecha_caducidad ASC, fecha_entrada ASC
            LIMIT 1
        ");

        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function obtenerTicket($ventaId) {
        global $db;
        $ventaId = (int)$ventaId;

        $resultado = $db->query("
            SELECT
                v.*,
                mp.nombre AS metodo_pago,
                CONCAT(col.nombre, ' ', col.apellido_paterno) AS colaborador,
                s.nombre_comercial AS sucursal,
                s.telefono AS sucursal_telefono,
                s.email AS sucursal_email
            FROM ventas v
            INNER JOIN metodos_pago mp ON mp.id = v.metodo_pago_id
            INNER JOIN colaboradores col ON col.id = v.colaborador_id
            INNER JOIN sucursales s ON s.id = v.sucursal_id
            WHERE v.id = {$ventaId}
            LIMIT 1
        ");

        $venta = $resultado ? $resultado->fetch_assoc() : null;
        if(!$venta) return null;

        $items = [];

        $rs = $db->query("
            SELECT
                s.nombre,
                vs.cantidad,
                vs.precio_unitario_sin_iva,
                vs.iva_monto,
                vs.total_linea_con_iva,
                vs.utilidad_linea,
                'Servicio' AS tipo,
                NULL AS lote
            FROM venta_servicios vs
            INNER JOIN servicios s ON s.id = vs.servicio_id
            WHERE vs.venta_id = {$ventaId}
        ");
        $items = array_merge($items, self::fetchAll($rs));

        $rp = $db->query("
            SELECT
                p.nombre,
                vp.cantidad,
                vp.precio_unitario_sin_iva,
                vp.iva_monto,
                vp.total_linea_con_iva,
                vp.utilidad_linea,
                'Producto' AS tipo,
                lp.codigo_lote AS lote
            FROM venta_productos vp
            INNER JOIN productos p ON p.id = vp.producto_id
            INNER JOIN lotes_producto lp ON lp.id = vp.lote_producto_id
            WHERE vp.venta_id = {$ventaId}
        ");
        $items = array_merge($items, self::fetchAll($rp));

        return ['venta' => $venta, 'items' => $items];
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

    private static function generarFolio() {
        return 'TCK' . date('ymdHis') . rand(10, 99);
    }

    private static function numeroSQL($numero) {
        return number_format((float)$numero, 2, '.', '');
    }

    private static function fechaValida($fecha) {
        $partes = explode('-', $fecha);
        if(count($partes) !== 3) return false;
        return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
    }
}

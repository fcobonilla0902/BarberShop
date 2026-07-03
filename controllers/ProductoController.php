<?php

namespace Controllers;

use MVC\Router;
use Model\CategoriaProducto;
use Model\EstadoLote;
use Model\LoteProducto;
use Model\Producto;
use Model\Proveedor;

class ProductoController {

    public static function index(Router $router) {
        isAdmin();

        $productos = Producto::todosConStock();

        $router->render('productos/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'productos' => $productos
        ]);
    }

    public static function lotes(Router $router) {
        isAdmin();

        $productoId = filter_var($_GET['producto_id'] ?? null, FILTER_VALIDATE_INT);
        $estadoId = filter_var($_GET['estado_id'] ?? null, FILTER_VALIDATE_INT);
        $caducidad = trim($_GET['caducidad'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $productos = Producto::todosConStock();
        $estados = EstadoLote::activos();
        $lotes = self::obtenerLotes($productoId, $estadoId, $caducidad, $q);
        $metricas = self::obtenerMetricasLotes($productoId, $estadoId, $caducidad, $q);

        $router->render('productos/lotes', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'productos' => $productos,
            'estados' => $estados,
            'lotes' => $lotes,
            'metricas' => $metricas,
            'productoId' => $productoId,
            'estadoId' => $estadoId,
            'caducidad' => $caducidad,
            'q' => $q
        ]);
    }

    public static function crear(Router $router) {
        isAdmin();

        $producto = new Producto;
        $categorias = CategoriaProducto::activas();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto = new Producto($_POST);
            $producto->codigo_barras = $_POST['codigo_barras'] !== '' ? $_POST['codigo_barras'] : null;
            $producto->imagen_url = $_POST['imagen_url'] !== '' ? $_POST['imagen_url'] : null;
            $producto->created_at = date('Y-m-d H:i:s');
            $producto->updated_at = date('Y-m-d H:i:s');

            $alertas = $producto->validar();

            if(empty($alertas)) {
                $resultado = $producto->guardar();

                if($resultado['resultado']) {
                    header('Location: /productos');
                    exit;
                }

                Producto::setAlerta('error', 'No se pudo guardar el producto. Revisa si el código de barras ya existe.');
            }
        }

        $alertas = Producto::getAlertas();

        $router->render('productos/crear', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'producto' => $producto,
            'categorias' => $categorias,
            'alertas' => $alertas
        ]);
    }

    public static function crearLote(Router $router) {
        isAdmin();

        $lote = new LoteProducto([
            'producto_id' => $_GET['producto_id'] ?? ''
        ]);

        $productos = Producto::todosConStock();
        $proveedores = Proveedor::activos();
        $estados = EstadoLote::activos();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lote = new LoteProducto($_POST);
            $lote->proveedor_id = $_POST['proveedor_id'] !== '' ? $_POST['proveedor_id'] : null;
            $lote->fecha_caducidad = $_POST['fecha_caducidad'] !== '' ? $_POST['fecha_caducidad'] : null;
            $lote->cantidad_actual = $lote->cantidad_inicial;
            $lote->created_at = date('Y-m-d H:i:s');
            $lote->updated_at = date('Y-m-d H:i:s');

            $alertas = $lote->validar();

            if(empty($alertas)) {
                $resultado = $lote->guardar();

                if($resultado['resultado']) {
                    self::registrarMovimientoEntrada($lote, $resultado['id']);
                    header('Location: /productos/lotes?producto_id=' . (int)$lote->producto_id);
                    exit;
                }

                LoteProducto::setAlerta('error', 'No se pudo guardar el lote. Revisa si el código de lote ya existe para ese producto.');
            }
        }

        $alertas = LoteProducto::getAlertas();

        $router->render('productos/lote', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'lote' => $lote,
            'productos' => $productos,
            'proveedores' => $proveedores,
            'estados' => $estados,
            'alertas' => $alertas
        ]);
    }

    public static function desactivar() {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if($id) {
                $producto = Producto::find($id);

                if($producto) {
                    $producto->activo = '0';
                    $producto->updated_at = date('Y-m-d H:i:s');
                    $producto->guardar();
                }
            }
        }

        header('Location: /productos');
        exit;
    }

    private static function obtenerLotes($productoId, $estadoId, $caducidad, $q) {
        global $db;

        $where = self::construirWhereLotes($productoId, $estadoId, $caducidad, $q);

        $query = "
            SELECT
                lp.id,
                lp.producto_id,
                p.nombre AS producto,
                p.marca,
                cp.nombre AS categoria,
                lp.codigo_lote,
                lp.fecha_entrada,
                lp.fecha_caducidad,
                lp.cantidad_inicial,
                lp.cantidad_actual,
                lp.costo_unitario_sin_iva,
                el.nombre AS estado,
                COALESCE(pr.nombre_comercial, 'Sin proveedor') AS proveedor,
                CASE
                    WHEN lp.fecha_caducidad IS NULL THEN 'Sin caducidad'
                    WHEN lp.fecha_caducidad < CURDATE() THEN 'Caducado'
                    WHEN lp.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 45 DAY) THEN 'Por caducar'
                    ELSE 'Vigente'
                END AS estado_caducidad
            FROM lotes_producto lp
            INNER JOIN productos p ON p.id = lp.producto_id
            INNER JOIN categorias_producto cp ON cp.id = p.categoria_producto_id
            INNER JOIN estados_lote el ON el.id = lp.estado_lote_id
            LEFT JOIN proveedores pr ON pr.id = lp.proveedor_id
            WHERE {$where}
            ORDER BY
                CASE
                    WHEN lp.fecha_caducidad IS NULL THEN 3
                    WHEN lp.fecha_caducidad < CURDATE() THEN 0
                    WHEN lp.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 45 DAY) THEN 1
                    ELSE 2
                END ASC,
                lp.fecha_caducidad ASC,
                p.nombre ASC,
                lp.codigo_lote ASC
            LIMIT 200
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerMetricasLotes($productoId, $estadoId, $caducidad, $q) {
        global $db;

        $where = self::construirWhereLotes($productoId, $estadoId, $caducidad, $q);

        $query = "
            SELECT
                COUNT(*) AS total_lotes,
                COALESCE(SUM(lp.cantidad_inicial), 0) AS cantidad_inicial,
                COALESCE(SUM(lp.cantidad_actual), 0) AS cantidad_actual,
                SUM(CASE WHEN lp.cantidad_actual <= 0 OR el.nombre = 'Agotado' THEN 1 ELSE 0 END) AS agotados,
                SUM(CASE WHEN lp.fecha_caducidad IS NOT NULL AND lp.fecha_caducidad < CURDATE() THEN 1 ELSE 0 END) AS caducados,
                SUM(CASE WHEN lp.fecha_caducidad IS NOT NULL AND lp.fecha_caducidad >= CURDATE() AND lp.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 45 DAY) THEN 1 ELSE 0 END) AS por_caducar
            FROM lotes_producto lp
            INNER JOIN productos p ON p.id = lp.producto_id
            INNER JOIN categorias_producto cp ON cp.id = p.categoria_producto_id
            INNER JOIN estados_lote el ON el.id = lp.estado_lote_id
            LEFT JOIN proveedores pr ON pr.id = lp.proveedor_id
            WHERE {$where}
        ";

        $resultado = $db->query($query);
        $row = $resultado ? $resultado->fetch_assoc() : [];

        return [
            'total_lotes' => (int)($row['total_lotes'] ?? 0),
            'cantidad_inicial' => (int)($row['cantidad_inicial'] ?? 0),
            'cantidad_actual' => (int)($row['cantidad_actual'] ?? 0),
            'agotados' => (int)($row['agotados'] ?? 0),
            'caducados' => (int)($row['caducados'] ?? 0),
            'por_caducar' => (int)($row['por_caducar'] ?? 0)
        ];
    }

    private static function construirWhereLotes($productoId, $estadoId, $caducidad, $q) {
        global $db;

        $where = ["1 = 1"];

        if($productoId) {
            $where[] = "lp.producto_id = " . (int)$productoId;
        }

        if($estadoId) {
            $where[] = "lp.estado_lote_id = " . (int)$estadoId;
        }

        if($caducidad === 'vigentes') {
            $where[] = "(lp.fecha_caducidad IS NULL OR lp.fecha_caducidad > DATE_ADD(CURDATE(), INTERVAL 45 DAY))";
        }

        if($caducidad === 'por_caducar') {
            $where[] = "lp.fecha_caducidad IS NOT NULL AND lp.fecha_caducidad >= CURDATE() AND lp.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 45 DAY)";
        }

        if($caducidad === 'caducados') {
            $where[] = "lp.fecha_caducidad IS NOT NULL AND lp.fecha_caducidad < CURDATE()";
        }

        if($caducidad === 'sin_caducidad') {
            $where[] = "lp.fecha_caducidad IS NULL";
        }

        if($q !== '') {
            $qSQL = $db->escape_string($q);
            $where[] = "(
                p.nombre LIKE '%{$qSQL}%'
                OR p.marca LIKE '%{$qSQL}%'
                OR lp.codigo_lote LIKE '%{$qSQL}%'
                OR pr.nombre_comercial LIKE '%{$qSQL}%'
                OR cp.nombre LIKE '%{$qSQL}%'
            )";
        }

        return implode(' AND ', $where);
    }

    private static function registrarMovimientoEntrada(LoteProducto $lote, $loteId) {
        global $db;

        $productoId = (int)$lote->producto_id;
        $loteId = (int)$loteId;
        $cantidad = (int)$lote->cantidad_inicial;
        $costo = number_format((float)$lote->costo_unitario_sin_iva, 2, '.', '');

        $query = "
            INSERT INTO movimientos_inventario
            (
                producto_id,
                lote_producto_id,
                tipo_movimiento_inventario_id,
                venta_producto_id,
                fecha_movimiento,
                cantidad,
                costo_unitario_sin_iva,
                motivo
            )
            VALUES
            (
                {$productoId},
                {$loteId},
                1,
                NULL,
                NOW(),
                {$cantidad},
                {$costo},
                'Entrada por lote registrado desde panel admin'
            )
        ";

        $db->query($query);
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

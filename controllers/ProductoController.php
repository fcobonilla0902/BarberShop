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
                    header('Location: /productos');
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
}

<?php

namespace Controllers;

use MVC\Router;
use Model\Servicio;
use Model\CategoriaServicio;

class ServicioController {

    public static function index(Router $router) {
        isAdmin();

        $servicios = Servicio::todosConCategoria();

        $router->render('servicios/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'servicios' => $servicios
        ]);
    }

    public static function crear(Router $router) {
        isAdmin();

        $servicio = new Servicio;
        $categorias = CategoriaServicio::activas();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio = new Servicio($_POST);
            $servicio->activo = $_POST['activo'] ?? '1';
            $servicio->created_at = date('Y-m-d H:i:s');
            $servicio->updated_at = date('Y-m-d H:i:s');

            $alertas = $servicio->validar();

            if(empty($alertas)) {
                $resultado = $servicio->guardar();

                if($resultado['resultado']) {
                    header('Location: /servicios');
                    exit;
                }

                Servicio::setAlerta('error', 'No se pudo guardar el servicio');
            }
        }

        $alertas = Servicio::getAlertas();

        $router->render('servicios/crear', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'servicio' => $servicio,
            'categorias' => $categorias,
            'alertas' => $alertas
        ]);
    }

    public static function actualizar(Router $router) {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /servicios');
            exit;
        }

        $servicio = Servicio::find($id);

        if(!$servicio) {
            header('Location: /servicios');
            exit;
        }

        $categorias = CategoriaServicio::activas();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio->sincronizar($_POST);
            $servicio->updated_at = date('Y-m-d H:i:s');

            $alertas = $servicio->validar();

            if(empty($alertas)) {
                $resultado = $servicio->guardar();

                if($resultado) {
                    header('Location: /servicios');
                    exit;
                }

                Servicio::setAlerta('error', 'No se pudo actualizar el servicio');
            }
        }

        $alertas = Servicio::getAlertas();

        $router->render('servicios/actualizar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'servicio' => $servicio,
            'categorias' => $categorias,
            'alertas' => $alertas
        ]);
    }

    public static function eliminar() {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if(!$id) {
                header('Location: /servicios');
                exit;
            }

            $servicio = Servicio::find($id);

            if($servicio) {
                // Baja lógica para no romper citas o ventas históricas por llaves foráneas.
                $servicio->activo = '0';
                $servicio->updated_at = date('Y-m-d H:i:s');
                $servicio->guardar();
            }
        }

        header('Location: /servicios');
        exit;
    }
}

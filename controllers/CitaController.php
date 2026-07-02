<?php

namespace Controllers;

use MVC\Router;

class CitaController {
    public static function index(Router $router) {
        isAuth();

        if(($_SESSION['tipo_usuario'] ?? '') !== 'cliente') {
            header('Location: /admin');
            exit;
        }

        $router->render('cita/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'id' => $_SESSION['cliente_id'] ?? $_SESSION['id'] ?? ''
        ]);
    }
}

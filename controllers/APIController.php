<?php

namespace Controllers;

use Model\Servicio;

class APIController {
    public static function index() {
        header('Content-Type: application/json; charset=utf-8');

        $servicios = Servicio::activosConCategoria();
        $respuesta = [];

        foreach($servicios as $servicio) {
            $precioSinIva = (float)$servicio->precio_base_sin_iva;
            $ivaPorcentaje = (float)$servicio->iva_porcentaje;
            $ivaMonto = round($precioSinIva * ($ivaPorcentaje / 100), 2);
            $precioFinal = round($precioSinIva + $ivaMonto, 2);

            $respuesta[] = [
                'id' => (int)$servicio->id,
                'nombre' => $servicio->nombre,
                'descripcion' => $servicio->descripcion,
                'categoria_servicio_id' => (int)$servicio->categoria_servicio_id,
                'categoria' => $servicio->categoria,
                'duracion_minutos' => (int)$servicio->duracion_minutos,
                'precio_base_sin_iva' => $precioSinIva,
                'iva_porcentaje' => $ivaPorcentaje,
                'iva_monto' => $ivaMonto,
                'precio_final' => $precioFinal,

                // Compatibilidad con app.js actual
                'precio' => $precioFinal
            ];
        }

        echo json_encode($respuesta);
    }

    public static function guardar() {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'La creación de citas con bloques de agenda se implementa en el Issue 5.'
        ]);
    }

    public static function eliminar() {
        header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/admin'));
        exit;
    }
}

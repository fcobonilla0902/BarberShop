<?php

namespace Controllers;

class APIController {
    public static function index() {
        global $db;

        header('Content-Type: application/json; charset=utf-8');

        $query = "
            SELECT
                s.id,
                s.nombre,
                s.descripcion,
                s.duracion_minutos,
                s.precio_base_sin_iva,
                s.iva_porcentaje,
                cs.nombre AS categoria
            FROM servicios s
            INNER JOIN categorias_servicio cs ON cs.id = s.categoria_servicio_id
            WHERE s.activo = 1
            ORDER BY cs.nombre, s.nombre
        ";

        $resultado = $db->query($query);
        $servicios = [];

        if($resultado) {
            while($servicio = $resultado->fetch_assoc()) {
                $precioSinIva = (float)$servicio['precio_base_sin_iva'];
                $ivaPorcentaje = (float)$servicio['iva_porcentaje'];
                $ivaMonto = round($precioSinIva * ($ivaPorcentaje / 100), 2);
                $precioConIva = round($precioSinIva + $ivaMonto, 2);

                $servicios[] = [
                    'id' => (int)$servicio['id'],
                    'nombre' => $servicio['nombre'],
                    'descripcion' => $servicio['descripcion'],
                    'categoria' => $servicio['categoria'],
                    'duracion_minutos' => (int)$servicio['duracion_minutos'],
                    'precio_base_sin_iva' => $precioSinIva,
                    'iva_porcentaje' => $ivaPorcentaje,
                    'iva_monto' => $ivaMonto,
                    'precio' => $precioConIva
                ];
            }
        }

        echo json_encode($servicios);
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

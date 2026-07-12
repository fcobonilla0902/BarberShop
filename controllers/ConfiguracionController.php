<?php

namespace Controllers;

use MVC\Router;

class ConfiguracionController {

    public static function index(Router $router) {
        isAdmin();

        $sucursal = self::obtenerSucursalPrincipal();
        $alertas = [];
        $mensaje = $_SESSION['mensaje_configuracion'] ?? null;
        unset($_SESSION['mensaje_configuracion']);

        if(!$sucursal) {
            $sucursal = self::crearSucursalBase();
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = self::limpiarDatos($_POST);
            $alertas = self::validar($datos);

            if(empty($alertas)) {
                $resultado = self::actualizarSucursal((int)$sucursal['id'], $datos);

                if($resultado) {
                    $_SESSION['mensaje_configuracion'] = [
                        'tipo' => 'exito',
                        'texto' => 'Configuración de sucursal actualizada correctamente.'
                    ];

                    header('Location: /configuracion');
                    exit;
                }

                $alertas['error'][] = 'No se pudo actualizar la configuración.';
            }

            $sucursal = array_merge($sucursal, $datos);
        }

        $router->render('configuracion/index', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'sucursal' => $sucursal,
            'alertas' => $alertas,
            'mensaje' => $mensaje
        ]);
    }

    private static function obtenerSucursalPrincipal() {
        global $db;

        $resultado = $db->query("
            SELECT *
            FROM sucursales
            ORDER BY activo DESC, id ASC
            LIMIT 1
        ");

        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function crearSucursalBase() {
        global $db;

        $query = "
            INSERT INTO sucursales
            (
                nombre_comercial,
                razon_social,
                telefono,
                email,
                calle,
                numero_exterior,
                numero_interior,
                colonia,
                municipio,
                estado,
                codigo_postal,
                bloque_agenda_minutos,
                activo,
                created_at,
                updated_at
            )
            VALUES
            (
                'BarberShop',
                NULL,
                '',
                '',
                'Calle principal',
                'S/N',
                NULL,
                'Centro',
                'Monterrey',
                'Nuevo León',
                '64000',
                15,
                1,
                NOW(),
                NOW()
            )
        ";

        $db->query($query);

        return self::obtenerSucursalPrincipal();
    }

    private static function limpiarDatos($post) {
        return [
            'nombre_comercial' => trim($post['nombre_comercial'] ?? ''),
            'razon_social' => trim($post['razon_social'] ?? ''),
            'telefono' => trim($post['telefono'] ?? ''),
            'email' => trim($post['email'] ?? ''),
            'calle' => trim($post['calle'] ?? ''),
            'numero_exterior' => trim($post['numero_exterior'] ?? ''),
            'numero_interior' => trim($post['numero_interior'] ?? ''),
            'colonia' => trim($post['colonia'] ?? ''),
            'municipio' => trim($post['municipio'] ?? ''),
            'estado' => trim($post['estado'] ?? ''),
            'codigo_postal' => trim($post['codigo_postal'] ?? ''),
            'bloque_agenda_minutos' => (int)($post['bloque_agenda_minutos'] ?? 15)
        ];
    }

    private static function validar($datos) {
        $alertas = [];

        if($datos['nombre_comercial'] === '') {
            $alertas['error'][] = 'El nombre del negocio es obligatorio.';
        }

        if(strlen($datos['nombre_comercial']) > 80) {
            $alertas['error'][] = 'El nombre del negocio no debe superar 80 caracteres.';
        }

        if($datos['razon_social'] !== '' && strlen($datos['razon_social']) > 120) {
            $alertas['error'][] = 'La razón social no debe superar 120 caracteres.';
        }

        if($datos['telefono'] === '') {
            $alertas['error'][] = 'El teléfono es obligatorio.';
        }

        if(strlen($datos['telefono']) > 20) {
            $alertas['error'][] = 'El teléfono no debe superar 20 caracteres.';
        }

        if($datos['email'] !== '' && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $alertas['error'][] = 'El correo de la sucursal no es válido.';
        }

        if($datos['calle'] === '') {
            $alertas['error'][] = 'La calle es obligatoria.';
        }

        if($datos['numero_exterior'] === '') {
            $alertas['error'][] = 'El número exterior es obligatorio.';
        }

        if($datos['colonia'] === '') {
            $alertas['error'][] = 'La colonia es obligatoria.';
        }

        if($datos['municipio'] === '') {
            $alertas['error'][] = 'El municipio es obligatorio.';
        }

        if($datos['estado'] === '') {
            $alertas['error'][] = 'El estado es obligatorio.';
        }

        if($datos['codigo_postal'] === '') {
            $alertas['error'][] = 'El código postal es obligatorio.';
        }

        if($datos['codigo_postal'] !== '' && !preg_match('/^\d{5}$/', $datos['codigo_postal'])) {
            $alertas['error'][] = 'El código postal debe tener 5 dígitos.';
        }

        if(!in_array((int)$datos['bloque_agenda_minutos'], [15, 20], true)) {
            $alertas['error'][] = 'El bloque de agenda debe ser de 15 o 20 minutos.';
        }

        return $alertas;
    }

    private static function actualizarSucursal($id, $datos) {
        global $db;

        $nombre = $db->escape_string($datos['nombre_comercial']);
        $razon = $datos['razon_social'] !== '' ? "'" . $db->escape_string($datos['razon_social']) . "'" : "NULL";
        $telefono = $db->escape_string($datos['telefono']);
        $email = $datos['email'] !== '' ? "'" . $db->escape_string($datos['email']) . "'" : "NULL";
        $calle = $db->escape_string($datos['calle']);
        $numeroExterior = $db->escape_string($datos['numero_exterior']);
        $numeroInterior = $datos['numero_interior'] !== '' ? "'" . $db->escape_string($datos['numero_interior']) . "'" : "NULL";
        $colonia = $db->escape_string($datos['colonia']);
        $municipio = $db->escape_string($datos['municipio']);
        $estado = $db->escape_string($datos['estado']);
        $codigoPostal = $db->escape_string($datos['codigo_postal']);
        $bloque = (int)$datos['bloque_agenda_minutos'];

        $query = "
            UPDATE sucursales
            SET
                nombre_comercial = '{$nombre}',
                razon_social = {$razon},
                telefono = '{$telefono}',
                email = {$email},
                calle = '{$calle}',
                numero_exterior = '{$numeroExterior}',
                numero_interior = {$numeroInterior},
                colonia = '{$colonia}',
                municipio = '{$municipio}',
                estado = '{$estado}',
                codigo_postal = '{$codigoPostal}',
                bloque_agenda_minutos = {$bloque},
                activo = 1,
                updated_at = NOW()
            WHERE id = {$id}
            LIMIT 1
        ";

        return $db->query($query);
    }
}

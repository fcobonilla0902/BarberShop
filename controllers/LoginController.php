<?php

namespace Controllers;

use MVC\Router;
use Model\Cuenta;
use Model\Cliente;
use Model\Colaborador;
use Model\ConfirmacionCuenta;
use Classes\Email;

class LoginController {

    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            if(isset($_POST['demo'])) {
                if($_POST['demo'] === 'admin') {
                    $_POST['email'] = 'admin@barbershop.com';
                    $_POST['password'] = '123456';
                }

                if($_POST['demo'] === 'cliente') {
                    $_POST['email'] = 'cliente@barbershop.com';
                    $_POST['password'] = '123456';
                }
            }

            $auth = new Cuenta($_POST);
            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                $cuenta = Cuenta::where('email', $auth->email);

                if($cuenta) {
                    if($cuenta->comprobarPasswordAndVerificado($auth->password)) {
                        self::crearSesion($cuenta);
                    }
                } else {
                    Cuenta::setAlerta('error', 'Cuenta no encontrada');
                }
            }
        }

        $alertas = Cuenta::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }

    private static function crearSesion(Cuenta $cuenta) {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        $_SESSION['login'] = true;
        $_SESSION['cuenta_id'] = $cuenta->id;
        $_SESSION['email'] = $cuenta->email;

        $cliente = Cliente::where('cuenta_id', $cuenta->id);

        if($cliente) {
            $_SESSION['tipo_usuario'] = 'cliente';
            $_SESSION['cliente_id'] = $cliente->id;
            $_SESSION['id'] = $cliente->id;
            $_SESSION['nombre'] = $cliente->nombreCompleto();

            $cuenta->actualizarUltimoAcceso();
            header('Location: /cita');
            exit;
        }

        $colaborador = Colaborador::buscarPorCuenta($cuenta->id);

        if($colaborador) {
            $_SESSION['tipo_usuario'] = 'colaborador';
            $_SESSION['colaborador_id'] = $colaborador->id;
            $_SESSION['id'] = $colaborador->id;
            $_SESSION['nombre'] = $colaborador->nombreCompleto();
            $_SESSION['rol'] = $colaborador->rol;
            $_SESSION['sucursal_id'] = $colaborador->sucursal_id;
            $_SESSION['admin'] = true;

            $cuenta->actualizarUltimoAcceso();
            header('Location: /admin');
            exit;
        }

        Cuenta::setAlerta('error', 'La cuenta no tiene cliente ni colaborador relacionado');
    }

    public static function logout() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        header('Location: /');
        exit;
    }

    public static function crear(Router $router) {
        $cuenta = new Cuenta;
        $cliente = new Cliente;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cuenta = new Cuenta($_POST);
            $cliente = new Cliente($_POST);

            $alertas = array_merge(
                $cuenta->validarNuevaCuenta(),
                $cliente->validarNuevaCuenta()
            );

            if(empty($alertas)) {
                $cuenta->existeEmail();
                $alertas = Cuenta::getAlertas();

                if(empty($alertas)) {
                    $cuenta->hashPassword();
                    $cuenta->activa = '1';
                    $cuenta->cuenta_confirmada = '0';
                    $cuenta->created_at = date('Y-m-d H:i:s');
                    $cuenta->updated_at = date('Y-m-d H:i:s');

                    $resultadoCuenta = $cuenta->guardar();

                    if($resultadoCuenta['resultado']) {
                        $cuentaId = $resultadoCuenta['id'];

                        $cliente->cuenta_id = $cuentaId;
                        $cliente->created_at = date('Y-m-d H:i:s');
                        $cliente->updated_at = date('Y-m-d H:i:s');
                        $cliente->guardar();

                        $tokenPlano = Cuenta::crearTokenPlano();

                        $confirmacion = new ConfirmacionCuenta([
                            'cuenta_id' => $cuentaId,
                            'token_hash' => Cuenta::hashToken($tokenPlano),
                            'fecha_creacion' => date('Y-m-d H:i:s'),
                            'fecha_expiracion' => date('Y-m-d H:i:s', strtotime('+1 day')),
                            'usado' => '0'
                        ]);

                        $confirmacion->guardar();

                        try {
                            $email = new Email($cuenta->email, $cliente->nombre, $tokenPlano);
                            $email->enviarConfirmacion();
                        } catch(\Throwable $e) {
                            // Para la entrega local no detenemos el registro si el SMTP falla.
                        }

                        header('Location: /mensaje');
                        exit;
                    }

                    Cuenta::setAlerta('error', 'No se pudo crear la cuenta');
                }
            }
        }

        $alertas = array_merge(Cuenta::getAlertas(), Cliente::getAlertas());

        $router->render('auth/crear-cuenta', [
            'cuenta' => $cuenta,
            'cliente' => $cliente,
            'alertas' => $alertas
        ]);
    }

    public static function confirmar(Router $router) {
        $alertas = [];
        $token = s($_GET['token'] ?? '');

        if(!$token) {
            Cuenta::setAlerta('error', 'Token no válido');
        } else {
            $tokenHash = Cuenta::hashToken($token);
            $confirmacion = ConfirmacionCuenta::where('token_hash', $tokenHash);

            if(!$confirmacion) {
                Cuenta::setAlerta('error', 'Token no válido');
            } elseif(!$confirmacion->estaVigente()) {
                Cuenta::setAlerta('error', 'El token ya fue utilizado o expiró');
            } else {
                $cuenta = Cuenta::find($confirmacion->cuenta_id);

                if($cuenta) {
                    $cuenta->cuenta_confirmada = '1';
                    $cuenta->updated_at = date('Y-m-d H:i:s');
                    $cuenta->guardar();

                    $confirmacion->usado = '1';
                    $confirmacion->fecha_confirmacion = date('Y-m-d H:i:s');
                    $confirmacion->guardar();

                    Cuenta::setAlerta('exito', 'Cuenta confirmada correctamente');
                } else {
                    Cuenta::setAlerta('error', 'Cuenta no encontrada');
                }
            }
        }

        $alertas = Cuenta::getAlertas();

        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje');
    }

    public static function olvide(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Cuenta($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                Cuenta::setAlerta('exito', 'Si el correo existe, se enviarán instrucciones. Recuperación formal queda para una siguiente etapa.');
            }
        }

        $alertas = Cuenta::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router) {
        $alertas = [];
        Cuenta::setAlerta('error', 'La recuperación de contraseña queda pendiente para una siguiente etapa.');
        $alertas = Cuenta::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => true
        ]);
    }
}

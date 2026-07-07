<?php

namespace Controllers;

use MVC\Router;

class AdminCitasController {

    public static function index(Router $router) {
        isAdmin();

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $estado = trim($_GET['estado'] ?? '');
        $busqueda = trim($_GET['q'] ?? '');

        if($fecha === 'todas' || $fecha === '') {
            $fecha = '';
        } else {
            $partesFecha = explode('-', $fecha);
            if(count($partesFecha) !== 3 || !checkdate((int)$partesFecha[1], (int)$partesFecha[2], (int)$partesFecha[0])) {
                $fecha = date('Y-m-d');
            }
        }

        $estados = self::obtenerEstados();
        $citas = self::obtenerCitas($fecha, $estado, $busqueda);
        $metricas = self::obtenerMetricas($fecha, $estado, $busqueda);

        $mensaje = $_SESSION['mensaje_admin_citas'] ?? null;
        unset($_SESSION['mensaje_admin_citas']);

        $router->render('admin/citas', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'fecha' => $fecha,
            'estadoSeleccionado' => $estado,
            'busqueda' => $busqueda,
            'estados' => $estados,
            'citas' => $citas,
            'metricas' => $metricas,
            'mensaje' => $mensaje
        ]);
    }

    public static function crear(Router $router) {
        isAdmin();

        $alertas = [];
        $form = [
            'tipo_cliente' => $_POST['tipo_cliente'] ?? 'registrado',
            'cliente_id' => $_POST['cliente_id'] ?? '',
            'alias_nombre' => $_POST['alias_nombre'] ?? '',
            'alias_telefono' => $_POST['alias_telefono'] ?? '',
            'alias_email' => $_POST['alias_email'] ?? '',
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'hora' => $_POST['hora'] ?? '',
            'servicios' => $_POST['servicios'] ?? [],
            'observaciones' => $_POST['observaciones'] ?? ''
        ];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = self::guardarCitaDesdeAdmin($_POST);

            if($resultado['ok']) {
                self::setMensaje('exito', $resultado['mensaje']);
                header('Location: /admin/citas?fecha=' . urlencode($resultado['fecha']));
                exit;
            }

            $alertas['error'][] = $resultado['mensaje'];
        }

        $router->render('admin/crear-cita', [
            'nombre' => $_SESSION['nombre'] ?? 'Administrador',
            'clientes' => self::obtenerClientesParaSelect(),
            'servicios' => self::obtenerServiciosParaFormulario(),
            'alertas' => $alertas,
            'form' => $form
        ]);
    }

    public static function cambiarEstado() {
        isAdmin();

        global $db;

        $citaId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $accion = $_POST['accion'] ?? '';
        $redirect = $_POST['redirect'] ?? '/admin/citas';

        if(!$citaId) {
            self::setMensaje('error', 'La cita no es válida.');
            header('Location: ' . $redirect);
            exit;
        }

        $acciones = [
            'atender' => [
                'estado' => 'Atendida',
                'fallback' => 2,
                'liberar_bloques' => false,
                'mensaje' => 'La cita fue marcada como atendida.'
            ],
            'cancelar' => [
                'estado' => 'Cancelada',
                'fallback' => 3,
                'liberar_bloques' => true,
                'mensaje' => 'La cita fue cancelada y sus bloques quedaron libres.'
            ],
            'aprobar_cancelacion' => [
                'estado' => 'Cancelada',
                'fallback' => 3,
                'liberar_bloques' => true,
                'mensaje' => 'La cancelación fue aprobada y sus bloques quedaron libres.'
            ],
            'rechazar_cancelacion' => [
                'estado' => 'Reservada',
                'fallback' => 1,
                'liberar_bloques' => false,
                'mensaje' => 'La solicitud de cancelación fue rechazada y la cita quedó reservada.'
            ]
        ];

        if(!isset($acciones[$accion])) {
            self::setMensaje('error', 'Acción no permitida.');
            header('Location: ' . $redirect);
            exit;
        }

        $cita = self::obtenerCitaSimple($citaId);

        if(!$cita) {
            self::setMensaje('error', 'No se encontró la cita.');
            header('Location: ' . $redirect);
            exit;
        }

        $config = $acciones[$accion];
        $estadoId = self::obtenerEstadoId($config['estado'], $config['fallback']);

        $db->begin_transaction();

        try {
            if($config['liberar_bloques']) {
                $db->query("DELETE FROM bloques_agenda WHERE cita_id = {$citaId}");
            }

            $query = "
                UPDATE citas
                SET estado_cita_id = {$estadoId},
                    updated_at = NOW()
                WHERE id = {$citaId}
                LIMIT 1
            ";

            if(!$db->query($query)) {
                throw new \Exception($db->error);
            }

            $db->commit();
            self::setMensaje('exito', $config['mensaje']);
        } catch(\Throwable $e) {
            $db->rollback();
            self::setMensaje('error', $e->getMessage() ?: 'No se pudo actualizar la cita.');
        }

        header('Location: ' . $redirect);
        exit;
    }

    private static function guardarCitaDesdeAdmin($data) {
        global $db;

        $tipoCliente = $data['tipo_cliente'] ?? 'registrado';
        $clienteId = filter_var($data['cliente_id'] ?? null, FILTER_VALIDATE_INT);
        $aliasNombre = trim($data['alias_nombre'] ?? '');
        $aliasTelefono = trim($data['alias_telefono'] ?? '');
        $aliasEmail = trim($data['alias_email'] ?? '');
        $fecha = trim($data['fecha'] ?? '');
        $hora = trim($data['hora'] ?? '');
        $observaciones = trim($data['observaciones'] ?? '');
        $serviciosInput = $data['servicios'] ?? [];

        if(!is_array($serviciosInput)) {
            $serviciosInput = explode(',', (string)$serviciosInput);
        }

        $idsServicios = array_values(array_unique(array_filter(array_map('intval', $serviciosInput))));

        if($tipoCliente !== 'registrado' && $tipoCliente !== 'alias') {
            return ['ok' => false, 'mensaje' => 'Selecciona si la cita es para cliente registrado o cliente sin cuenta.'];
        }

        if($tipoCliente === 'registrado') {
            if(!$clienteId || !self::clienteRegistradoExiste($clienteId)) {
                return ['ok' => false, 'mensaje' => 'Selecciona un cliente registrado válido.'];
            }

            $aliasNombre = '';
            $aliasTelefono = '';
            $aliasEmail = '';
        }

        if($tipoCliente === 'alias') {
            $clienteId = null;

            if($aliasNombre === '') {
                return ['ok' => false, 'mensaje' => 'Escribe el nombre o alias de la persona sin cuenta.'];
            }

            if($aliasEmail !== '' && !filter_var($aliasEmail, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'mensaje' => 'El correo del cliente sin cuenta no es válido.'];
            }
        }

        if(!$fecha || !$hora || empty($idsServicios)) {
            return ['ok' => false, 'mensaje' => 'Faltan fecha, hora o servicios para crear la cita.'];
        }

        if(!self::fechaValida($fecha)) {
            return ['ok' => false, 'mensaje' => 'La fecha no es válida.'];
        }

        if(!self::horaValida($hora)) {
            return ['ok' => false, 'mensaje' => 'La hora no es válida.'];
        }

        if(self::esFinDeSemana($fecha)) {
            return ['ok' => false, 'mensaje' => 'No se permiten citas en fin de semana.'];
        }

        if(!self::horaDentroDeHorario($hora)) {
            return ['ok' => false, 'mensaje' => 'El horario disponible es de 10:00 a 18:00.'];
        }

        $sucursal = self::obtenerSucursalPrincipal();

        if(!$sucursal) {
            return ['ok' => false, 'mensaje' => 'No hay sucursal activa configurada.'];
        }

        $colaborador = self::obtenerBarberoDisponibleBase();

        if(!$colaborador) {
            return ['ok' => false, 'mensaje' => 'No hay barbero activo para asignar la cita.'];
        }

        $servicios = self::obtenerServiciosPorIds($idsServicios);

        if(count($servicios) !== count($idsServicios)) {
            return ['ok' => false, 'mensaje' => 'Uno o más servicios no existen o están inactivos.'];
        }

        $duracionTotal = 0;
        $subtotalSinIva = 0;
        $ivaTotal = 0;
        $totalConIva = 0;

        foreach($servicios as $servicio) {
            $duracionTotal += (int)$servicio['duracion_minutos'];

            $precioSinIva = (float)$servicio['precio_base_sin_iva'];
            $ivaPorcentaje = (float)$servicio['iva_porcentaje'];
            $ivaMonto = round($precioSinIva * ($ivaPorcentaje / 100), 2);
            $totalLinea = round($precioSinIva + $ivaMonto, 2);

            $subtotalSinIva += $precioSinIva;
            $ivaTotal += $ivaMonto;
            $totalConIva += $totalLinea;
        }

        $bloqueMinutos = (int)($sucursal['bloque_agenda_minutos'] ?? 15);
        if($bloqueMinutos <= 0) $bloqueMinutos = 15;

        if(($duracionTotal % $bloqueMinutos) !== 0) {
            return ['ok' => false, 'mensaje' => 'La duración total no respeta los bloques de agenda configurados.'];
        }

        $horaInicio = self::normalizarHora($hora);
        $horaFin = self::sumarMinutosHora($horaInicio, $duracionTotal);

        if(!self::horaFinDentroDeHorario($horaFin)) {
            return ['ok' => false, 'mensaje' => 'La cita termina fuera del horario permitido.'];
        }

        $bloques = self::generarBloques($horaInicio, $duracionTotal, $bloqueMinutos);

        if(empty($bloques)) {
            return ['ok' => false, 'mensaje' => 'No se pudieron generar los bloques de agenda.'];
        }

        $conflictos = self::buscarConflictos(
            (int)$sucursal['id'],
            (int)$colaborador['id'],
            $fecha,
            $bloques
        );

        if(!empty($conflictos)) {
            return [
                'ok' => false,
                'mensaje' => 'Ese horario ya está ocupado. Conflicto en: ' . implode(', ', $conflictos)
            ];
        }

        $fechaSQL = $db->escape_string($fecha);
        $horaInicioSQL = $db->escape_string($horaInicio);
        $horaFinSQL = $db->escape_string($horaFin);
        $observacionesSQL = $observaciones !== '' ? "'" . $db->escape_string(substr($observaciones, 0, 180)) . "'" : "NULL";
        $clienteIdSQL = $clienteId ? (string)(int)$clienteId : "NULL";
        $aliasNombreSQL = $aliasNombre !== '' ? "'" . $db->escape_string(substr($aliasNombre, 0, 120)) . "'" : "NULL";
        $aliasTelefonoSQL = $aliasTelefono !== '' ? "'" . $db->escape_string(substr($aliasTelefono, 0, 20)) . "'" : "NULL";
        $aliasEmailSQL = $aliasEmail !== '' ? "'" . $db->escape_string(substr($aliasEmail, 0, 120)) . "'" : "NULL";

        $db->begin_transaction();

        try {
            $queryCita = "
                INSERT INTO citas
                (
                    cliente_id,
                    cliente_alias_nombre,
                    cliente_alias_telefono,
                    cliente_alias_email,
                    colaborador_id,
                    sucursal_id,
                    estado_cita_id,
                    fecha,
                    hora_inicio,
                    hora_fin,
                    duracion_total_minutos,
                    observaciones,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    {$clienteIdSQL},
                    {$aliasNombreSQL},
                    {$aliasTelefonoSQL},
                    {$aliasEmailSQL},
                    {$colaborador['id']},
                    {$sucursal['id']},
                    1,
                    '{$fechaSQL}',
                    '{$horaInicioSQL}',
                    '{$horaFinSQL}',
                    {$duracionTotal},
                    {$observacionesSQL},
                    NOW(),
                    NOW()
                )
            ";

            if(!$db->query($queryCita)) {
                throw new \Exception($db->error);
            }

            $citaId = $db->insert_id;
            $orden = 1;

            foreach($servicios as $servicio) {
                $servicioId = (int)$servicio['id'];
                $duracion = (int)$servicio['duracion_minutos'];
                $precioSinIva = (float)$servicio['precio_base_sin_iva'];
                $ivaPorcentaje = (float)$servicio['iva_porcentaje'];
                $totalLinea = round($precioSinIva + ($precioSinIva * ($ivaPorcentaje / 100)), 2);

                $queryServicio = "
                    INSERT INTO citas_servicios
                    (
                        cita_id,
                        servicio_id,
                        orden,
                        duracion_minutos_snapshot,
                        precio_sin_iva_snapshot,
                        iva_porcentaje_snapshot,
                        total_con_iva_snapshot
                    )
                    VALUES
                    (
                        {$citaId},
                        {$servicioId},
                        {$orden},
                        {$duracion},
                        " . self::numeroSQL($precioSinIva) . ",
                        " . self::numeroSQL($ivaPorcentaje) . ",
                        " . self::numeroSQL($totalLinea) . "
                    )
                ";

                if(!$db->query($queryServicio)) {
                    throw new \Exception($db->error);
                }

                $orden++;
            }

            foreach($bloques as $bloqueHora) {
                $bloqueHoraSQL = $db->escape_string($bloqueHora);

                $queryBloque = "
                    INSERT INTO bloques_agenda
                    (
                        cita_id,
                        sucursal_id,
                        colaborador_id,
                        fecha,
                        hora_inicio,
                        duracion_bloque_minutos
                    )
                    VALUES
                    (
                        {$citaId},
                        {$sucursal['id']},
                        {$colaborador['id']},
                        '{$fechaSQL}',
                        '{$bloqueHoraSQL}',
                        {$bloqueMinutos}
                    )
                ";

                if(!$db->query($queryBloque)) {
                    throw new \Exception('El horario se ocupó antes de guardar. Intenta con otra hora.');
                }
            }

            $db->commit();

            $tipoTexto = $tipoCliente === 'alias' ? 'cliente sin cuenta' : 'cliente registrado';

            return [
                'ok' => true,
                'mensaje' => 'Cita creada correctamente para ' . $tipoTexto . '. Total estimado: $' . number_format($totalConIva, 2) . ' MXN.',
                'cita_id' => (int)$citaId,
                'fecha' => $fecha
            ];
        } catch(\Throwable $e) {
            $db->rollback();

            return [
                'ok' => false,
                'mensaje' => $e->getMessage() ?: 'No se pudo crear la cita.'
            ];
        }
    }

    private static function obtenerCitas($fecha, $estado, $busqueda) {
        global $db;

        [$where, $joinExtra] = self::construirFiltros($fecha, $estado, $busqueda);

        $query = "
            SELECT
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.duracion_total_minutos,
                c.observaciones,
                c.estado_cita_id,
                ec.nombre AS estado,
                CASE
                    WHEN c.cliente_id IS NULL THEN COALESCE(NULLIF(c.cliente_alias_nombre, ''), 'Cliente sin cuenta')
                    ELSE TRIM(CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', COALESCE(cl.apellido_materno, '')))
                END AS cliente,
                CASE
                    WHEN c.cliente_id IS NULL THEN COALESCE(NULLIF(c.cliente_alias_telefono, ''), 'Sin teléfono')
                    ELSE COALESCE(NULLIF(cl.telefono, ''), 'Sin teléfono')
                END AS cliente_telefono,
                CASE
                    WHEN c.cliente_id IS NULL THEN COALESCE(NULLIF(c.cliente_alias_email, ''), 'Sin correo')
                    ELSE COALESCE(NULLIF(cu.email, ''), 'Sin correo')
                END AS cliente_email,
                CASE
                    WHEN c.cliente_id IS NULL THEN 'Sin cuenta'
                    ELSE 'Registrado'
                END AS cliente_tipo,
                CONCAT(co.nombre, ' ', co.apellido_paterno) AS barbero,
                scc.nombre_comercial AS sucursal,
                GROUP_CONCAT(s.nombre ORDER BY cs.orden SEPARATOR ' + ') AS servicios,
                SUM(cs.total_con_iva_snapshot) AS total_con_iva,
                (
                    SELECT COUNT(*)
                    FROM bloques_agenda ba
                    WHERE ba.cita_id = c.id
                ) AS bloques_ocupados
            FROM citas c
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            LEFT JOIN cuentas cu ON cu.id = cl.cuenta_id
            INNER JOIN colaboradores co ON co.id = c.colaborador_id
            INNER JOIN sucursales scc ON scc.id = c.sucursal_id
            INNER JOIN citas_servicios cs ON cs.cita_id = c.id
            INNER JOIN servicios s ON s.id = cs.servicio_id
            {$joinExtra}
            WHERE {$where}
            GROUP BY
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.duracion_total_minutos,
                c.observaciones,
                c.estado_cita_id,
                ec.nombre,
                c.cliente_id,
                c.cliente_alias_nombre,
                c.cliente_alias_telefono,
                c.cliente_alias_email,
                cl.nombre,
                cl.apellido_paterno,
                cl.apellido_materno,
                cl.telefono,
                cu.email,
                co.nombre,
                co.apellido_paterno,
                scc.nombre_comercial
            ORDER BY c.fecha DESC, c.hora_inicio DESC
            LIMIT 100
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerMetricas($fecha, $estado, $busqueda) {
        global $db;

        [$where, $joinExtra] = self::construirFiltros($fecha, $estado, $busqueda);

        $query = "
            SELECT
                COUNT(DISTINCT c.id) AS total,
                SUM(CASE WHEN ec.nombre = 'Reservada' THEN 1 ELSE 0 END) AS reservadas,
                SUM(CASE WHEN ec.nombre = 'Atendida' THEN 1 ELSE 0 END) AS atendidas,
                SUM(CASE WHEN ec.nombre = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas,
                SUM(CASE WHEN ec.nombre = 'Cancelación solicitada' THEN 1 ELSE 0 END) AS cancelacion_solicitada
            FROM citas c
            INNER JOIN estados_cita ec ON ec.id = c.estado_cita_id
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            LEFT JOIN cuentas cu ON cu.id = cl.cuenta_id
            {$joinExtra}
            WHERE {$where}
        ";

        $resultado = $db->query($query);
        $row = $resultado ? $resultado->fetch_assoc() : [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'reservadas' => (int)($row['reservadas'] ?? 0),
            'atendidas' => (int)($row['atendidas'] ?? 0),
            'canceladas' => (int)($row['canceladas'] ?? 0),
            'cancelacion_solicitada' => (int)($row['cancelacion_solicitada'] ?? 0)
        ];
    }

    private static function construirFiltros($fecha, $estado, $busqueda) {
        global $db;

        $where = ["1 = 1"];
        $joinExtra = "";

        if($fecha !== '') {
            $fechaSQL = $db->escape_string($fecha);
            $where[] = "c.fecha = '{$fechaSQL}'";
        }

        if($estado !== '') {
            $estadoSQL = $db->escape_string($estado);
            $where[] = "ec.nombre = '{$estadoSQL}'";
        }

        if($busqueda !== '') {
            $busquedaSQL = $db->escape_string($busqueda);
            $where[] = "(
                cl.nombre LIKE '%{$busquedaSQL}%'
                OR cl.apellido_paterno LIKE '%{$busquedaSQL}%'
                OR cl.apellido_materno LIKE '%{$busquedaSQL}%'
                OR cl.telefono LIKE '%{$busquedaSQL}%'
                OR cu.email LIKE '%{$busquedaSQL}%'
                OR c.cliente_alias_nombre LIKE '%{$busquedaSQL}%'
                OR c.cliente_alias_telefono LIKE '%{$busquedaSQL}%'
                OR c.cliente_alias_email LIKE '%{$busquedaSQL}%'
            )";
        }

        return [implode(' AND ', $where), $joinExtra];
    }

    private static function obtenerClientesParaSelect() {
        global $db;

        $query = "
            SELECT
                cl.id,
                TRIM(CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', COALESCE(cl.apellido_materno, ''))) AS nombre,
                COALESCE(cl.telefono, '') AS telefono,
                COALESCE(cu.email, '') AS email
            FROM clientes cl
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            WHERE cu.activa = 1
            ORDER BY cl.apellido_paterno ASC, cl.nombre ASC
        ";

        return self::fetchAll($db->query($query));
    }

    private static function obtenerServiciosParaFormulario() {
        global $db;

        $query = "
            SELECT
                s.id,
                s.nombre,
                cs.nombre AS categoria,
                s.duracion_minutos,
                s.precio_base_sin_iva,
                s.iva_porcentaje,
                ROUND(s.precio_base_sin_iva + (s.precio_base_sin_iva * (s.iva_porcentaje / 100)), 2) AS precio_final
            FROM servicios s
            INNER JOIN categorias_servicio cs ON cs.id = s.categoria_servicio_id
            WHERE s.activo = 1
            ORDER BY cs.nombre ASC, s.nombre ASC
        ";

        return self::fetchAll($db->query($query));
    }

    private static function clienteRegistradoExiste($clienteId) {
        global $db;

        $clienteId = (int)$clienteId;
        $resultado = $db->query("
            SELECT cl.id
            FROM clientes cl
            INNER JOIN cuentas cu ON cu.id = cl.cuenta_id
            WHERE cl.id = {$clienteId}
            AND cu.activa = 1
            LIMIT 1
        ");

        return $resultado && $resultado->num_rows > 0;
    }

    private static function obtenerSucursalPrincipal() {
        global $db;

        $resultado = $db->query("
            SELECT id, bloque_agenda_minutos
            FROM sucursales
            WHERE activo = 1
            ORDER BY id ASC
            LIMIT 1
        ");

        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function obtenerBarberoDisponibleBase() {
        global $db;

        $resultado = $db->query("
            SELECT c.id, c.nombre, c.apellido_paterno
            FROM colaboradores c
            INNER JOIN roles_colaborador r ON r.id = c.rol_colaborador_id
            WHERE c.activo = 1
            AND r.nombre = 'Barbero'
            ORDER BY c.id ASC
            LIMIT 1
        ");

        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function obtenerServiciosPorIds($idsServicios) {
        global $db;

        $ids = implode(',', array_map('intval', $idsServicios));

        $query = "
            SELECT
                id,
                nombre,
                duracion_minutos,
                precio_base_sin_iva,
                iva_porcentaje,
                costo_estimado_sin_iva
            FROM servicios
            WHERE activo = 1
            AND id IN ({$ids})
        ";

        $resultado = $db->query($query);
        $servicios = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $servicios[(int)$row['id']] = $row;
            }
        }

        $ordenados = [];
        foreach($idsServicios as $id) {
            if(isset($servicios[$id])) {
                $ordenados[] = $servicios[$id];
            }
        }

        return $ordenados;
    }

    private static function buscarConflictos($sucursalId, $colaboradorId, $fecha, $bloques) {
        global $db;

        $bloquesSQL = array_map(function($bloque) use ($db) {
            return "'" . $db->escape_string($bloque) . "'";
        }, $bloques);

        $fechaSQL = $db->escape_string($fecha);
        $listaBloques = implode(',', $bloquesSQL);

        $query = "
            SELECT hora_inicio
            FROM bloques_agenda
            WHERE sucursal_id = {$sucursalId}
            AND colaborador_id = {$colaboradorId}
            AND fecha = '{$fechaSQL}'
            AND hora_inicio IN ({$listaBloques})
        ";

        $resultado = $db->query($query);
        $conflictos = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $conflictos[] = substr($row['hora_inicio'], 0, 5);
            }
        }

        return $conflictos;
    }

    private static function obtenerEstados() {
        global $db;

        return self::fetchAll($db->query("
            SELECT id, nombre
            FROM estados_cita
            WHERE activo = 1
            ORDER BY id ASC
        "));
    }

    private static function obtenerEstadoId($nombre, $fallback) {
        global $db;

        $nombreSQL = $db->escape_string($nombre);
        $resultado = $db->query("SELECT id FROM estados_cita WHERE nombre = '{$nombreSQL}' LIMIT 1");

        if($resultado && $row = $resultado->fetch_assoc()) {
            return (int)$row['id'];
        }

        return (int)$fallback;
    }

    private static function obtenerCitaSimple($id) {
        global $db;

        $id = (int)$id;
        $resultado = $db->query("SELECT id, estado_cita_id FROM citas WHERE id = {$id} LIMIT 1");

        return $resultado ? $resultado->fetch_assoc() : null;
    }

    private static function generarBloques($horaInicio, $duracionTotal, $bloqueMinutos) {
        $bloques = [];
        $actual = strtotime($horaInicio);
        $cantidad = (int)($duracionTotal / $bloqueMinutos);

        for($i = 0; $i < $cantidad; $i++) {
            $bloques[] = date('H:i:s', $actual);
            $actual = strtotime("+{$bloqueMinutos} minutes", $actual);
        }

        return $bloques;
    }

    private static function sumarMinutosHora($hora, $minutos) {
        return date('H:i:s', strtotime("+{$minutos} minutes", strtotime($hora)));
    }

    private static function normalizarHora($hora) {
        if(strlen($hora) === 5) {
            return $hora . ':00';
        }

        return $hora;
    }

    private static function fechaValida($fecha) {
        $partes = explode('-', $fecha);

        if(count($partes) !== 3) {
            return false;
        }

        return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
    }

    private static function horaValida($hora) {
        return preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora) === 1;
    }

    private static function esFinDeSemana($fecha) {
        $dia = (int)date('N', strtotime($fecha));
        return $dia >= 6;
    }

    private static function horaDentroDeHorario($hora) {
        $hora = self::normalizarHora($hora);
        return $hora >= '10:00:00' && $hora <= '18:00:00';
    }

    private static function horaFinDentroDeHorario($horaFin) {
        return $horaFin <= '19:00:00';
    }

    private static function numeroSQL($numero) {
        return number_format((float)$numero, 2, '.', '');
    }

    private static function setMensaje($tipo, $texto) {
        $_SESSION['mensaje_admin_citas'] = [
            'tipo' => $tipo,
            'texto' => $texto
        ];
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

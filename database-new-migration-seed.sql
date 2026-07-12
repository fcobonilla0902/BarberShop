-- ==========================================================
-- BARBERSHOP - BASE DE DATOS NUEVA + SEEDS
-- Issue 1: estructura corregida para Administración de Base de Datos
-- MySQL 8.0
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS venta_servicios;
DROP TABLE IF EXISTS venta_productos;
DROP TABLE IF EXISTS ventas;
DROP TABLE IF EXISTS movimientos_inventario;
DROP TABLE IF EXISTS metodos_pago;
DROP TABLE IF EXISTS tipos_movimiento_inventario;
DROP TABLE IF EXISTS lotes_producto;
DROP TABLE IF EXISTS estados_lote;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS proveedores;
DROP TABLE IF EXISTS categorias_producto;
DROP TABLE IF EXISTS bloques_agenda;
DROP TABLE IF EXISTS citas_servicios;
DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS categorias_servicio;
DROP TABLE IF EXISTS estados_cita;
DROP TABLE IF EXISTS colaboradores;
DROP TABLE IF EXISTS roles_colaborador;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS confirmaciones_cuenta;
DROP TABLE IF EXISTS cuentas;
DROP TABLE IF EXISTS sucursales;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- DATOS DEL NEGOCIO
-- ==========================================================

CREATE TABLE sucursales (
    id INT NOT NULL AUTO_INCREMENT,
    nombre_comercial VARCHAR(80) NOT NULL,
    razon_social VARCHAR(120) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(120) NULL,
    calle VARCHAR(80) NOT NULL,
    numero_exterior VARCHAR(10) NOT NULL,
    numero_interior VARCHAR(10) NULL,
    colonia VARCHAR(80) NOT NULL,
    municipio VARCHAR(80) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    codigo_postal CHAR(5) NOT NULL,
    bloque_agenda_minutos SMALLINT NOT NULL DEFAULT 15,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    INDEX idx_sucursales_activo (activo),
    CONSTRAINT chk_sucursales_bloque CHECK (bloque_agenda_minutos IN (15, 20, 30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- CUENTAS, CLIENTES Y COLABORADORES
-- ==========================================================

CREATE TABLE cuentas (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    cuenta_confirmada TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_acceso DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cuentas_email (email),
    INDEX idx_cuentas_activa_confirmada (activa, cuenta_confirmada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE confirmaciones_cuenta (
    id INT NOT NULL AUTO_INCREMENT,
    cuenta_id INT NOT NULL,
    token_hash VARCHAR(128) NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    fecha_confirmacion DATETIME NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_confirmaciones_token_hash (token_hash),
    INDEX idx_confirmaciones_cuenta (cuenta_id),
    CONSTRAINT fk_confirmaciones_cuenta
        FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
    id INT NOT NULL AUTO_INCREMENT,
    cuenta_id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    apellido_paterno VARCHAR(60) NOT NULL,
    apellido_materno VARCHAR(60) NULL,
    telefono VARCHAR(20) NULL,
    fecha_nacimiento DATE NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_cuenta (cuenta_id),
    INDEX idx_clientes_nombre (apellido_paterno, apellido_materno, nombre),
    CONSTRAINT fk_clientes_cuenta
        FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles_colaborador (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(40) NOT NULL,
    descripcion VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_colaborador_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE colaboradores (
    id INT NOT NULL AUTO_INCREMENT,
    cuenta_id INT NULL,
    rol_colaborador_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    apellido_paterno VARCHAR(60) NOT NULL,
    apellido_materno VARCHAR(60) NULL,
    curp CHAR(18) NOT NULL,
    telefono VARCHAR(20) NULL,
    fecha_nacimiento DATE NULL,
    fecha_contratacion DATE NOT NULL,
    salario_mensual DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_colaboradores_cuenta (cuenta_id),
    UNIQUE KEY uq_colaboradores_curp (curp),
    INDEX idx_colaboradores_rol (rol_colaborador_id),
    INDEX idx_colaboradores_sucursal (sucursal_id),
    INDEX idx_colaboradores_activo (activo),
    CONSTRAINT fk_colaboradores_cuenta
        FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_colaboradores_rol
        FOREIGN KEY (rol_colaborador_id) REFERENCES roles_colaborador(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_colaboradores_sucursal
        FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_colaboradores_salario CHECK (salario_mensual >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- CITAS Y AGENDA
-- ==========================================================

CREATE TABLE estados_cita (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(40) NOT NULL,
    descripcion VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estados_cita_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias_servicio (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(150) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_servicio_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE servicios (
    id INT NOT NULL AUTO_INCREMENT,
    categoria_servicio_id INT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(180) NULL,
    duracion_minutos SMALLINT NOT NULL,
    precio_base_sin_iva DECIMAL(8,2) NOT NULL,
    costo_estimado_sin_iva DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    iva_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 16.00,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    INDEX idx_servicios_categoria (categoria_servicio_id),
    INDEX idx_servicios_activo (activo),
    CONSTRAINT fk_servicios_categoria
        FOREIGN KEY (categoria_servicio_id) REFERENCES categorias_servicio(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_servicios_duracion CHECK (duracion_minutos > 0),
    CONSTRAINT chk_servicios_precio CHECK (precio_base_sin_iva >= 0),
    CONSTRAINT chk_servicios_costo CHECK (costo_estimado_sin_iva >= 0),
    CONSTRAINT chk_servicios_iva CHECK (iva_porcentaje >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE citas (
    id INT NOT NULL AUTO_INCREMENT,
    cliente_id INT NULL,
    cliente_alias_nombre VARCHAR(120) NULL,
    cliente_alias_telefono VARCHAR(20) NULL,
    cliente_alias_email VARCHAR(120) NULL,
    colaborador_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    estado_cita_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion_total_minutos SMALLINT NOT NULL,
    observaciones VARCHAR(180) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    INDEX idx_citas_cliente_fecha (cliente_id, fecha),
    INDEX idx_citas_colaborador_fecha (colaborador_id, fecha),
    INDEX idx_citas_sucursal_fecha (sucursal_id, fecha),
    INDEX idx_citas_estado (estado_cita_id),
    INDEX idx_citas_agenda (sucursal_id, colaborador_id, fecha, hora_inicio),
    CONSTRAINT fk_citas_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_citas_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_citas_sucursal
        FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_citas_estado
        FOREIGN KEY (estado_cita_id) REFERENCES estados_cita(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_citas_duracion CHECK (duracion_total_minutos > 0),
    CONSTRAINT chk_citas_horas CHECK (hora_fin > hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE citas_servicios (
    id INT NOT NULL AUTO_INCREMENT,
    cita_id INT NOT NULL,
    servicio_id INT NOT NULL,
    orden SMALLINT NOT NULL DEFAULT 1,
    duracion_minutos_snapshot SMALLINT NOT NULL,
    precio_sin_iva_snapshot DECIMAL(8,2) NOT NULL,
    iva_porcentaje_snapshot DECIMAL(5,2) NOT NULL,
    total_con_iva_snapshot DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_citas_servicios (cita_id, servicio_id),
    INDEX idx_citas_servicios_servicio (servicio_id),
    CONSTRAINT fk_citas_servicios_cita
        FOREIGN KEY (cita_id) REFERENCES citas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_citas_servicios_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_citas_servicios_duracion CHECK (duracion_minutos_snapshot > 0),
    CONSTRAINT chk_citas_servicios_precio CHECK (precio_sin_iva_snapshot >= 0),
    CONSTRAINT chk_citas_servicios_total CHECK (total_con_iva_snapshot >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bloques_agenda (
    id INT NOT NULL AUTO_INCREMENT,
    cita_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    colaborador_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    duracion_bloque_minutos SMALLINT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_bloques_agenda_ocupado (sucursal_id, colaborador_id, fecha, hora_inicio),
    INDEX idx_bloques_agenda_cita (cita_id),
    CONSTRAINT fk_bloques_agenda_cita
        FOREIGN KEY (cita_id) REFERENCES citas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bloques_agenda_sucursal
        FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_bloques_agenda_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_bloques_duracion CHECK (duracion_bloque_minutos > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- PRODUCTOS, LOTES E INVENTARIO
-- ==========================================================

CREATE TABLE categorias_producto (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(150) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_producto_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE proveedores (
    id INT NOT NULL AUTO_INCREMENT,
    nombre_comercial VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    INDEX idx_proveedores_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
    id INT NOT NULL AUTO_INCREMENT,
    categoria_producto_id INT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    marca VARCHAR(60) NULL,
    descripcion VARCHAR(180) NULL,
    codigo_barras VARCHAR(40) NULL,
    imagen_url VARCHAR(160) NULL,
    unidad_medida VARCHAR(20) NOT NULL DEFAULT 'pieza',
    costo_referencia_sin_iva DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    factor_utilidad DECIMAL(5,2) NOT NULL DEFAULT 1.30,
    precio_venta_sin_iva DECIMAL(10,2) NOT NULL,
    iva_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 16.00,
    stock_minimo INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_productos_codigo_barras (codigo_barras),
    INDEX idx_productos_categoria (categoria_producto_id),
    INDEX idx_productos_nombre (nombre),
    INDEX idx_productos_activo (activo),
    CONSTRAINT fk_productos_categoria
        FOREIGN KEY (categoria_producto_id) REFERENCES categorias_producto(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_productos_costo CHECK (costo_referencia_sin_iva >= 0),
    CONSTRAINT chk_productos_factor CHECK (factor_utilidad > 0),
    CONSTRAINT chk_productos_precio CHECK (precio_venta_sin_iva >= 0),
    CONSTRAINT chk_productos_iva CHECK (iva_porcentaje >= 0),
    CONSTRAINT chk_productos_stock_minimo CHECK (stock_minimo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estados_lote (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(40) NOT NULL,
    descripcion VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estados_lote_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lotes_producto (
    id INT NOT NULL AUTO_INCREMENT,
    producto_id INT NOT NULL,
    proveedor_id INT NULL,
    estado_lote_id INT NOT NULL,
    codigo_lote VARCHAR(40) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_caducidad DATE NULL,
    cantidad_inicial INT NOT NULL,
    cantidad_actual INT NOT NULL,
    costo_unitario_sin_iva DECIMAL(10,2) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lotes_producto_codigo (producto_id, codigo_lote),
    INDEX idx_lotes_producto_producto (producto_id),
    INDEX idx_lotes_producto_caducidad (fecha_caducidad),
    INDEX idx_lotes_producto_estado (estado_lote_id),
    CONSTRAINT fk_lotes_producto_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_lotes_producto_proveedor
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_lotes_producto_estado
        FOREIGN KEY (estado_lote_id) REFERENCES estados_lote(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_lotes_cantidad_inicial CHECK (cantidad_inicial >= 0),
    CONSTRAINT chk_lotes_cantidad_actual CHECK (cantidad_actual >= 0),
    CONSTRAINT chk_lotes_cantidad_actual_max CHECK (cantidad_actual <= cantidad_inicial),
    CONSTRAINT chk_lotes_costo CHECK (costo_unitario_sin_iva >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipos_movimiento_inventario (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tipos_movimiento_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE metodos_pago (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(40) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_metodos_pago_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- VENTAS Y TICKETS
-- ==========================================================

CREATE TABLE ventas (
    id INT NOT NULL AUTO_INCREMENT,
    folio VARCHAR(20) NOT NULL,
    sucursal_id INT NOT NULL,
    cliente_id INT NULL,
    colaborador_id INT NOT NULL,
    cita_id INT NULL,
    metodo_pago_id INT NOT NULL,
    fecha_venta DATETIME NOT NULL,
    subtotal_sin_iva DECIMAL(10,2) NOT NULL,
    iva_total DECIMAL(10,2) NOT NULL,
    total_con_iva DECIMAL(10,2) NOT NULL,
    costo_total_sin_iva DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    utilidad_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ventas_folio (folio),
    UNIQUE KEY uq_ventas_cita (cita_id),
    INDEX idx_ventas_sucursal_fecha (sucursal_id, fecha_venta),
    INDEX idx_ventas_cliente (cliente_id),
    INDEX idx_ventas_colaborador (colaborador_id),
    CONSTRAINT fk_ventas_sucursal
        FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ventas_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_ventas_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ventas_cita
        FOREIGN KEY (cita_id) REFERENCES citas(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_ventas_metodo_pago
        FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_ventas_subtotal CHECK (subtotal_sin_iva >= 0),
    CONSTRAINT chk_ventas_iva CHECK (iva_total >= 0),
    CONSTRAINT chk_ventas_total CHECK (total_con_iva >= 0),
    CONSTRAINT chk_ventas_costo CHECK (costo_total_sin_iva >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venta_productos (
    id INT NOT NULL AUTO_INCREMENT,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    lote_producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    costo_unitario_sin_iva_snapshot DECIMAL(10,2) NOT NULL,
    costo_total_sin_iva DECIMAL(10,2) NOT NULL,
    precio_unitario_sin_iva DECIMAL(10,2) NOT NULL,
    iva_porcentaje_aplicado DECIMAL(5,2) NOT NULL,
    iva_monto DECIMAL(10,2) NOT NULL,
    total_linea_con_iva DECIMAL(10,2) NOT NULL,
    utilidad_linea DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_venta_productos_venta (venta_id),
    INDEX idx_venta_productos_producto (producto_id),
    INDEX idx_venta_productos_lote (lote_producto_id),
    CONSTRAINT fk_venta_productos_venta
        FOREIGN KEY (venta_id) REFERENCES ventas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_venta_productos_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_venta_productos_lote
        FOREIGN KEY (lote_producto_id) REFERENCES lotes_producto(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_venta_productos_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_venta_productos_costos CHECK (costo_total_sin_iva >= 0),
    CONSTRAINT chk_venta_productos_total CHECK (total_linea_con_iva >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venta_servicios (
    id INT NOT NULL AUTO_INCREMENT,
    venta_id INT NOT NULL,
    servicio_id INT NOT NULL,
    cita_servicio_id INT NULL,
    colaborador_id INT NOT NULL,
    cantidad SMALLINT NOT NULL DEFAULT 1,
    costo_estimado_unitario_sin_iva_snapshot DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    costo_total_estimado_sin_iva DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_unitario_sin_iva DECIMAL(10,2) NOT NULL,
    iva_porcentaje_aplicado DECIMAL(5,2) NOT NULL,
    iva_monto DECIMAL(10,2) NOT NULL,
    total_linea_con_iva DECIMAL(10,2) NOT NULL,
    utilidad_linea DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_venta_servicios_venta (venta_id),
    INDEX idx_venta_servicios_servicio (servicio_id),
    INDEX idx_venta_servicios_cita_servicio (cita_servicio_id),
    INDEX idx_venta_servicios_colaborador (colaborador_id),
    CONSTRAINT fk_venta_servicios_venta
        FOREIGN KEY (venta_id) REFERENCES ventas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_venta_servicios_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_venta_servicios_cita_servicio
        FOREIGN KEY (cita_servicio_id) REFERENCES citas_servicios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_venta_servicios_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_venta_servicios_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_venta_servicios_total CHECK (total_linea_con_iva >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movimientos_inventario (
    id INT NOT NULL AUTO_INCREMENT,
    producto_id INT NOT NULL,
    lote_producto_id INT NOT NULL,
    tipo_movimiento_inventario_id INT NOT NULL,
    venta_producto_id INT NULL,
    fecha_movimiento DATETIME NOT NULL,
    cantidad INT NOT NULL,
    costo_unitario_sin_iva DECIMAL(10,2) NULL,
    motivo VARCHAR(120) NULL,
    PRIMARY KEY (id),
    INDEX idx_movimientos_producto (producto_id),
    INDEX idx_movimientos_lote (lote_producto_id),
    INDEX idx_movimientos_tipo (tipo_movimiento_inventario_id),
    INDEX idx_movimientos_fecha (fecha_movimiento),
    INDEX idx_movimientos_venta_producto (venta_producto_id),
    CONSTRAINT fk_movimientos_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimientos_lote
        FOREIGN KEY (lote_producto_id) REFERENCES lotes_producto(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimientos_tipo
        FOREIGN KEY (tipo_movimiento_inventario_id) REFERENCES tipos_movimiento_inventario(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimientos_venta_producto
        FOREIGN KEY (venta_producto_id) REFERENCES venta_productos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_movimientos_cantidad CHECK (cantidad <> 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEEDS
-- Password demo para cuentas: 123456
-- ==========================================================

INSERT INTO sucursales
(id, nombre_comercial, razon_social, telefono, email, calle, numero_exterior, numero_interior, colonia, municipio, estado, codigo_postal, bloque_agenda_minutos, activo, created_at, updated_at)
VALUES
(1, 'BarberShop Pro', 'BarberShop Pro S.A. de C.V.', '+52 55 1234 5678', 'contacto@barbershop.test', 'Av. Insurgentes', '123', NULL, 'Centro', 'Monterrey', 'Nuevo León', '64000', 15, 1, NOW(), NOW());

INSERT INTO cuentas
(id, email, password_hash, activa, cuenta_confirmada, ultimo_acceso, created_at, updated_at)
VALUES
(1, 'admin@barbershop.com', '$2y$12$atFHyoQWFYZmqIE6rSv.c.dKXWCUjRCgElTVf.GT9k1IRT.sv5CYa', 1, 1, NULL, NOW(), NOW()),
(2, 'cliente@barbershop.com', '$2y$12$atFHyoQWFYZmqIE6rSv.c.dKXWCUjRCgElTVf.GT9k1IRT.sv5CYa', 1, 1, NULL, NOW(), NOW()),
(3, 'barbero@barbershop.com', '$2y$12$atFHyoQWFYZmqIE6rSv.c.dKXWCUjRCgElTVf.GT9k1IRT.sv5CYa', 1, 1, NULL, NOW(), NOW());

INSERT INTO confirmaciones_cuenta
(cuenta_id, token_hash, fecha_creacion, fecha_expiracion, fecha_confirmacion, usado)
VALUES
(1, SHA2('admin-confirmado-demo', 256), NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), NOW(), 1),
(2, SHA2('cliente-confirmado-demo', 256), NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), NOW(), 1),
(3, SHA2('barbero-confirmado-demo', 256), NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), NOW(), 1);

INSERT INTO clientes
(id, cuenta_id, nombre, apellido_paterno, apellido_materno, telefono, fecha_nacimiento, created_at, updated_at)
VALUES
(1, 2, 'Ángel', 'Martínez', 'Hernández', '8112345678', '2000-06-15', NOW(), NOW());

INSERT INTO roles_colaborador
(id, nombre, descripcion, activo)
VALUES
(1, 'Administrador', 'Puede administrar operación general del sistema.', 1),
(2, 'Barbero', 'Atiende citas y realiza servicios.', 1),
(3, 'Cajero', 'Registra ventas y tickets.', 1),
(4, 'Ayudante', 'Apoya en operación general de la barbería.', 1);

INSERT INTO colaboradores
(id, cuenta_id, rol_colaborador_id, sucursal_id, nombre, apellido_paterno, apellido_materno, curp, telefono, fecha_nacimiento, fecha_contratacion, salario_mensual, activo, created_at, updated_at)
VALUES
(1, 1, 1, 1, 'Luis', 'Hernández', 'Gómez', 'HEGL900101HNLABC01', '8111111111', '1990-01-01', '2024-01-10', 18000.00, 1, NOW(), NOW()),
(2, 3, 2, 1, 'Roberto', 'García', 'López', 'GALR950505HNLABC02', '8122222222', '1995-05-05', '2024-05-01', 15000.00, 1, NOW(), NOW());

INSERT INTO estados_cita
(id, nombre, descripcion, activo)
VALUES
(1, 'Reservada', 'Cita registrada y pendiente de atender.', 1),
(2, 'Atendida', 'Cita ya realizada.', 1),
(3, 'Cancelada', 'Cita cancelada.', 1),
(4, 'Cancelación solicitada', 'El cliente solicitó cancelar la cita.', 1);

INSERT INTO categorias_servicio
(id, nombre, descripcion, activo)
VALUES
(1, 'Cortes', 'Servicios de corte de cabello.', 1),
(2, 'Barba', 'Servicios relacionados con barba.', 1),
(3, 'Tratamientos', 'Servicios de tratamiento o estilizado.', 1);

INSERT INTO servicios
(id, categoria_servicio_id, nombre, descripcion, duracion_minutos, precio_base_sin_iva, costo_estimado_sin_iva, iva_porcentaje, activo, created_at, updated_at)
VALUES
(1, 1, 'Corte Adulto', 'Corte para adulto.', 30, 129.31, 40.00, 16.00, 1, NOW(), NOW()),
(2, 1, 'Corte Niño', 'Corte para niño.', 30, 103.45, 35.00, 16.00, 1, NOW(), NOW()),
(3, 1, 'Corte Adulto Mayor', 'Corte para adulto mayor.', 30, 112.07, 35.00, 16.00, 1, NOW(), NOW()),
(4, 2, 'Barba', 'Perfilado y arreglo de barba.', 30, 86.21, 25.00, 16.00, 1, NOW(), NOW()),
(5, 3, 'Alaciado', 'Tratamiento de alaciado.', 60, 215.52, 90.00, 16.00, 1, NOW(), NOW()),
(6, 3, 'Cepillado', 'Servicio de cepillado.', 30, 155.17, 50.00, 16.00, 1, NOW(), NOW());

INSERT INTO citas
(id, cliente_id, colaborador_id, sucursal_id, estado_cita_id, fecha, hora_inicio, hora_fin, duracion_total_minutos, observaciones, created_at, updated_at)
VALUES
(1, 1, 2, 1, 1, '2025-07-02', '10:00:00', '11:00:00', 60, 'Demo de cita reservada.', NOW(), NOW()),
(2, 1, 2, 1, 2, '2025-06-28', '12:00:00', '12:30:00', 30, 'Demo de cita atendida.', NOW(), NOW()),
(3, 1, 2, 1, 3, '2025-06-15', '11:00:00', '12:30:00', 90, 'Demo de cita cancelada.', NOW(), NOW()),
(4, 1, 2, 1, 4, '2025-07-05', '15:00:00', '15:30:00', 30, 'Demo de cancelación solicitada.', NOW(), NOW());

INSERT INTO citas_servicios
(id, cita_id, servicio_id, orden, duracion_minutos_snapshot, precio_sin_iva_snapshot, iva_porcentaje_snapshot, total_con_iva_snapshot)
VALUES
(1, 1, 1, 1, 30, 129.31, 16.00, 150.00),
(2, 1, 4, 2, 30, 86.21, 16.00, 100.00),
(3, 2, 1, 1, 30, 129.31, 16.00, 150.00),
(4, 3, 5, 1, 60, 215.52, 16.00, 250.00),
(5, 3, 6, 2, 30, 155.17, 16.00, 180.00),
(6, 4, 2, 1, 30, 103.45, 16.00, 120.00);

INSERT INTO bloques_agenda
(cita_id, sucursal_id, colaborador_id, fecha, hora_inicio, duracion_bloque_minutos)
VALUES
(1, 1, 2, '2025-07-02', '10:00:00', 15),
(1, 1, 2, '2025-07-02', '10:15:00', 15),
(1, 1, 2, '2025-07-02', '10:30:00', 15),
(1, 1, 2, '2025-07-02', '10:45:00', 15),
(2, 1, 2, '2025-06-28', '12:00:00', 15),
(2, 1, 2, '2025-06-28', '12:15:00', 15),
(3, 1, 2, '2025-06-15', '11:00:00', 15),
(3, 1, 2, '2025-06-15', '11:15:00', 15),
(3, 1, 2, '2025-06-15', '11:30:00', 15),
(3, 1, 2, '2025-06-15', '11:45:00', 15),
(3, 1, 2, '2025-06-15', '12:00:00', 15),
(3, 1, 2, '2025-06-15', '12:15:00', 15),
(4, 1, 2, '2025-07-05', '15:00:00', 15),
(4, 1, 2, '2025-07-05', '15:15:00', 15);

INSERT INTO categorias_producto
(id, nombre, descripcion, activo)
VALUES
(1, 'Cera', 'Ceras y pomadas para cabello.', 1),
(2, 'Gel', 'Geles fijadores.', 1),
(3, 'Shampoo', 'Shampoos y productos de limpieza.', 1),
(4, 'Barba', 'Productos para barba.', 1);

INSERT INTO proveedores
(id, nombre_comercial, telefono, email, activo, created_at, updated_at)
VALUES
(1, 'Distribuidora Barber MX', '8188888888', 'ventas@barbermx.test', 1, NOW(), NOW()),
(2, 'Productos Capilares del Norte', '8177777777', 'contacto@capilares.test', 1, NOW(), NOW());

INSERT INTO productos
(id, categoria_producto_id, nombre, marca, descripcion, codigo_barras, imagen_url, unidad_medida, costo_referencia_sin_iva, factor_utilidad, precio_venta_sin_iva, iva_porcentaje, stock_minimo, activo, created_at, updated_at)
VALUES
(1, 1, 'Pomada Modeladora', 'Barber Pro', 'Fijación fuerte, acabado brillante.', '750100000001', '/build/img/productos/pomada.webp', 'pieza', 95.00, 1.63, 155.17, 16.00, 5, 1, NOW(), NOW()),
(2, 4, 'Aceite para Barba', 'Beard MX', 'Hidratación y suavidad.', '750100000002', '/build/img/productos/aceite-barba.webp', 'pieza', 120.00, 1.58, 189.66, 16.00, 5, 1, NOW(), NOW()),
(3, 3, 'Shampoo Anticaspa', 'Clean Hair', 'Control de caspa activo.', '750100000003', '/build/img/productos/shampoo.webp', 'pieza', 85.00, 1.52, 129.31, 16.00, 3, 1, NOW(), NOW()),
(4, 1, 'Cera para Cabello', 'Style Max', 'Moldeado flexible.', '750100000004', '/build/img/productos/cera.webp', 'pieza', 80.00, 1.72, 137.93, 16.00, 4, 1, NOW(), NOW()),
(5, 2, 'Gel Fijador', 'Fix Men', 'Gel de fijación diaria.', '750100000005', '/build/img/productos/gel.webp', 'pieza', 45.00, 1.72, 77.59, 16.00, 6, 1, NOW(), NOW());

INSERT INTO estados_lote
(id, nombre, descripcion, activo)
VALUES
(1, 'Disponible', 'Lote disponible para venta.', 1),
(2, 'Agotado', 'Lote sin existencia.', 1),
(3, 'Caducado', 'Lote vencido.', 1),
(4, 'Bloqueado', 'Lote bloqueado para venta.', 1);

INSERT INTO lotes_producto
(id, producto_id, proveedor_id, estado_lote_id, codigo_lote, fecha_entrada, fecha_caducidad, cantidad_inicial, cantidad_actual, costo_unitario_sin_iva, created_at, updated_at)
VALUES
(1, 1, 1, 1, 'POM-2025-001', '2025-06-01', '2026-06-01', 15, 8, 95.00, NOW(), NOW()),
(2, 2, 1, 1, 'ACE-2025-001', '2025-06-01', '2025-08-15', 8, 3, 120.00, NOW(), NOW()),
(3, 3, 2, 2, 'SHA-2025-001', '2025-06-01', '2025-07-25', 10, 0, 85.00, NOW(), NOW()),
(4, 4, 1, 1, 'CER-2025-001', '2025-06-05', '2026-01-30', 20, 12, 80.00, NOW(), NOW()),
(5, 5, 2, 1, 'GEL-2025-001', '2025-06-05', '2026-03-20', 25, 10, 45.00, NOW(), NOW());

INSERT INTO tipos_movimiento_inventario
(id, nombre, descripcion, activo)
VALUES
(1, 'Entrada compra', 'Entrada por compra a proveedor.', 1),
(2, 'Salida venta', 'Salida por venta al cliente.', 1),
(3, 'Ajuste entrada', 'Corrección positiva de inventario.', 1),
(4, 'Ajuste salida', 'Corrección negativa de inventario.', 1),
(5, 'Merma', 'Producto dañado o perdido.', 1),
(6, 'Caducidad', 'Salida por producto caducado.', 1);

INSERT INTO metodos_pago
(id, nombre, activo)
VALUES
(1, 'Efectivo', 1),
(2, 'Tarjeta', 1),
(3, 'Transferencia', 1);

INSERT INTO ventas
(id, folio, sucursal_id, cliente_id, colaborador_id, cita_id, metodo_pago_id, fecha_venta,
 subtotal_sin_iva, iva_total, total_con_iva, costo_total_sin_iva, utilidad_total, created_at, updated_at)
VALUES
(1, 'TCK-0001', 1, 1, 1, 2, 1, '2025-06-28 12:35:00',
 284.48, 45.52, 330.00, 160.00, 124.48, NOW(), NOW());

INSERT INTO venta_servicios
(id, venta_id, servicio_id, cita_servicio_id, colaborador_id, cantidad,
 costo_estimado_unitario_sin_iva_snapshot, costo_total_estimado_sin_iva,
 precio_unitario_sin_iva, iva_porcentaje_aplicado, iva_monto, total_linea_con_iva, utilidad_linea)
VALUES
(1, 1, 1, 3, 2, 1, 40.00, 40.00, 129.31, 16.00, 20.69, 150.00, 89.31);

INSERT INTO venta_productos
(id, venta_id, producto_id, lote_producto_id, cantidad,
 costo_unitario_sin_iva_snapshot, costo_total_sin_iva,
 precio_unitario_sin_iva, iva_porcentaje_aplicado, iva_monto, total_linea_con_iva, utilidad_linea)
VALUES
(1, 1, 2, 2, 1, 120.00, 120.00, 189.66, 16.00, 30.34, 220.00, 69.66);

INSERT INTO movimientos_inventario
(producto_id, lote_producto_id, tipo_movimiento_inventario_id, venta_producto_id, fecha_movimiento, cantidad, costo_unitario_sin_iva, motivo)
VALUES
(1, 1, 1, NULL, '2025-06-01 09:00:00', 15, 95.00, 'Carga inicial de inventario'),
(2, 2, 1, NULL, '2025-06-01 09:10:00', 8, 120.00, 'Carga inicial de inventario'),
(3, 3, 1, NULL, '2025-06-01 09:20:00', 10, 85.00, 'Carga inicial de inventario'),
(4, 4, 1, NULL, '2025-06-05 09:00:00', 20, 80.00, 'Carga inicial de inventario'),
(5, 5, 1, NULL, '2025-06-05 09:10:00', 25, 45.00, 'Carga inicial de inventario'),
(2, 2, 2, 1, '2025-06-28 12:35:00', -1, 120.00, 'Venta TCK-0001');

-- ==========================================================
-- CONSULTAS RÁPIDAS DE VALIDACIÓN
-- ==========================================================

-- SELECT * FROM cuentas;
-- SELECT * FROM clientes;
-- SELECT * FROM colaboradores;
-- SELECT * FROM servicios;
-- SELECT * FROM citas;
-- SELECT * FROM bloques_agenda;
-- SELECT p.nombre, SUM(lp.cantidad_actual) AS stock_total
-- FROM productos p
-- LEFT JOIN lotes_producto lp ON lp.producto_id = p.id
-- GROUP BY p.id, p.nombre;
-- SELECT * FROM ventas;

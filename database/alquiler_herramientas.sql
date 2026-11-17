-- ============================================================================
-- Sistema de alquiler de herramientas
-- Estructura + datos de prueba. Se puede importar las veces que haga falta:
-- borra las tablas y las vuelve a crear (no borra la base).
--
-- Importar con:
--   mysql -u root alquiler_herramientas < database/alquiler_herramientas.sql
-- o desde phpMyAdmin: Importar -> elegir este archivo.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS alquiler_herramientas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE alquiler_herramientas;

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detalle_alquiler;
DROP TABLE IF EXISTS alquileres;
DROP TABLE IF EXISTS herramientas;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Usuarios del sistema (quienes operan la aplicación, no los clientes)
-- Contraseñas guardadas con password_hash().
-- ----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    usuario VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'vendedor', 'cliente') NOT NULL DEFAULT 'vendedor',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT uq_usuarios_usuario UNIQUE (usuario)
) ENGINE = InnoDB;

-- ----------------------------------------------------------------------------
-- Clientes (entidad B: quienes alquilan)
-- ----------------------------------------------------------------------------
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    deuda DECIMAL(10, 2) NOT NULL DEFAULT 0,
    CONSTRAINT uq_clientes_dni UNIQUE (dni),
    CONSTRAINT ck_clientes_deuda CHECK (deuda >= 0)
) ENGINE = InnoDB;

-- ----------------------------------------------------------------------------
-- Categorías de herramientas
-- ----------------------------------------------------------------------------
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE = InnoDB;

-- ----------------------------------------------------------------------------
-- Herramientas (entidad A: lo que se alquila)
-- ----------------------------------------------------------------------------
CREATE TABLE herramientas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    precio_dia DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_herramientas_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT ck_herramientas_precio CHECK (precio_dia >= 0),
    CONSTRAINT ck_herramientas_stock CHECK (stock >= 0 AND stock_minimo >= 0)
) ENGINE = InnoDB;

-- ----------------------------------------------------------------------------
-- Alquileres (la operación: une un cliente con una o más herramientas)
-- Estados válidos: pendiente -> activo -> devuelto | cancelado
-- ----------------------------------------------------------------------------
CREATE TABLE alquileres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('pendiente', 'activo', 'devuelto', 'cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alquileres_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_alquileres_vendedor
        FOREIGN KEY (vendedor_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_alquileres_fechas CHECK (fecha_fin >= fecha_inicio),
    INDEX idx_alquileres_estado (estado),
    INDEX idx_alquileres_fechas (fecha_inicio, fecha_fin),
    INDEX idx_alquileres_cliente (cliente_id)
) ENGINE = InnoDB;

-- ----------------------------------------------------------------------------
-- Detalle: qué herramientas y cuántas lleva cada alquiler
-- ----------------------------------------------------------------------------
CREATE TABLE detalle_alquiler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alquiler_id INT NOT NULL,
    herramienta_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_dia DECIMAL(10, 2) NOT NULL,
    dias INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_detalle_alquiler
        FOREIGN KEY (alquiler_id) REFERENCES alquileres (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_herramienta
        FOREIGN KEY (herramienta_id) REFERENCES herramientas (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_detalle_cantidad CHECK (cantidad > 0)
) ENGINE = InnoDB;

-- ============================================================================
-- DATOS DE PRUEBA
-- ============================================================================

-- Contraseña de ambos usuarios: 1234
-- Hash generado con password_hash('1234', PASSWORD_DEFAULT).
INSERT INTO usuarios (id, nombre, usuario, contrasena, rol) VALUES
(1, 'Administrador', 'admin', '$2y$10$GV0VMngf/M/3l2rvKa.6s.CWLjOR1Oto.EMfiIMDTxdBLXwp9oOGC', 'administrador'),
(2, 'Vendedor de prueba', 'vendedor', '$2y$10$GV0VMngf/M/3l2rvKa.6s.CWLjOR1Oto.EMfiIMDTxdBLXwp9oOGC', 'vendedor');

INSERT INTO clientes (id, nombre, dni, telefono, email, activo, deuda) VALUES
(1, 'Juan Pérez', '30111222', '3764-111111', 'juan@example.com', 1, 0.00),
(2, 'María López', '28999888', '3764-222222', 'maria@example.com', 1, 0.00),
(3, 'Carlos Gómez', '25444555', '3764-333333', NULL, 0, 1500.00);

INSERT INTO categorias (id, nombre) VALUES
(1, 'Herramientas eléctricas'),
(2, 'Herramientas manuales'),
(3, 'Jardinería');

INSERT INTO herramientas (id, categoria_id, nombre, precio_dia, stock, stock_minimo) VALUES
(1, 1, 'Taladro', 4500.00, 5, 1),
(2, 1, 'Amoladora', 3800.00, 3, 1),
(3, 2, 'Martillo', 800.00, 10, 2),
(4, 3, 'Cortadora de césped', 9000.00, 2, 1),
(5, 1, 'Lijadora', 2500.00, 4, 1);

-- Alquiler devuelto (2 días)
INSERT INTO alquileres (id, cliente_id, vendedor_id, fecha_inicio, fecha_fin, estado, total) VALUES
(1, 1, 2, '2026-11-01', '2026-11-03', 'devuelto', 12200.00),
(2, 2, 2, '2026-11-10', '2026-11-12', 'activo', 25600.00),
(3, 1, 2, '2026-11-05', '2026-11-06', 'cancelado', 2500.00);

INSERT INTO detalle_alquiler (alquiler_id, herramienta_id, cantidad, precio_dia, dias, subtotal) VALUES
(1, 1, 1, 4500.00, 2, 9000.00),
(1, 3, 2, 800.00, 2, 3200.00),
(2, 2, 1, 3800.00, 2, 7600.00),
(2, 4, 1, 9000.00, 2, 18000.00),
(3, 5, 1, 2500.00, 1, 2500.00);

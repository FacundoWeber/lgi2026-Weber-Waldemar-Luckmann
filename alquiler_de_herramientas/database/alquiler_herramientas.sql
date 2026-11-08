CREATE DATABASE IF NOT EXISTS alquiler_herramientas
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alquiler_herramientas;

CREATE TABLE usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL,
 usuario VARCHAR(50) NOT NULL UNIQUE,
 contrasena VARCHAR(255) NOT NULL,
 rol ENUM('administrador','vendedor','cliente') NOT NULL DEFAULT 'vendedor',
 activo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE clientes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 dni VARCHAR(20) UNIQUE,
 telefono VARCHAR(30)
);

CREATE TABLE categorias (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE herramientas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 categoria_id INT NULL,
 nombre VARCHAR(100) NOT NULL,
 precio_dia DECIMAL(10,2) NOT NULL,
 stock INT NOT NULL DEFAULT 0,
 stock_minimo INT NOT NULL DEFAULT 1,
 FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE alquileres (
 id INT AUTO_INCREMENT PRIMARY KEY,
 cliente_id INT NOT NULL,
 vendedor_id INT NOT NULL,
 fecha_inicio DATE NOT NULL,
 fecha_fin DATE NOT NULL,
 estado ENUM('pendiente','activo','devuelto','cancelado') DEFAULT 'pendiente',
 total DECIMAL(10,2) NOT NULL DEFAULT 0,
 FOREIGN KEY (cliente_id) REFERENCES clientes(id),
 FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
);

CREATE TABLE detalle_alquiler (
 id INT AUTO_INCREMENT PRIMARY KEY,
 alquiler_id INT NOT NULL,
 herramienta_id INT NOT NULL,
 cantidad INT NOT NULL,
 precio_dia DECIMAL(10,2) NOT NULL,
 dias INT NOT NULL,
 subtotal DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (alquiler_id) REFERENCES alquileres(id) ON DELETE CASCADE,
 FOREIGN KEY (herramienta_id) REFERENCES herramientas(id)
);

INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES
('Administrador', 'admin', '$2y$12$WZj/PBFfqRu3YHWa4DXs6OaqOGtHNQEukHDBZq6YNoQOcKlxRZpyq', 'administrador'),
('Vendedor de prueba', 'vendedor', '$2y$12$WZj/PBFfqRu3YHWa4DXs6OaqOGtHNQEukHDBZq6YNoQOcKlxRZpyq', 'vendedor');

INSERT INTO clientes (nombre, dni, telefono) VALUES
('Juan Perez', '30111222', '3764-111111'),
('Maria Lopez', '28999888', '3764-222222');

INSERT INTO categorias (nombre) VALUES
('Herramientas electricas'), ('Herramientas manuales'), ('Jardineria');

INSERT INTO herramientas (categoria_id, nombre, precio_dia, stock, stock_minimo) VALUES
(1, 'Taladro', 4500.00, 5, 1),
(1, 'Amoladora', 3800.00, 3, 1),
(2, 'Martillo', 800.00, 10, 2),
(3, 'Cortadora de cesped', 9000.00, 2, 1);

drop database if exists tienda_online;
create database if not exists tienda_online;

use tienda_online;

######################## TABLAS ########################

create table estado_pedido(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    color VARCHAR(20)
);

create table usuario(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    username VARCHAR(50) UNIQUE,
    imagen_perfil BLOB,
    pass VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    fecha_nac DATE,
    cp VARCHAR(20),
    rol ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL DEFAULT NULL,
    activo BOOLEAN DEFAULT TRUE
);

create table genero(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

create table plataforma(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

create table producto(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    imagen VARCHAR(255),
    descripcion TEXT,
    fecha_lanzamiento DATE,
    genero_id INT,
    precio DECIMAL(10,2),
    descuento INT DEFAULT NULL,
    stock INT,
    plataforma_id INT,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_por INT,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (genero_id) REFERENCES genero(id),
    FOREIGN KEY (plataforma_id) REFERENCES plataforma(id)
);

create table stock_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT,
    stock_reservado INT,
    stock_disponible INT,
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);

create table pedido(
	id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    precio_total DECIMAL(10,2),
    estado_pedido_id INT,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    fecha_envio TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id),
    FOREIGN KEY (estado_pedido_id) REFERENCES estado_pedido(id)
);

create table pedido_item(
	id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    producto_id INT,
    cantidad INT,
    precio_total DECIMAL(10,2),
    FOREIGN KEY (pedido_id) REFERENCES pedido(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);

create table carrito(
    id INT AUTO_INCREMENT PRIMARY KEY,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

create table carrito_item(
    id INT AUTO_INCREMENT PRIMARY KEY,
    carrito_id INT,
    producto_id INT,
    cantidad INT,
    precio_total DECIMAL(10,2),
    FOREIGN KEY (carrito_id) REFERENCES carrito(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);

create table favorito(
	id INT AUTO_INCREMENT PRIMARY KEY,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

create table favorito_item(
	id INT AUTO_INCREMENT PRIMARY KEY,
    favorito_id INT,
    producto_id INT,
    FOREIGN KEY (favorito_id) REFERENCES favorito(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);

CREATE TABLE producto_genero (
    producto_id INT NOT NULL,
    genero_id INT NOT NULL,
    PRIMARY KEY (producto_id, genero_id),
    FOREIGN KEY (producto_id) REFERENCES producto(id),
    FOREIGN KEY (genero_id) REFERENCES genero(id)
);

CREATE TABLE producto_plataforma (
    producto_id INT NOT NULL,
    plataforma_id INT NOT NULL,
    PRIMARY KEY (producto_id, plataforma_id),
    FOREIGN KEY (producto_id) REFERENCES producto(id),
    FOREIGN KEY (plataforma_id) REFERENCES plataforma(id)
);

CREATE TABLE votos (
	id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    valoracion INT DEFAULT 0 CHECK(valoracion BETWEEN 1 AND 5),
    CONSTRAINT fk_votos_usu FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_votos_pro FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE
);

######################## INSERCIONES ########################

-- Insertar estados de pedido
INSERT INTO estado_pedido (nombre, color) VALUES
('Pendiente', 'Azul'),
('Enviado', 'Verde'),
('Entregado', 'Gris'),
('Cancelado', 'Rojo');

-- Insertar usuarios (admin y usuario normal)
INSERT INTO usuario (nombre, username, pass, email, telefono, direccion, fecha_nac, cp, rol) VALUES
('Admin', 'admin123', 'adminpass', 'admin@email.com', '123456789', 'Calle Falsa 123', '1990-01-01', '28001', 'admin'),
('Juan Pérez', 'juanp', '123456', 'juan@email.com', '654987321', 'Av. Siempre Viva 742', '1995-06-15', '28002', 'user');

-- Insertar géneros de videojuegos
INSERT INTO genero (nombre) VALUES
('Acción'),
('Aventura'),
('RPG'),
('Deportes'),
('Estrategia');

-- Insertar plataformas
INSERT INTO plataforma (nombre) VALUES
('PC'),
('PlayStation 5'),
('Xbox Series X'),
('Nintendo Switch');

-- Insertar productos (videojuegos)
INSERT INTO producto (nombre, imagen, descripcion, fecha_lanzamiento, genero_id, precio, stock, plataforma_id, creado_por) VALUES
('The Witcher 3', 'witcher3.jpg', 'Juego de rol y aventura en mundo abierto.', '2015-05-19', 3, 29.99, 50, 1, 1),
('FIFA 23', 'fifa23.jpg', 'El mejor simulador de fútbol.', '2022-09-30', 4, 59.99, 30, 2, 1),
('Zelda: Breath of the Wild', 'zelda.jpg', 'Aventura épica en mundo abierto.', '2017-03-03', 2, 49.99, 40, 4, 1);

-- Insertar stock de productos
INSERT INTO stock_producto (producto_id, stock_reservado, stock_disponible) VALUES
(1, 5, 45),
(2, 3, 27),
(3, 4, 36);

-- Insertar pedidos
INSERT INTO pedido (usuario_id, precio_total, estado_pedido_id, creado_por) VALUES
(2, 59.99, 1, 2),  -- Pedido de Juan Pérez, pendiente
(2, 29.99, 2, 2);  -- Pedido de Juan Pérez, enviado

-- Insertar items en pedidos
INSERT INTO pedido_item (pedido_id, producto_id, cantidad, precio_total) VALUES
(1, 2, 1, 59.99),  -- FIFA 23
(2, 1, 1, 29.99);  -- The Witcher 3

-- Insertar carritos de compra
INSERT INTO carrito (creado_por) VALUES
(2);

-- Insertar items en carritos
INSERT INTO carrito_item (carrito_id, producto_id, cantidad, precio_total) VALUES
(1, 3, 1, 49.99);  -- Zelda en el carrito de Juan Pérez

-- Insertar favoritos
INSERT INTO favorito (creado_por) VALUES
(2);

-- Insertar items en favoritos
INSERT INTO favorito_item (favorito_id, producto_id) VALUES
(1, 1);  -- Juan tiene The Witcher 3 en favoritos

-- Insertar relaciones entre productos y géneros (muchos a muchos)
INSERT INTO producto_genero (producto_id, genero_id) VALUES
(1, 3),  -- The Witcher 3 es RPG
(2, 4),  -- FIFA 23 es de Deportes
(3, 2);  -- Zelda es de Aventura

-- Insertar relaciones entre productos y plataformas (muchos a muchos)
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES
(1, 1),  -- The Witcher 3 en PC
(2, 2),  -- FIFA 23 en PS5
(3, 4);  -- Zelda en Switch


######################## CONSULTAS ########################

select * from carrito;
select * from carrito_item;
select * from estado_pedido;
select * from favorito;
select * from favorito_item;
select * from genero;
select * from pedido;
select * from pedido_item;
select * from plataforma;
select * from producto;
select * from producto_genero;
select * from producto_plataforma;
select * from stock_producto;
select * from usuario;
select * from votos;

INSERT INTO producto (nombre, imagen, descripcion, fecha_lanzamiento, genero_id, precio, descuento, stock, plataforma_id, creado_por) VALUES
('The Witcher 3', '../images/TheWitcher3_WH.jpg', 'Juego de rol y aventura en mundo abierto.', '2015-05-19', 3, 29.99, 15, 50, 1, 1);
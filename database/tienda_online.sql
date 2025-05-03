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
    username VARCHAR(50) UNIQUE NOT NULL,
    imagen_perfil LONGBLOB,
    pass VARCHAR(50),
    email VARCHAR(100) UNIQUE NOT NULL,
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
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

create table plataforma(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

create table producto(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    descripcion TEXT NOT NULL,
    fecha_lanzamiento DATE NOT NULL,
    genero_id INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    descuento INT DEFAULT NULL,
    stock INT NOT NULL,
    plataforma_id INT NOT NULL,
    creado_por INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    actualizado_por INT DEFAULT NULL,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (genero_id) REFERENCES genero(id),
    FOREIGN KEY (plataforma_id) REFERENCES plataforma(id),
    FOREIGN KEY (creado_por) REFERENCES usuario(id),
    FOREIGN KEY (actualizado_por) REFERENCES usuario(id)
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
    FOREIGN KEY (estado_pedido_id) REFERENCES estado_pedido(id),
    FOREIGN KEY (creado_por) REFERENCES usuario(id)
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
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (creado_por) REFERENCES usuario(id)
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
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (creado_por) REFERENCES usuario(id)
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
('Admin', 'admin123', 'adminpass', 'admin@email.com', '123456789', 'Calle Falsa 123', '1990-01-01', '28001', 'admin');

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

INSERT INTO producto (nombre, imagen, descripcion, fecha_lanzamiento, genero_id, precio, descuento, stock, plataforma_id, creado_por)
VALUES
('Elden Ring', 'default.jpg', 'Juego de rol de acción en mundo abierto.', '2022-02-25', 3, 59.99, NULL, 50, 1, 1),
('Baldur''s Gate 3', 'default.jpg', 'RPG basado en D&D en un mundo de fantasía.', '2023-08-03', 3, 69.99, 20, 40, 1, 1),
('Cyberpunk 2077', 'default.jpg', 'RPG de acción futurista en Night City.', '2020-12-10', 1, 39.99, 10, 60, 1, 1),
('Diablo IV', 'default.jpg', 'Juego de rol de acción y mazmorras.', '2023-06-06', 3, 69.99, NULL, 35, 1, 1),
('Red Dead Redemption 2', 'default.jpg', 'Acción en el Viejo Oeste.', '2018-10-26', 2, 29.99, 15, 25, 1, 1),
('Doom Eternal', 'default.jpg', 'Shooter en primera persona de acción rápida.', '2020-03-20', 1, 24.99, NULL, 40, 1, 1),
('Resident Evil 4 Remake', 'default.jpg', 'Remake de clásico survival horror.', '2023-03-24', 2, 59.99, 5, 20, 1, 1),
('Hogwarts Legacy', 'default.jpg', 'RPG de acción en el mundo de Harry Potter.', '2023-02-10', 2, 69.99, 10, 45, 1, 1),
('Alan Wake 2', 'default.jpg', 'Terror y suspenso en tercera persona.', '2023-10-27', 2, 69.99, NULL, 30, 1, 1),
('Forza Horizon 5', 'default.jpg', 'Carreras de mundo abierto en México.', '2021-11-09', 4, 49.99, NULL, 70, 1, 1),
('Counter Strike 2', 'default.jpg', 'Shooter competitivo por equipos.', '2023-09-27', 1, 19.99, NULL, 100, 1, 1),
('Starfield', 'default.jpg', 'Exploración espacial RPG.', '2023-09-06', 3, 69.99, 5, 30, 1, 1),
('Palworld', 'default.jpg', 'Criaturas, supervivencia y armas.', '2024-01-19', 2, 29.99, NULL, 80, 1, 1),
('Overwatch 2', 'default.jpg', 'Shooter multijugador de héroes.', '2022-10-04', 1, 0.00, NULL, 200, 1, 1),
('Assassin’s Creed Mirage', 'default.jpg', 'Acción y sigilo en Bagdad.', '2023-10-05', 2, 49.99, 10, 35, 1, 1),
('Sons of the Forest', 'default.jpg', 'Survival horror cooperativo.', '2023-02-23', 2, 29.99, NULL, 50, 1, 1),
('Destiny 2: Lightfall', 'default.jpg', 'Expansión de shooter espacial.', '2023-02-28', 1, 39.99, NULL, 60, 1, 1),
('Lies of P', 'default.jpg', 'Soulslike basado en Pinocho.', '2023-09-19', 2, 59.99, 15, 20, 1, 1),
('Hades II', 'default.jpg', 'Roguelike de acción mitológica.', '2024-04-17', 3, 24.99, NULL, 40, 1, 1),
('Ghostrunner 2', 'default.jpg', 'Acción de parkour cibernético.', '2023-10-26', 1, 39.99, 10, 30, 1, 1),
('The Legend of Zelda: Tears of the Kingdom', 'default.jpg', 'Aventura épica en Hyrule.', '2023-05-12', 2, 69.99, NULL, 30, 4, 1),
('Spider-Man: Miles Morales', 'default.jpg', 'Acción de superhéroes en Nueva York.', '2020-11-12', 1, 49.99, 10, 40, 2, 1),
('Final Fantasy XVI', 'default.jpg', 'RPG de acción fantástico.', '2023-06-22', 3, 69.99, 15, 25, 2, 1),
('FIFA 24', 'default.jpg', 'Fútbol realista y competitivo.', '2023-09-29', 4, 69.99, NULL, 100, 3, 1),
('NBA 2K24', 'default.jpg', 'Basket de última generación.', '2023-09-08', 4, 69.99, NULL, 90, 2, 1),
('Mortal Kombat 1', 'default.jpg', 'Lucha brutal y cinemática.', '2023-09-19', 1, 69.99, 5, 50, 2, 1),
('Super Mario Wonder', 'default.jpg', 'Plataformas clásicas renovadas.', '2023-10-20', 2, 59.99, NULL, 60, 4, 1),
('Mario Kart 8 Deluxe', 'default.jpg', 'Carreras frenéticas de Mario.', '2017-04-28', 4, 59.99, 20, 80, 4, 1),
('God of War Ragnarök', 'default.jpg', 'Aventura mitológica intensa.', '2022-11-09', 2, 69.99, NULL, 30, 2, 1),
('The Last of Us Part I', 'default.jpg', 'Survival post-apocalíptico.', '2022-09-02', 2, 69.99, 10, 20, 2, 1),
('Dead Space Remake', 'default.jpg', 'Terror espacial remasterizado.', '2023-01-27', 2, 59.99, NULL, 25, 2, 1),
('Pikmin 4', 'default.jpg', 'Estrategia y aventura.', '2023-07-21', 5, 59.99, NULL, 35, 4, 1),
('Metroid Dread', 'default.jpg', 'Acción y exploración en 2D.', '2021-10-08', 2, 59.99, NULL, 40, 4, 1),
('Halo Infinite', 'default.jpg', 'Shooter icónico renovado.', '2021-12-08', 1, 59.99, 15, 50, 3, 1),
('Sea of Thieves', 'default.jpg', 'Aventura pirata online.', '2018-03-20', 2, 39.99, NULL, 70, 3, 1),
('Call of Duty: Modern Warfare III', 'default.jpg', 'Shooter militar de acción.', '2023-11-10', 1, 69.99, NULL, 100, 2, 1),
('It Takes Two', 'default.jpg', 'Cooperativo de aventuras.', '2021-03-26', 2, 39.99, 20, 30, 3, 1),
('Ratchet & Clank: Rift Apart', 'default.jpg', 'Acción de plataformas futurista.', '2021-06-11', 1, 69.99, 10, 20, 2, 1),
('Horizon Forbidden West', 'default.jpg', 'Mundo abierto post-apocalíptico.', '2022-02-18', 2, 69.99, NULL, 45, 2, 1),
('Returnal', 'default.jpg', 'Acción roguelike en bucle.', '2021-04-30', 1, 59.99, 20, 25, 2, 1);

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
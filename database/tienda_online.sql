drop database if exists tienda_online;
create database if not exists tienda_online;

use tienda_online;

######################## TABLAS ########################

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    username VARCHAR(50) UNIQUE NOT NULL,
    imagen_perfil LONGBLOB,
    pass VARCHAR(255),
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

CREATE TABLE genero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE plataforma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    descripcion TEXT NOT NULL,
    fecha_lanzamiento DATE NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    descuento INT DEFAULT NULL,
    creado_por INT NOT NULL,
    actualizado_por INT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (creado_por) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES usuario(id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE producto_stock (
    producto_id INT NOT NULL,
    plataforma_id INT NOT NULL,
    stock_disponible INT DEFAULT 0,
    stock_reservado INT DEFAULT 0,
    PRIMARY KEY (producto_id, plataforma_id),
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (plataforma_id) REFERENCES plataforma(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    precio_total DECIMAL(10,2),
    descuento DECIMAL(10,2) DEFAULT 0.00,
    estado ENUM('pendiente', 'cancelado', 'entregado') DEFAULT 'pendiente',
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    fecha_envio TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE pedido_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    producto_id INT,
    plataforma_id INT,
    cantidad INT,
    precio_total DECIMAL(10,2),
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (creado_por) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE carrito_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carrito_id INT,
    producto_id INT,
    cantidad INT,
    plataforma_id INT NOT NULL,
    precio_total DECIMAL(10,2),
    FOREIGN KEY (carrito_id) REFERENCES carrito(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (plataforma_id) REFERENCES plataforma(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE favorito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (creado_por) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE favorito_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    favorito_id INT,
    producto_id INT,
    FOREIGN KEY (favorito_id) REFERENCES favorito(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE producto_genero (
    producto_id INT NOT NULL,
    genero_id INT NOT NULL,
    PRIMARY KEY (producto_id, genero_id),
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (genero_id) REFERENCES genero(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE producto_plataforma (
    producto_id INT NOT NULL,
    plataforma_id INT NOT NULL,
    PRIMARY KEY (producto_id, plataforma_id),
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (plataforma_id) REFERENCES plataforma(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    valoracion INT DEFAULT 0 CHECK (valoracion BETWEEN 1 AND 5),
    CONSTRAINT fk_votos_usu FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_votos_pro FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE facturacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pedido_id INT NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    numero_tarjeta VARCHAR(20) DEFAULT NULL,
    vencimiento_tarjeta VARCHAR(5) DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE ON UPDATE CASCADE
);

######################## INSERCIONES ########################

-- Insertar usuarios (admin y usuario normal)
INSERT INTO usuario (nombre, username, pass, email, telefono, direccion, fecha_nac, cp, rol) VALUES
('Admin', 'admin123', 'adminpass', 'admin@email.com', '123456789', 'Calle Falsa 123', '1990-01-01', '28001', 'admin');

INSERT INTO genero (nombre) VALUES
('Acción'),
('Arcade'), 
('Aventura'), 
('Beat''em all'),
('Carreras'), 
('Cloud Gaming'), 
('Cooperación'),
('Cooperativo local'), 
('Cooperativo online'), 
('Deporte'), 
('Entrenamiento'), 
('Estrategia'), 
('FPS'), 
('Gestión'), 
('Indies'), 
('Juegos para PS5'), 
('Lucha'), 
('MMO'), 
('Multijugador'), 
('Multijugador multiplataforma'),
('Música'), 
('Plataformas'), 
('PvP online'), 
('RPG'), 
('Shoot''em up'), 
('Simulación'), 
('Un solo jugador'), 
('Wargame');

-- acomoda las que tengo debajo en orden alfabético
INSERT INTO plataforma (nombre) VALUES
('Nintendo Switch'),
('PC'),
('PlayStation 4'),
('PlayStation 5'),
('Xbox One'),
('Xbox Series S'),
('Xbox Series X');

INSERT INTO producto (nombre, imagen, descripcion, fecha_lanzamiento, precio, descuento, creado_por)
VALUES
('Elden Ring', 'default.jpg', 'Juego de rol de acción en mundo abierto.', '2022-02-25', 59.99, NULL, 1),
('Baldur''s Gate 3', 'default.jpg', 'RPG basado en D&D en un mundo de fantasía.', '2023-08-03', 69.99, 20, 1),
('Cyberpunk 2077', 'default.jpg', 'RPG de acción futurista en Night City.', '2020-12-10', 39.99, 10, 1),
('Diablo IV', 'default.jpg', 'Juego de rol de acción y mazmorras.', '2023-06-06', 69.99, NULL, 1),
('Red Dead Redemption 2', 'default.jpg', 'Acción en el Viejo Oeste.', '2018-10-26', 29.99, 15, 1),
('Doom Eternal', 'default.jpg', 'Shooter en primera persona de acción rápida.', '2020-03-20', 24.99, NULL, 1),
('Resident Evil 4 Remake', 'default.jpg', 'Remake de clásico survival horror.', '2023-03-24', 59.99, 5, 1),
('Hogwarts Legacy', 'default.jpg', 'RPG de acción en el mundo de Harry Potter.', '2023-02-10', 69.99, 10, 1),
('Alan Wake 2', 'default.jpg', 'Terror y suspenso en tercera persona.', '2023-10-27', 69.99, NULL, 1),
('Forza Horizon 5', 'default.jpg', 'Carreras de mundo abierto en México.', '2021-11-09', 49.99, NULL, 1),
('Counter Strike 2', 'default.jpg', 'Shooter competitivo por equipos.', '2023-09-27', 19.99, NULL, 1),
('Starfield', 'default.jpg', 'Exploración espacial RPG.', '2023-09-06', 69.99, 5, 1),
('Palworld', 'default.jpg', 'Criaturas, supervivencia y armas.', '2024-01-19', 29.99, NULL, 1),
('Overwatch 2', 'default.jpg', 'Shooter multijugador de héroes.', '2022-10-04', 0.00, NULL, 1),
('Assassin’s Creed Mirage', 'default.jpg', 'Acción y sigilo en Bagdad.', '2023-10-05', 49.99, 10, 1),
('Sons of the Forest', 'default.jpg', 'Survival horror cooperativo.', '2023-02-23', 29.99, NULL, 1),
('Destiny 2: Lightfall', 'default.jpg', 'Expansión de shooter espacial.', '2023-02-28', 39.99, NULL, 1),
('Lies of P', 'default.jpg', 'Soulslike basado en Pinocho.', '2023-09-19', 59.99, 15, 1),
('Hades II', 'default.jpg', 'Roguelike de acción mitológica.', '2024-04-17', 24.99, NULL, 1),
('Ghostrunner 2', 'default.jpg', 'Acción de parkour cibernético.', '2023-10-26', 39.99, 10, 1),
('The Legend of Zelda: Tears of the Kingdom', 'default.jpg', 'Aventura épica en Hyrule.', '2023-05-12', 69.99, NULL, 1),
('Spider-Man: Miles Morales', 'default.jpg', 'Acción de superhéroes en Nueva York.', '2020-11-12', 49.99, 10, 1),
('Final Fantasy XVI', 'default.jpg', 'RPG de acción fantástico.', '2023-06-22', 69.99, 15, 1),
('FIFA 24', 'default.jpg', 'Fútbol realista y competitivo.', '2023-09-29', 69.99, NULL, 1),
('NBA 2K24', 'default.jpg', 'Basket de última generación.', '2023-09-08', 69.99, NULL, 1),
('Mortal Kombat 1', 'default.jpg', 'Lucha brutal y cinemática.', '2023-09-19', 69.99, 5, 1),
('Super Mario Wonder', 'default.jpg', 'Plataformas clásicas renovadas.', '2023-10-20', 59.99, NULL, 1),
('Mario Kart 8 Deluxe', 'default.jpg', 'Carreras frenéticas de Mario.', '2017-04-28', 59.99, 20, 1),
('God of War Ragnarök', 'default.jpg', 'Aventura mitológica intensa.', '2022-11-09', 69.99, NULL, 1),
('The Last of Us Part I', 'default.jpg', 'Survival post-apocalíptico.', '2022-09-02', 69.99, 10, 1),
('Dead Space Remake', 'default.jpg', 'Terror espacial remasterizado.', '2023-01-27', 59.99, NULL, 1),
('Pikmin 4', 'default.jpg', 'Estrategia y aventura.', '2023-07-21', 59.99, NULL, 1),
('Metroid Dread', 'default.jpg', 'Acción y exploración en 2D.', '2021-10-08', 59.99, NULL, 1),
('Halo Infinite', 'default.jpg', 'Shooter icónico renovado.', '2021-12-08', 59.99, 15, 1),
('Sea of Thieves', 'default.jpg', 'Aventura pirata online.', '2018-03-20', 39.99, NULL, 1),
('Call of Duty: Modern Warfare III', 'default.jpg', 'Shooter militar de acción.', '2023-11-10', 69.99, NULL, 1),
('It Takes Two', 'default.jpg', 'Cooperativo de aventuras.', '2021-03-26', 39.99, 20, 1),
('Ratchet & Clank: Rift Apart', 'default.jpg', 'Acción de plataformas futurista.', '2021-06-11', 69.99, 10, 1),
('Horizon Forbidden West', 'default.jpg', 'Mundo abierto post-apocalíptico.', '2022-02-18', 69.99, NULL, 1),
('Returnal', 'default.jpg', 'Acción roguelike en bucle.', '2021-04-30', 59.99, 20, 1);

INSERT INTO producto_genero (producto_id, genero_id) VALUES (1, 1), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 13), (1, 16), (1, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (2, 3), (2, 23), (2, 24), (2, 26);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (3, 3), (3, 5), (3, 16), (3, 19), (3, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (4, 1), (4, 3), (4, 13), (4, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (5, 1), (5, 5), (5, 16), (5, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (6, 1), (6, 5), (6, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (7, 1), (7, 5), (7, 8), (7, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (8, 1), (8, 3), (8, 23), (8, 24), (8, 26);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (9, 1), (9, 3), (9, 8), (9, 13), (9, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (10, 5), (10, 13), (10, 16), (10, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (11, 1), (11, 5), (11, 16), (11, 19), (11, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (12, 3), (12, 13), (12, 16), (12, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (13, 1), (13, 3), (13, 5), (13, 16), (13, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (14, 1), (14, 5), (14, 16), (14, 19), (14, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (15, 1), (15, 3), (15, 4), (15, 8), (15, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (16, 1), (16, 5), (16, 8), (16, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (17, 1), (17, 5), (17, 16), (17, 19);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (18, 1), (18, 3), (18, 13), (18, 16), (18, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (19, 1), (19, 3), (19, 16), (19, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (20, 1), (20, 5), (20, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (21, 3), (21, 4), (21, 16), (21, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (22, 1), (22, 3), (22, 16), (22, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (23, 3), (23, 23), (23, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (24, 1), (24, 10), (24, 13), (24, 16), (24, 19);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (25, 1), (25, 10), (25, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (26, 1), (26, 4), (26, 8), (26, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (27, 1), (27, 4), (27, 16), (27, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (28, 5), (28, 16), (28, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (29, 1), (29, 4), (29, 16), (29, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (30, 1), (30, 3), (30, 8), (30, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (31, 1), (31, 5), (31, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (32, 3), (32, 5), (32, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (33, 1), (33, 3), (33, 4), (33, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (34, 1), (34, 5), (34, 16), (34, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (35, 1), (35, 3), (35, 5), (35, 16);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (36, 1), (36, 5), (36, 16), (36, 19);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (37, 1), (37, 8), (37, 16), (37, 24);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (38, 1), (38, 4), (38, 16), (38, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (39, 1), (39, 3), (39, 13), (39, 23);
INSERT INTO producto_genero (producto_id, genero_id) VALUES (40, 1), (40, 5), (40, 16);

INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (1, 2), (1, 4), (1, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (2, 2), (2, 4), (2, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (3, 2), (3, 4), (3, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (4, 2), (4, 4), (4, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (5, 2), (5, 4), (5, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (6, 2), (6, 4), (6, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (7, 2), (7, 4), (7, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (8, 2), (8, 4), (8, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (9, 2), (9, 4), (9, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (10, 2), (10, 4), (10, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (11, 2), (11, 4), (11, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (12, 2), (12, 4), (12, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (13, 2), (13, 4), (13, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (14, 2), (14, 4), (14, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (15, 2), (15, 4), (15, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (16, 2), (16, 4), (16, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (17, 2), (17, 4), (17, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (18, 2), (18, 4), (18, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (19, 2), (19, 4), (19, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (20, 2), (20, 4), (20, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (21, 4), (21, 2);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (22, 4), (22, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (23, 2), (23, 4), (23, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (24, 2), (24, 4), (24, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (25, 2), (25, 4), (25, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (26, 2), (26, 4), (26, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (27, 4), (27, 2);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (28, 4), (28, 2);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (29, 2), (29, 4), (29, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (30, 2), (30, 4), (30, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (31, 2), (31, 4), (31, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (32, 4), (32, 2);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (33, 2), (33, 4);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (34, 2), (34, 4), (34, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (35, 2), (35, 4), (35, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (36, 2), (36, 4), (36, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (37, 2), (37, 4), (37, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (38, 2), (38, 4), (38, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (39, 2), (39, 4), (39, 5);
INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (40, 2), (40, 4), (40, 5);

-- Elden Ring
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(1, 1, 100),  -- Nintendo Switch
(1, 2, 100),  -- PC
(1, 3, 100),  -- PlayStation 4
(1, 4, 100),  -- PlayStation 5
(1, 5, 100),  -- Xbox One
(1, 6, 100),  -- Xbox Series S
(1, 7, 100);  -- Xbox Series X

-- Baldur's Gate 3
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(2, 1, 100),  -- Nintendo Switch
(2, 2, 100),  -- PC
(2, 3, 100),  -- PlayStation 4
(2, 4, 100),  -- PlayStation 5
(2, 5, 100),  -- Xbox One
(2, 6, 100),  -- Xbox Series S
(2, 7, 100);  -- Xbox Series X

-- Cyberpunk 2077
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(3, 1, 100),  -- Nintendo Switch
(3, 2, 100),  -- PC
(3, 3, 100),  -- PlayStation 4
(3, 4, 100),  -- PlayStation 5
(3, 5, 100),  -- Xbox One
(3, 6, 100),  -- Xbox Series S
(3, 7, 100);  -- Xbox Series X

-- Diablo IV
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(4, 1, 100),  -- Nintendo Switch
(4, 2, 100),  -- PC
(4, 3, 100),  -- PlayStation 4
(4, 4, 100),  -- PlayStation 5
(4, 5, 100),  -- Xbox One
(4, 6, 100),  -- Xbox Series S
(4, 7, 100);  -- Xbox Series X

-- Red Dead Redemption 2
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(5, 1, 100),  -- Nintendo Switch
(5, 2, 100),  -- PC
(5, 3, 100),  -- PlayStation 4
(5, 4, 100),  -- PlayStation 5
(5, 5, 100),  -- Xbox One
(5, 6, 100),  -- Xbox Series S
(5, 7, 100);  -- Xbox Series X

-- Doom Eternal
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(6, 1, 100),  -- Nintendo Switch
(6, 2, 100),  -- PC
(6, 3, 100),  -- PlayStation 4
(6, 4, 100),  -- PlayStation 5
(6, 5, 100),  -- Xbox One
(6, 6, 100),  -- Xbox Series S
(6, 7, 100);  -- Xbox Series X

-- Resident Evil 4 Remake
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(7, 1, 100),  -- Nintendo Switch
(7, 2, 100),  -- PC
(7, 3, 100),  -- PlayStation 4
(7, 4, 100),  -- PlayStation 5
(7, 5, 100),  -- Xbox One
(7, 6, 100),  -- Xbox Series S
(7, 7, 100);  -- Xbox Series X

-- Hogwarts Legacy
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(8, 1, 100),  -- Nintendo Switch
(8, 2, 100),  -- PC
(8, 3, 100),  -- PlayStation 4
(8, 4, 100),  -- PlayStation 5
(8, 5, 100),  -- Xbox One
(8, 6, 100),  -- Xbox Series S
(8, 7, 100);  -- Xbox Series X

-- Alan Wake 2
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(9, 1, 100),  -- Nintendo Switch
(9, 2, 100),  -- PC
(9, 3, 100),  -- PlayStation 4
(9, 4, 100),  -- PlayStation 5
(9, 5, 100),  -- Xbox One
(9, 6, 100),  -- Xbox Series S
(9, 7, 100);  -- Xbox Series X

-- Forza Horizon 5
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(10, 1, 100),  -- Nintendo Switch
(10, 2, 100),  -- PC
(10, 3, 100),  -- PlayStation 4
(10, 4, 100),  -- PlayStation 5
(10, 5, 100),  -- Xbox One
(10, 6, 100),  -- Xbox Series S
(10, 7, 100);  -- Xbox Series X

-- Counter Strike 2
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(11, 1, 100),  -- Nintendo Switch
(11, 2, 100),  -- PC
(11, 3, 100),  -- PlayStation 4
(11, 4, 100),  -- PlayStation 5
(11, 5, 100),  -- Xbox One
(11, 6, 100),  -- Xbox Series S
(11, 7, 100);  -- Xbox Series X

-- Starfield
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(12, 1, 100),  -- Nintendo Switch
(12, 2, 100),  -- PC
(12, 3, 100),  -- PlayStation 4
(12, 4, 100),  -- PlayStation 5
(12, 5, 100),  -- Xbox One
(12, 6, 100),  -- Xbox Series S
(12, 7, 100);  -- Xbox Series X

-- Palworld
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(13, 1, 100),  -- Nintendo Switch
(13, 2, 100),  -- PC
(13, 3, 100),  -- PlayStation 4
(13, 4, 100),  -- PlayStation 5
(13, 5, 100),  -- Xbox One
(13, 6, 100),  -- Xbox Series S
(13, 7, 100);  -- Xbox Series X

-- Overwatch 2
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(14, 1, 100),  -- Nintendo Switch
(14, 2, 100),  -- PC
(14, 3, 100),  -- PlayStation 4
(14, 4, 100),  -- PlayStation 5
(14, 5, 100),  -- Xbox One
(14, 6, 100),  -- Xbox Series S
(14, 7, 100);  -- Xbox Series X

-- Assassin’s Creed Mirage
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(15, 1, 100),  -- Nintendo Switch
(15, 2, 100),  -- PC
(15, 3, 100),  -- PlayStation 4
(15, 4, 100),  -- PlayStation 5
(15, 5, 100),  -- Xbox One
(15, 6, 100),  -- Xbox Series S
(15, 7, 100);  -- Xbox Series X

-- Sons of the Forest
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(16, 1, 100),  -- Nintendo Switch
(16, 2, 100),  -- PC
(16, 3, 100),  -- PlayStation 4
(16, 4, 100),  -- PlayStation 5
(16, 5, 100),  -- Xbox One
(16, 6, 100),  -- Xbox Series S
(16, 7, 100);  -- Xbox Series X

-- Destiny 2: Lightfall
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(17, 1, 100),  -- Nintendo Switch
(17, 2, 100),  -- PC
(17, 3, 100),  -- PlayStation 4
(17, 4, 100),  -- PlayStation 5
(17, 5, 100),  -- Xbox One
(17, 6, 100),  -- Xbox Series S
(17, 7, 100);  -- Xbox Series X

-- Lies of P
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(18, 1, 100),  -- Nintendo Switch
(18, 2, 100),  -- PC
(18, 3, 100),  -- PlayStation 4
(18, 4, 100),  -- PlayStation 5
(18, 5, 100),  -- Xbox One
(18, 6, 100),  -- Xbox Series S
(18, 7, 100);  -- Xbox Series X

-- Hades II
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(19, 1, 100),  -- Nintendo Switch
(19, 2, 100),  -- PC
(19, 3, 100),  -- PlayStation 4
(19, 4, 100),  -- PlayStation 5
(19, 5, 100),  -- Xbox One
(19, 6, 100),  -- Xbox Series S
(19, 7, 100);  -- Xbox Series X

-- Ghostrunner 2
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(20, 1, 100),  -- Nintendo Switch
(20, 2, 100),  -- PC
(20, 3, 100),  -- PlayStation 4
(20, 4, 100),  -- PlayStation 5
(20, 5, 100),  -- Xbox One
(20, 6, 100),  -- Xbox Series S
(20, 7, 100);  -- Xbox Series X

-- The Legend of Zelda: Tears of the Kingdom
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(21, 1, 100),  -- Nintendo Switch
(21, 2, 100),  -- PC
(21, 3, 100),  -- PlayStation 4
(21, 4, 100),  -- PlayStation 5
(21, 5, 100),  -- Xbox One
(21, 6, 100),  -- Xbox Series S
(21, 7, 100);  -- Xbox Series X

-- Spider-Man: Miles Morales
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(22, 1, 100),  -- Nintendo Switch
(22, 2, 100),  -- PC
(22, 3, 100),  -- PlayStation 4
(22, 4, 100),  -- PlayStation 5
(22, 5, 100),  -- Xbox One
(22, 6, 100),  -- Xbox Series S
(22, 7, 100);  -- Xbox Series X

-- Final Fantasy XVI
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(23, 1, 100),  -- Nintendo Switch
(23, 2, 100),  -- PC
(23, 3, 100),  -- PlayStation 4
(23, 4, 100),  -- PlayStation 5
(23, 5, 100),  -- Xbox One
(23, 6, 100),  -- Xbox Series S
(23, 7, 100);  -- Xbox Series X

-- FIFA 24
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(24, 1, 100),  -- Nintendo Switch
(24, 2, 100),  -- PC
(24, 3, 100),  -- PlayStation 4
(24, 4, 100),  -- PlayStation 5
(24, 5, 100),  -- Xbox One
(24, 6, 100),  -- Xbox Series S
(24, 7, 100);  -- Xbox Series X

-- NBA 2K24
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(25, 1, 100),  -- Nintendo Switch
(25, 2, 100),  -- PC
(25, 3, 100),  -- PlayStation 4
(25, 4, 100),  -- PlayStation 5
(25, 5, 100),  -- Xbox One
(25, 6, 100),  -- Xbox Series S
(25, 7, 100);  -- Xbox Series X

-- Mortal Kombat 1
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(26, 1, 100),  -- Nintendo Switch
(26, 2, 100),  -- PC
(26, 3, 100),  -- PlayStation 4
(26, 4, 100),  -- PlayStation 5
(26, 5, 100),  -- Xbox One
(26, 6, 100),  -- Xbox Series S
(26, 7, 100);  -- Xbox Series X

-- Super Mario Wonder
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(27, 1, 100),  -- Nintendo Switch
(27, 2, 100),  -- PC
(27, 3, 100),  -- PlayStation 4
(27, 4, 100),  -- PlayStation 5
(27, 5, 100),  -- Xbox One
(27, 6, 100),  -- Xbox Series S
(27, 7, 100);  -- Xbox Series X

-- Mario Kart 8 Deluxe
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(28, 1, 100),  -- Nintendo Switch
(28, 2, 100),  -- PC
(28, 3, 100),  -- PlayStation 4
(28, 4, 100),  -- PlayStation 5
(28, 5, 100),  -- Xbox One
(28, 6, 100),  -- Xbox Series S
(28, 7, 100);  -- Xbox Series X

-- God of War Ragnarök
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(29, 1, 100),  -- Nintendo Switch
(29, 2, 100),  -- PC
(29, 3, 100),  -- PlayStation 4
(29, 4, 100),  -- PlayStation 5
(29, 5, 100),  -- Xbox One
(29, 6, 100),  -- Xbox Series S
(29, 7, 100);  -- Xbox Series X

-- The Last of Us Part I
INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(30, 1, 100),  -- Nintendo Switch
(30, 2, 100),  -- PC
(30, 3, 100),  -- PlayStation 4
(30, 4, 100),  -- PlayStation 5
(30, 5, 100),  -- Xbox One
(30, 6, 100),  -- Xbox Series S
(30, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(31, 1, 100),  -- Nintendo Switch
(31, 2, 100),  -- PC
(31, 3, 100),  -- PlayStation 4
(31, 4, 100),  -- PlayStation 5
(31, 5, 100),  -- Xbox One
(31, 6, 100),  -- Xbox Series S
(31, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(32, 1, 100),  -- Nintendo Switch
(32, 2, 100),  -- PC
(32, 3, 100),  -- PlayStation 4
(32, 4, 100),  -- PlayStation 5
(32, 5, 100),  -- Xbox One
(32, 6, 100),  -- Xbox Series S
(32, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(33, 1, 100),  -- Nintendo Switch
(33, 2, 100),  -- PC
(33, 3, 100),  -- PlayStation 4
(33, 4, 100),  -- PlayStation 5
(33, 5, 100),  -- Xbox One
(33, 6, 100),  -- Xbox Series S
(33, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(34, 1, 100),  -- Nintendo Switch
(34, 2, 100),  -- PC
(34, 3, 100),  -- PlayStation 4
(34, 4, 100),  -- PlayStation 5
(34, 5, 100),  -- Xbox One
(34, 6, 100),  -- Xbox Series S
(34, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(35, 1, 100),  -- Nintendo Switch
(35, 2, 100),  -- PC
(35, 3, 100),  -- PlayStation 4
(35, 4, 100),  -- PlayStation 5
(35, 5, 100),  -- Xbox One
(35, 6, 100),  -- Xbox Series S
(35, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(36, 1, 100),  -- Nintendo Switch
(36, 2, 100),  -- PC
(36, 3, 100),  -- PlayStation 4
(36, 4, 100),  -- PlayStation 5
(36, 5, 100),  -- Xbox One
(36, 6, 100),  -- Xbox Series S
(36, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(37, 1, 100),  -- Nintendo Switch
(37, 2, 100),  -- PC
(37, 3, 100),  -- PlayStation 4
(37, 4, 100),  -- PlayStation 5
(37, 5, 100),  -- Xbox One
(37, 6, 100),  -- Xbox Series S
(37, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(38, 1, 100),  -- Nintendo Switch
(38, 2, 100),  -- PC
(38, 3, 100),  -- PlayStation 4
(38, 4, 100),  -- PlayStation 5
(38, 5, 100),  -- Xbox One
(38, 6, 100),  -- Xbox Series S
(38, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(39, 1, 100),  -- Nintendo Switch
(39, 2, 100),  -- PC
(39, 3, 100),  -- PlayStation 4
(39, 4, 100),  -- PlayStation 5
(39, 5, 100),  -- Xbox One
(39, 6, 100),  -- Xbox Series S
(39, 7, 100);  -- Xbox Series X

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES
(40, 1, 100),  -- Nintendo Switch
(40, 2, 100),  -- PC
(40, 3, 100),  -- PlayStation 4
(40, 4, 100),  -- PlayStation 5
(40, 5, 100),  -- Xbox One
(40, 6, 100),  -- Xbox Series S
(40, 7, 100);  -- Xbox Series X

######################## CONSULTAS ########################

select * from carrito;
select * from carrito_item;
select * from favorito;
select * from favorito_item;
select * from genero;
select * from pedido;
select * from pedido_item;
select * from plataforma;
select * from producto;
select * from producto_genero;
select * from producto_plataforma;
select * from producto_stock;
select * from usuario;
select * from votos;
select * from facturacion;
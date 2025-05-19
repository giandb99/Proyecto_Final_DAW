drop database if exists tienda_online;
create database if not exists tienda_online;

use tienda_online;

######################## TABLAS ########################

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    username VARCHAR(50) UNIQUE NOT NULL,
    imagen_perfil VARCHAR(255) DEFAULT NULL,
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
    imagen VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
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
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON UPDATE CASCADE
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

CREATE TABLE facturacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pedido_id INT NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    numero_tarjeta VARCHAR(20) DEFAULT NULL,
    vencimiento_tarjeta VARCHAR(5) DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE ON UPDATE CASCADE
);

######################## INSERCIONES ########################

INSERT INTO usuario (nombre, username, pass, email, telefono, direccion, fecha_nac, cp, rol, fecha_creacion, ultimo_login, activo) VALUES
('Admin', 'admin123', 'adminpass', 'admin@email.com', '123456789', 'Calle Falsa 123', '1990-01-01', '28001', 'admin', '2023-01-10 09:00:00', '2025-05-10 12:00:00', 1),
('Juan Pérez', 'juanp', '1234', 'juan@demo.com', '222222222', 'Calle 2', '1992-02-02', '2000', 'user', '2024-03-15 10:00:00', '2025-05-15 08:00:00', 1),
('Ana López', 'anal', '1234', 'ana@demo.com', '333333333', 'Calle 3', '1993-03-03', '3000', 'user', '2024-01-20 11:00:00', '2025-04-20 09:00:00', 1),
('Carlos Ruiz', 'carlr', '1234', 'carlos@demo.com', '444444444', 'Calle 4', '1994-04-04', '4000', 'user', '2023-12-05 12:00:00', '2025-03-10 10:00:00', 0),
('Lucía Gómez', 'luciag', '1234', 'lucia@demo.com', '555555555', 'Calle 5', '1995-05-05', '5000', 'user', '2024-04-01 13:00:00', '2025-05-01 11:00:00', 1),
('Pedro Torres', 'pedrot', '1234', 'pedro@demo.com', '666666666', 'Calle 6', '1996-06-06', '6000', 'user', '2023-11-10 14:00:00', '2024-12-10 12:00:00', 0),
('María Díaz', 'mariad', '1234', 'maria@demo.com', '777777777', 'Calle 7', '1997-07-07', '7000', 'user', '2024-02-18 15:00:00', '2025-02-18 13:00:00', 1),
('Sofía Romero', 'sofiar', '1234', 'sofia@demo.com', '888888888', 'Calle 8', '1998-08-08', '8000', 'user', '2023-10-25 16:00:00', '2024-11-25 14:00:00', 1),
('Diego Castro', 'diegoc', '1234', 'diego@demo.com', '999999999', 'Calle 9', '1999-09-09', '9000', 'user', '2024-05-10 17:00:00', '2025-05-10 15:00:00', 1),
('Valentina Vera', 'valev', '1234', 'valentina@demo.com', '101010101', 'Calle 10', '2000-10-10', '10000', 'user', '2023-09-30 18:00:00', '2024-10-30 16:00:00', 0);

INSERT INTO genero (nombre) VALUES
('Acción'), ('Arcade'), ('Aventura'), ('Beat''em all'), ('Carreras'), ('Cloud Gaming'), ('Cooperación'), ('Cooperativo local'), 
('Cooperativo online'), ('Deporte'), ('Entrenamiento'), ('Estrategia'), ('FPS'), ('Gestión'), ('Indies'), ('Juegos para PS5'), 
('Lucha'), ('MMO'), ('Multijugador'), ('Multijugador multiplataforma'), ('Música'), ('Plataformas'), ('PvP online'), ('RPG'), 
('Shoot''em up'), ('Simulación'), ('Un solo jugador'), ('Wargame');

INSERT INTO plataforma (nombre) VALUES
('Nintendo Switch'),('PC'),('PlayStation 4'),('PlayStation 5'),('Xbox One'),('Xbox Series S'),('Xbox Series X');

INSERT INTO producto (nombre, imagen, descripcion, fecha_lanzamiento, precio, descuento, creado_por) VALUES
('Elden Ring', 'default.jpg', 'Elden Ring es un juego de rol de acción en mundo abierto creado por FromSoftware. Explora las Tierras Intermedias, enfréntate a jefes desafiantes y descubre una historia épica llena de misterio y fantasía oscura.', '2022-02-25', 59.99, NULL, 1),
('Baldur''s Gate 3', 'default.jpg', 'Baldur''s Gate 3 es un RPG basado en Dungeons & Dragons. Forma tu grupo, toma decisiones que afectan el mundo y vive una aventura de fantasía con combates tácticos por turnos y una narrativa profunda.', '2023-08-03', 69.99, 20, 1),
('Cyberpunk 2077', 'default.jpg', 'Cyberpunk 2077 es un RPG de acción futurista ambientado en Night City. Personaliza a tu personaje, mejora tus habilidades cibernéticas y sumérgete en una historia adulta llena de elecciones y consecuencias.', '2020-12-10', 39.99, 10, 1),
('Diablo IV', 'default.jpg', 'Diablo IV es la nueva entrega de la legendaria saga de rol y acción. Explora un mundo oscuro y abierto, enfréntate a hordas de demonios y personaliza a tu héroe en una experiencia cooperativa o en solitario.', '2023-06-06', 69.99, NULL, 1),
('Red Dead Redemption 2', 'default.jpg', 'Red Dead Redemption 2 es una aventura de acción en el Viejo Oeste. Vive la historia de Arthur Morgan y la banda de Van der Linde en un mundo abierto lleno de detalles, misiones y paisajes impresionantes.', '2018-10-26', 29.99, 15, 1),
('Doom Eternal', 'default.jpg', 'Doom Eternal es un shooter en primera persona frenético y brutal donde encarnas al Doom Slayer. Enfréntate a hordas de demonios con un arsenal devastador, explora escenarios infernales y vive una experiencia de acción intensa y desafiante.', '2020-03-20', 24.99, NULL, 1),
('Resident Evil 4 Remake', 'default.jpg', 'Resident Evil 4 Remake reinventa el clásico survival horror con gráficos modernos y jugabilidad mejorada. Acompaña a Leon S. Kennedy en su misión de rescate en un pueblo aterrador, enfrentando enemigos y resolviendo acertijos en una atmósfera tensa.', '2023-03-24', 59.99, 5, 1),
('Hogwarts Legacy', 'default.jpg', 'Hogwarts Legacy es un RPG de acción ambientado en el universo de Harry Potter. Explora el castillo de Hogwarts y sus alrededores, aprende magia, crea pociones y vive tu propia aventura como estudiante en el siglo XIX.', '2023-02-10', 69.99, 10, 1),
('Alan Wake 2', 'default.jpg', 'Alan Wake 2 es un juego de terror psicológico y suspenso en tercera persona. Sumérgete en una historia oscura y misteriosa, enfrentando horrores sobrenaturales mientras buscas la verdad en un mundo inquietante y atmosférico.', '2023-10-27', 69.99, NULL, 1),
('Forza Horizon 5', 'default.jpg', 'Forza Horizon 5 te lleva a recorrer México en un mundo abierto vibrante y lleno de carreras. Disfruta de una gran variedad de coches, eventos y paisajes espectaculares en uno de los mejores juegos de conducción de la generación.', '2021-11-09', 49.99, NULL, 1),
('Ghost of Tsushima', 'default.jpg', 'Ghost of Tsushima es una aventura de acción en mundo abierto ambientada en el Japón feudal. Ponte en la piel de Jin Sakai, un samurái que debe adaptarse y aprender nuevas tácticas para liberar su tierra de la invasión mongola. Explora paisajes impresionantes, participa en duelos épicos y vive una historia cargada de honor y sacrificio.', '2020-07-17', 49.99, 10, 1),
('Counter Strike 2', 'default.jpg', 'Counter Strike 2 es la evolución del legendario shooter competitivo por equipos. Participa en intensos combates tácticos, domina armas y estrategias, y compite en partidas rápidas y emocionantes contra jugadores de todo el mundo.', '2023-09-27', 19.99, NULL, 1),
('Starfield', 'default.jpg', 'Starfield es un RPG de exploración espacial desarrollado por Bethesda. Viaja a través de la galaxia, descubre planetas únicos, personaliza tu nave y vive una aventura épica llena de misterios, facciones y decisiones que marcarán tu destino entre las estrellas.', '2023-09-06', 69.99, 5, 1),
('Palworld', 'default.jpg', 'Palworld combina supervivencia, exploración y captura de criaturas en un mundo abierto lleno de sorpresas. Cría y entrena a tus Pals, construye bases, enfréntate a enemigos y descubre secretos en una experiencia única que mezcla acción y estrategia.', '2024-01-19', 29.99, NULL, 1),
('Assassin’s Creed Mirage', 'default.jpg', 'Assassin’s Creed Mirage te transporta al Bagdad del siglo IX, donde la acción y el sigilo se combinan en una historia de orígenes. Domina el parkour, elimina objetivos con precisión y sumérgete en una ciudad vibrante llena de secretos y desafíos.', '2023-10-05', 49.99, 10, 1),
('Sons of the Forest', 'default.jpg', 'Sons of the Forest es un survival horror cooperativo en el que deberás sobrevivir en una isla misteriosa llena de peligros. Construye refugios, explora cuevas, enfréntate a criaturas aterradoras y descubre la verdad detrás de este entorno hostil.', '2023-02-23', 29.99, NULL, 1),
('Destiny 2: Lightfall', 'default.jpg', 'Destiny 2: Lightfall es una expansión del shooter espacial de Bungie. Enfréntate a nuevas amenazas, explora la ciudad de Neomuna en Neptuno, desbloquea poderes inéditos y únete a otros guardianes para salvar la galaxia en intensas misiones cooperativas.', '2023-02-28', 39.99, NULL, 1),
('Lies of P', 'default.jpg', 'Lies of P es un desafiante soulslike inspirado en el cuento de Pinocho. Explora una ciudad oscura y decadente, enfréntate a enemigos implacables y toma decisiones morales que afectarán el desarrollo de la historia y el destino de tu personaje.', '2023-09-19', 59.99, 15, 1),
('Hades II', 'default.jpg', 'Hades II es un roguelike de acción mitológica donde te embarcas en una nueva aventura en el inframundo. Enfréntate a dioses y criaturas legendarias, mejora tus habilidades y descubre una narrativa profunda con cada intento de escape.', '2024-04-17', 24.99, NULL, 1),
('Ghostrunner 2', 'default.jpg', 'Ghostrunner 2 es un intenso juego de acción en primera persona que combina parkour cibernético y combates rápidos. Enfréntate a enemigos letales, supera desafiantes plataformas y explora un mundo futurista lleno de peligros y secretos.', '2023-10-26', 39.99, 10, 1),
('The Legend of Zelda: Tears of the Kingdom', 'default.jpg', 'La esperada secuela de Breath of the Wild te invita a explorar un Hyrule renovado, lleno de misterios, desafíos y nuevas mecánicas. Descubre secretos ancestrales, resuelve puzles y vive una aventura épica junto a Link.', '2023-05-12', 69.99, NULL, 1),
('Spider-Man: Miles Morales', 'default.jpg', 'Ponte en la piel de Miles Morales y recorre una Nueva York nevada con nuevos poderes arácnidos. Enfréntate a enemigos formidables, protege tu barrio y descubre qué significa ser un verdadero Spider-Man.', '2020-11-12', 49.99, 10, 1),
('Final Fantasy XVI', 'default.jpg', 'Sumérgete en una nueva entrega de la legendaria saga Final Fantasy. Vive una historia épica de fantasía oscura, combates espectaculares y personajes inolvidables en un mundo lleno de magia y conflictos.', '2023-06-22', 69.99, 15, 1),
('FIFA 24', 'default.jpg', 'FIFA 24 ofrece la experiencia de fútbol más realista y competitiva hasta la fecha. Disfruta de modos de juego renovados, gráficos mejorados y compite con los mejores equipos y jugadores del mundo.', '2023-09-29', 69.99, NULL, 1),
('NBA 2K24', 'default.jpg', 'NBA 2K24 lleva el baloncesto virtual a un nuevo nivel con gráficos de última generación, modos de juego innovadores y una jugabilidad pulida. Vive la emoción de la NBA y crea tu propia leyenda en la cancha.', '2023-09-08', 69.99, NULL, 1),
('Mortal Kombat 1', 'default.jpg', 'Mortal Kombat 1 reinventa la legendaria saga de lucha con nuevos personajes, gráficos espectaculares y combates aún más brutales. Descubre una historia renovada y desafía a tus amigos en intensos duelos llenos de acción y fatalities.', '2023-09-19', 69.99, 5, 1),
('Super Mario Wonder', 'default.jpg', 'Super Mario Wonder trae de vuelta la magia de las plataformas clásicas con nuevos poderes, mundos sorprendentes y desafíos para toda la familia. Acompaña a Mario y sus amigos en una aventura colorida y llena de sorpresas.', '2023-10-20', 59.99, NULL, 1),
('Mario Kart 8 Deluxe', 'default.jpg', 'Mario Kart 8 Deluxe es la experiencia definitiva de carreras con Mario y compañía. Compite en circuitos alocados, usa objetos para tomar ventaja y disfruta de frenéticas partidas multijugador tanto local como online.', '2017-04-28', 59.99, 20, 1),
('God of War Ragnarök', 'default.jpg', 'God of War Ragnarök continúa la épica historia de Kratos y Atreus en los mitos nórdicos. Enfréntate a dioses y monstruos en combates intensos, explora paisajes impresionantes y vive una narrativa profunda y emocionante.', '2022-11-09', 69.99, NULL, 1),
('The Last of Us Part I', 'default.jpg', 'The Last of Us Part I es una aventura de supervivencia post-apocalíptica donde Joel y Ellie luchan por sobrevivir en un mundo devastado. Disfruta de una historia emotiva, gráficos mejorados y jugabilidad renovada.', '2022-09-02', 69.99, 10, 1),
('Dead Space Remake', 'default.jpg', 'Dead Space Remake revive el clásico survival horror espacial con gráficos modernos y atmósfera aterradora. Acompaña a Isaac Clarke en su lucha por sobrevivir a bordo de la USG Ishimura enfrentando horrores indescriptibles.', '2023-01-27', 59.99, NULL, 1),
('Pikmin 4', 'default.jpg', 'Pikmin 4 es un juego de estrategia y aventura donde lideras a pequeñas criaturas llamadas Pikmin en un mundo lleno de desafíos y misterios. Explora escenarios coloridos, resuelve acertijos y utiliza las habilidades únicas de cada Pikmin para superar obstáculos y enemigos.', '2023-07-21', 59.99, NULL, 1),
('Metroid Dread', 'default.jpg', 'Metroid Dread es una aventura de acción y exploración en 2D protagonizada por Samus Aran. Descubre secretos, enfréntate a enemigos letales y recorre laberintos alienígenas en una experiencia intensa y atmosférica que retoma la esencia clásica de la saga.', '2021-10-08', 59.99, NULL, 1),
('Halo Infinite', 'default.jpg', 'Halo Infinite renueva el icónico shooter en primera persona con una campaña épica y un multijugador competitivo. Ponte en la armadura del Jefe Maestro y enfréntate a nuevos enemigos en vastos escenarios, disfrutando de una jugabilidad fluida y moderna.', '2021-12-08', 59.99, 15, 1),
('Sea of Thieves', 'default.jpg', 'Sea of Thieves es una aventura pirata online en mundo abierto donde puedes explorar islas, buscar tesoros y enfrentarte a otros jugadores. Forma tu tripulación, navega por mares peligrosos y vive historias únicas en un entorno lleno de humor y acción.', '2018-03-20', 39.99, NULL, 1),
('Call of Duty: Modern Warfare III', 'default.jpg', 'Call of Duty: Modern Warfare III es un shooter militar de acción que ofrece intensas batallas, modos multijugador competitivos y una campaña cinematográfica. Experimenta combates realistas y estrategias modernas en escenarios globales.', '2023-11-10', 69.99, NULL, 1),
('It Takes Two', 'default.jpg', 'It Takes Two es un juego cooperativo de aventuras donde dos jugadores deben colaborar para superar desafíos creativos y variados. Vive una historia emotiva y divertida, llena de mecánicas originales y escenarios sorprendentes.', '2021-03-26', 39.99, 20, 1),
('Ratchet & Clank: Rift Apart', 'default.jpg', 'Ratchet & Clank: Rift Apart es una aventura de plataformas futurista con acción trepidante y gráficos espectaculares. Viaja entre dimensiones, utiliza armas extravagantes y acompaña a Ratchet y Clank en una misión para salvar el multiverso.', '2021-06-11', 69.99, 10, 1),
('Horizon Forbidden West', 'default.jpg', 'Horizon Forbidden West es un mundo abierto post-apocalíptico donde Aloy explora tierras misteriosas, enfrenta máquinas colosales y descubre secretos ancestrales. Disfruta de una narrativa profunda y paisajes impresionantes.', '2022-02-18', 69.99, NULL, 1),
('Returnal', 'default.jpg', 'Returnal es un juego de acción roguelike en bucle donde Selene, una astronauta, queda atrapada en un planeta alienígena hostil. Cada muerte reinicia el ciclo, cambiando el mundo y obligándote a adaptarte para sobrevivir y descubrir la verdad.', '2021-04-30', 59.99, 20, 1);

INSERT INTO producto_genero (producto_id, genero_id) VALUES
(1,1),(1,3),(1,18),(1,23),(1,26),
(2,3),(2,23),(2,26),(2,18),
(3,1),(3,3),(3,13),(3,18),(3,23),(3,26),
(4,1),(4,23),(4,18),(4,26),
(5,1),(5,3),(5,18),(5,26),
(6,1),(6,13),(6,18),(6,26),
(7,1),(7,3),(7,26),
(8,3),(8,23),(8,26),
(9,3),(9,26),
(10,5),(10,18),(10,19),
(11,1),(11,3),(11,26),
(12,1),(12,13),(12,18),(12,19),(12,22),
(13,3),(13,23),(13,26),
(14,1),(14,3),(14,23),(14,25),(14,26),
(15,1),(15,3),(15,26),
(16,1),(16,3),(16,7),(16,8),(16,18),(16,26),
(17,1),(17,13),(17,18),(17,19),(17,23),
(18,1),(18,3),(18,23),(18,26),
(19,1),(19,23),(19,26),
(20,1),(20,13),(20,18),(20,26),
(21,3),(21,18),(21,26),
(22,1),(22,3),(22,18),(22,26),
(23,3),(23,23),(23,26),
(24,10),(24,18),(24,19),
(25,10),(25,18),(25,19),
(26,1),(26,17),(26,18),(26,22),
(27,2),(27,21),(27,26),(27,18),
(28,2),(28,5),(28,18),(28,19),(28,21),
(29,1),(29,3),(29,26),
(30,1),(30,3),(30,26),
(31,1),(31,3),(31,26),
(32,1),(32,11),(32,12),(32,25),(32,26),
(33,1),(33,3),(33,26),
(34,1),(34,13),(34,18),(34,19),(34,22),
(35,1),(35,3),(35,7),(35,8),(35,18),(35,19),
(36,1),(36,13),(36,18),(36,19),(36,22),
(37,3),(37,7),(37,8),(37,18),
(38,1),(38,3),(38,21),(38,26),
(39,1),(39,3),(39,26),
(40,1),(40,26);

INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES
(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),
(2,2),(2,4),(2,7),
(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),
(4,2),(4,4),(4,5),(4,6),(4,7),
(5,2),(5,3),(5,4),(5,5),(5,7),
(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),
(7,2),(7,4),(7,7),
(8,1),(8,2),(8,3),(8,4),(8,5),(8,7),
(9,2),(9,4),(9,7),
(10,2),(10,6),(10,7),
(11,3),(11,4),
(12,2),
(13,2),(13,6),(13,7),
(14,2),(14,6),(14,7),
(15,2),(15,3),(15,4),(15,5),(15,6),(15,7),
(16,2),
(17,2),(17,3),(17,4),(17,5),(17,6),(17,7),
(18,2),(18,4),(18,5),(18,6),(18,7),
(19,2),
(20,2),(20,4),(20,7),
(21,1),
(22,3),(22,4),
(23,4),
(24,1),(24,2),(24,3),(24,4),(24,5),(24,6),(24,7),
(25,1),(25,2),(25,3),(25,4),(25,5),(25,6),(25,7),
(26,2),(26,4),(26,5),(26,6),(26,7),
(27,1),
(28,1),
(29,4),
(30,2),(30,4),
(31,2),(31,4),(31,7),
(32,1),
(33,1),
(34,2),(34,6),(34,7),
(35,2),(35,6),(35,7),
(36,2),(36,3),(36,4),(36,5),(36,6),(36,7),
(37,1),(37,2),(37,3),(37,4),(37,5),(37,6),(37,7),
(38,4),
(39,4),
(40,2),(40,4);

INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible, stock_reservado) VALUES
(1,2,10,0),(1,3,8,0),(1,4,5,0),(1,5,7,0),(1,6,6,0),(1,7,4,0),
(2,2,20,0),(2,4,15,0),(2,7,10,0),
(3,2,12,0),(3,3,10,0),(3,4,8,0),(3,5,6,0),(3,6,5,0),(3,7,4,0),
(4,2,15,0),(4,4,10,0),(4,5,8,0),(4,6,7,0),(4,7,5,0),
(5,2,18,0),(5,3,12,0),(5,4,10,0),(5,5,8,0),(5,7,6,0),
(6,1,20,0),(6,2,15,0),(6,3,10,0),(6,4,8,0),(6,5,6,0),(6,6,5,0),(6,7,4,0),
(7,2,14,0),(7,4,10,0),(7,7,8,0),
(8,1,20,0),(8,2,15,0),(8,3,12,0),(8,4,10,0),(8,5,8,0),(8,7,6,0),
(9,2,15,0),(9,4,10,0),(9,7,8,0),
(10,2,18,0),(10,6,12,0),(10,7,10,0),
(11,3,10,0),(11,4,8,0),
(12,2,7,0),
(13,2,15,0),(13,6,10,0),(13,7,8,0),
(14,2,14,0),(14,6,10,0),(14,7,8,0),
(15,2,18,0),(15,3,14,0),(15,4,12,0),(15,5,10,0),(15,6,8,0),(15,7,6,0),
(16,2,7,0),
(17,2,15,0),(17,3,12,0),(17,4,10,0),(17,5,8,0),(17,6,7,0),(17,7,5,0),
(18,2,14,0),(18,4,10,0),(18,5,8,0),(18,6,7,0),(18,7,5,0),
(19,2,10,0),
(20,2,8,0),(20,4,6,0),(20,7,4,0),
(21,1,10,0),
(22,3,8,0),(22,4,6,0),
(23,4,5,0),
(24,1,10,0),(24,2,9,0),(24,3,8,0),(24,4,7,0),(24,5,6,0),(24,6,5,0),(24,7,4,0),
(25,1,10,0),(25,2,9,0),(25,3,8,0),(25,4,7,0),(25,5,6,0),(25,6,5,0),(25,7,4,0),
(26,2,8,0),(26,4,7,0),(26,5,6,0),(26,6,5,0),(26,7,4,0),
(27,1,10,0),
(28,1,10,0),
(29,4,5,0),
(30,2,8,0),(30,4,6,0),
(31,2,7,0),(31,4,5,0),(31,7,3,0),
(32,1,10,0),
(33,1,10,0),
(34,2,8,0),(34,6,6,0),(34,7,4,0),
(35,2,8,0),(35,6,6,0),(35,7,4,0),
(36,2,7,0),(36,3,6,0),(36,4,5,0),(36,5,4,0),(36,6,3,0),(36,7,2,0),
(37,1,10,0),(37,2,9,0),(37,3,8,0),(37,4,7,0),(37,5,6,0),(37,6,5,0),(37,7,4,0),
(38,4,5,0),
(39,4,5,0),
(40,2,7,0),(40,4,5,0);

INSERT INTO pedido (usuario_id, precio_total, descuento, estado, creado_por, creado_en, activo, fecha_envio) VALUES
(2, 59.99, 10, 'entregado', 1, '2025-05-10 10:00:00', 1, '2025-05-12 10:00:00'),
(3, 79.99, 0, 'entregado', 2, '2025-05-15 10:00:00', 1, '2025-05-16 10:00:00'),
(4, 49.99, 5, 'cancelado', 3, '2025-04-15 11:00:00', 1, NULL),
(5, 24.99, 0, 'pendiente', 4, '2025-03-20 12:00:00', 1, NULL),
(6, 69.99, 15, 'entregado', 5, '2025-02-25 13:00:00', 1, '2025-02-27 13:00:00'),
(7, 49.99, 0, 'cancelado', 6, '2025-01-30 14:00:00', 1, NULL),
(8, 59.99, 5, 'entregado', 7, '2024-12-05 15:00:00', 1, '2024-12-07 15:00:00'),
(9, 69.99, 10, 'entregado', 8, '2024-11-10 16:00:00', 1, '2024-11-12 16:00:00'),
(10, 29.99, 0, 'entregado', 9, '2024-10-15 17:00:00', 1, '2024-10-17 17:00:00'),
(3, 39.99, 5, 'entregado', 2, '2024-09-15 11:00:00', 1, '2024-09-17 11:00:00'),
(4, 24.99, 0, 'pendiente', 3, '2024-08-20 12:00:00', 1, NULL),
(5, 69.99, 15, 'entregado', 4, '2024-07-25 13:00:00', 1, '2024-07-27 13:00:00'),
(6, 49.99, 0, 'cancelado', 5, '2024-06-30 14:00:00', 1, NULL),
(7, 59.99, 5, 'entregado', 6, '2024-05-05 15:00:00', 1, '2024-05-07 15:00:00'),
(8, 69.99, 10, 'entregado', 7, '2024-04-10 16:00:00', 1, '2024-04-12 16:00:00'),
(9, 29.99, 0, 'entregado', 8, '2024-03-15 17:00:00', 1, '2024-03-17 17:00:00');

INSERT INTO pedido_item (pedido_id, producto_id, plataforma_id, cantidad, precio_total) VALUES
(1,1,2,1,59.99),
(2,2,2,1,79.99),
(3,3,5,1,49.99),
(4,4,2,1,24.99),
(5,5,2,2,69.99),
(6,6,2,1,49.99),
(7,7,2,1,59.99),
(8,8,2,1,69.99),
(9,9,2,1,29.99),
(10,10,3,1,39.99),
(11,11,2,1,24.99),
(12,12,2,1,69.99),
(13,13,1,1,49.99),
(14,14,2,1,59.99),
(15,15,3,1,69.99),
(16,16,4,1,59.99);

INSERT INTO carrito (creado_por, creado_en, actualizado_en, activo) VALUES
(2, '2025-05-10 10:00:00', '2025-05-10 10:00:00', 1),
(3, '2025-05-15 10:00:00', '2025-05-15 10:00:00', 1),
(4, '2025-04-15 11:00:00', '2025-04-15 11:00:00', 1),
(5, '2025-03-20 12:00:00', '2025-03-20 12:00:00', 1),
(6, '2025-02-25 13:00:00', '2025-02-25 13:00:00', 1),
(7, '2025-01-30 14:00:00', '2025-01-30 14:00:00', 1),
(8, '2024-12-05 15:00:00', '2024-12-05 15:00:00', 1),
(9, '2024-11-10 16:00:00', '2024-11-10 16:00:00', 1),
(10, '2024-10-15 17:00:00', '2024-10-15 17:00:00', 1);

INSERT INTO carrito_item (carrito_id, producto_id, cantidad, plataforma_id, precio_total) VALUES
(1,1,1,2,59.99),
(3,2,1,2,79.99),
(1,3,1,5,49.99),
(2,4,1,2,24.99),
(3,5,2,2,69.99),
(3,6,1,2,49.99),
(4,7,1,2,59.99),
(5,8,1,2,69.99),
(6,9,1,2,29.99),
(7,10,1,3,39.99),
(8,11,1,2,24.99),
(9,12,1,2,69.99),
(8,13,1,1,49.99);

INSERT INTO favorito (creado_por, creado_en, actualizado_en, activo) VALUES
(2, '2025-05-10 10:00:00', '2025-05-10 10:00:00', 1),
(3, '2025-05-15 10:00:00', '2025-05-15 10:00:00', 1),
(4, '2025-04-15 11:00:00', '2025-04-15 11:00:00', 1),
(5, '2025-03-20 12:00:00', '2025-03-20 12:00:00', 1),
(6, '2024-01-30 14:00:00', '2024-01-30 14:00:00', 1),
(7, '2023-12-05 15:00:00', '2023-12-05 15:00:00', 1),
(8, '2023-11-10 16:00:00', '2023-11-10 16:00:00', 1),
(9, '2023-10-15 17:00:00', '2023-10-15 17:00:00', 1),
(10, '2023-09-20 18:00:00', '2023-09-20 18:00:00', 1);

INSERT INTO favorito_item (favorito_id, producto_id) VALUES
(1,1),(1,2),(2,3),(2,4),(3,5),(3,6),(4,7),(4,8),
(5,9),(5,10),(6,11),(6,12),(7,13),(7,14),(8,15),(8,16),
(9,17),(9,18),(4,19),(8,20);

INSERT INTO facturacion (usuario_id, pedido_id, nombre_completo, email, direccion, pais, numero_tarjeta, vencimiento_tarjeta, fecha_creacion) VALUES
(2,1,'Juan Pérez','juan@demo.com','Calle 2','España','1234567890123456','12/25','2025-05-10 10:00:00'),
(3,2,'Ana López','ana@demo.com','Calle 3','España','2345678901234567','11/26','2025-05-15 10:00:00'),
(4,3,'Carlos Ruiz','carlos@demo.com','Calle 4','España','3456789012345678','10/27','2025-03-20 12:00:00'),
(5,4,'Lucía Gómez','lucia@demo.com','Calle 5','España','4567890123456789','09/28','2025-02-25 13:00:00'),
(6,5,'Pedro Torres','pedro@demo.com','Calle 6','España','5678901234567890','08/29','2024-12-05 15:00:00'),
(7,6,'María Díaz','maria@demo.com','Calle 7','España','6789012345678901','07/30','2024-11-10 16:00:00'),
(8,7,'Sofía Romero','sofia@demo.com','Calle 8','España','7890123456789012','06/31','2024-10-15 17:00:00'),
(3,8,'Ana López','ana@demo.com','Calle 3','España','2345678901234567','11/26','2024-07-25 13:00:00'),
(4,9,'Carlos Ruiz','carlos@demo.com','Calle 4','España','3456789012345678','10/27','2025-03-20 12:00:00'),
(5,10,'Lucía Gómez','lucia@demo.com','Calle 5','España','4567890123456789','09/28','2025-02-25 13:00:00'),
(6,11,'Pedro Torres','pedro@demo.com','Calle 6','España','5678901234567890','08/29','2024-12-05 15:00:00'),
(7,12,'María Díaz','maria@demo.com','Calle 7','España','6789012345678901','07/30','2024-11-10 16:00:00'),
(8,13,'Sofía Romero','sofia@demo.com','Calle 8','España','7890123456789012','06/31','2024-10-15 17:00:00'),
(3,14,'Ana López','ana@demo.com','Calle 3','España','2345678901234567','11/26','2024-07-25 13:00:00'),
(6,15,'Pedro Torres','pedro@demo.com','Calle 6','España','5678901234567890','08/29','2024-12-05 15:00:00'),
(7,16,'María Díaz','maria@demo.com','Calle 7','España','6789012345678901','07/30','2024-11-10 16:00:00');

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
select * from facturacion;
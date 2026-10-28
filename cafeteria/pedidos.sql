CREATE DATABASE IF NOT EXISTS cafeteria
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cafeteria;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CHECK (precio >= 0)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente VARCHAR(150) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM(
        'Pendiente',
        'En preparación',
        'Listo',
        'Entregado',
        'Cancelado'
    ) NOT NULL DEFAULT 'Pendiente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NULL,
    producto_nombre VARCHAR(150) NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id)
        ON DELETE CASCADE,

    FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON DELETE SET NULL,

    CHECK (cantidad > 0),
    CHECK (precio >= 0),
    CHECK (subtotal >= 0)
);

INSERT INTO productos (nombre, precio) VALUES
('Café americano', 5.00),
('Café con leche', 7.00),
('Capuchino', 8.00),
('Sándwich', 10.00),
('Jugo de naranja', 6.00);

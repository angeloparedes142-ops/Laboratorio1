CREATE DATABASE IF NOT EXISTS cafeteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafeteria;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CHECK (precio >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente VARCHAR(150) NOT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente','En preparación','Listo','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NULL,
    producto_nombre VARCHAR(150) NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL,
    CHECK (cantidad > 0), CHECK (precio >= 0), CHECK (subtotal >= 0)
) ENGINE=InnoDB;

INSERT INTO productos (nombre, precio) SELECT 'Café americano', 5.00 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Café americano');
INSERT INTO productos (nombre, precio) SELECT 'Café con leche', 7.00 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Café con leche');
INSERT INTO productos (nombre, precio) SELECT 'Capuchino', 8.00 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Capuchino');
INSERT INTO productos (nombre, precio) SELECT 'Sándwich', 10.00 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Sándwich');
INSERT INTO productos (nombre, precio) SELECT 'Jugo de naranja', 6.00 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Jugo de naranja');

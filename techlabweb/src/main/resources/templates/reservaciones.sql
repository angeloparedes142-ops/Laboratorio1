CREATE DATABASE IF NOT EXISTS cafeteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafeteria;

CREATE TABLE IF NOT EXISTS reservas (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(255) NOT NULL,
    fecha VARCHAR(255) NOT NULL,
    hora VARCHAR(255) NOT NULL,
    personas INT NOT NULL,
    ocasion VARCHAR(255),
    comentario VARCHAR(500),
    CHECK (personas >= 1 AND personas <= 20)
) ENGINE=InnoDB;

INSERT INTO reservas (nombre, telefono, fecha, hora, personas, ocasion, comentario)
SELECT 'Ana Torres', '987654321', '2026-11-05', '18:00', 4, 'Cumpleaños', 'Mesa cerca de la ventana'
WHERE NOT EXISTS (SELECT 1 FROM reservas WHERE nombre = 'Ana Torres' AND fecha = '2026-11-05');

INSERT INTO reservas (nombre, telefono, fecha, hora, personas, ocasion, comentario)
SELECT 'Luis Pérez', '912345678', '2026-11-06', '13:30', 2, 'Reunión de trabajo', ''
WHERE NOT EXISTS (SELECT 1 FROM reservas WHERE nombre = 'Luis Pérez' AND fecha = '2026-11-06');
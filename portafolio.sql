
-- =============================================
-- Portafolio de presentacion
-- Base de datos: portafolio
-- =============================================

CREATE DATABASE IF NOT EXISTS portafolio;

USE portafolio;

CREATE TABLE IF NOT EXISTS contactos (
    id             INT AUTO_INCREMENT,
    nombre         VARCHAR(100) NOT NULL,
    correo         VARCHAR(100) NOT NULL,
    mensaje        TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

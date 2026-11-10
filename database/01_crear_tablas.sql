-- Proyecto: Sistema IoT de monitoreo de calidad del agua
-- Universidad: UNAD
-- Script 01: Creacion de tablas
-- Motor: MySQL / MariaDB

-- Tabla de dispositivos
CREATE TABLE dispositivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(150),
    estado VARCHAR(20) NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

-- Tabla de mediciones
CREATE TABLE mediciones (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dispositivo_id INT NOT NULL,
    ph DECIMAL(5,2) NOT NULL,
    turbidez DECIMAL(8,2) NOT NULL,
    temperatura DECIMAL(5,2) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mediciones_dispositivos
        FOREIGN KEY (dispositivo_id)
        REFERENCES dispositivos(id),

    INDEX idx_mediciones_fecha (fecha)
) ENGINE=InnoDB;

-- Tabla de alertas
CREATE TABLE alertas (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    medicion_id BIGINT NOT NULL,
    parametro VARCHAR(30) NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_alertas_mediciones
        FOREIGN KEY (medicion_id)
        REFERENCES mediciones(id)
) ENGINE=InnoDB;

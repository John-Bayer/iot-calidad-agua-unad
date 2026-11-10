-- Proyecto: Sistema IoT de monitoreo de calidad del agua
-- Universidad: UNAD
-- Script 02: Registro inicial de dispositivos

INSERT INTO dispositivos (nombre, ubicacion, estado)
SELECT 'ESP32-001', 'Prototipo experimental UNAD', 'activo'
WHERE NOT EXISTS (
    SELECT 1
    FROM dispositivos
    WHERE nombre = 'ESP32-001'
);

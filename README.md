# Sistema IoT para el monitoreo de la calidad del agua

**Proyecto de grado – Ingeniería de Sistemas**

**Universidad Nacional Abierta y a Distancia (UNAD)**

## Descripción del proyecto

Este proyecto tiene como objetivo desarrollar y validar experimentalmente un sistema IoT para el monitoreo de parámetros fisicoquímicos de la calidad del agua en fuentes de abastecimiento de comunidades rurales.

La solución integra un microcontrolador ESP32, sensores de medición, comunicación mediante HTTP, almacenamiento en una base de datos MySQL y una plataforma web para consultar la información recopilada.

El desarrollo contempla un enfoque híbrido que combina simulación tecnológica y validación experimental de un prototipo.

## Tecnologías utilizadas

- **ESP32:** microcontrolador encargado de procesar y transmitir las mediciones.
- **Wokwi:** plataforma de simulación del funcionamiento del ESP32.
- **Arduino/C++:** programación del microcontrolador.
- **PHP:** desarrollo de la API para recibir las mediciones.
- **MySQL:** almacenamiento de los datos.
- **Apache:** servidor web.
- **HTML, CSS y JavaScript:** tecnologías previstas para la interfaz de visualización.
- **GitHub:** control de versiones y documentación del proyecto.

## Arquitectura del sistema

El funcionamiento general se basa en el siguiente flujo:

ESP32 (Wokwi) → HTTP POST → API PHP → Base de datos MySQL → Plataforma web

El ESP32 genera mediciones simuladas de calidad del agua y las transmite al servidor mediante solicitudes HTTP. La API recibe los datos, realiza su procesamiento y los almacena en MySQL para su posterior consulta.

## Parámetros monitoreados

El sistema contempla tres parámetros principales:

| Parámetro | Unidad |
|---|---|
| pH | Escala de pH |
| Turbidez | NTU |
| Temperatura | °C |

Estos parámetros permiten realizar una evaluación preliminar de las condiciones del agua monitoreada.

## Estado actual del desarrollo

**Funcionalidades implementadas:**

- Configuración del ESP32 en Wokwi.
- Conexión a la red Wi-Fi simulada.
- Generación y transmisión de mediciones simuladas.
- Comunicación HTTP mediante solicitudes POST.
- Implementación de una API en PHP.
- Conexión con la base de datos MySQL.
- Recepción y almacenamiento exitoso de mediciones.

**Funcionalidades en desarrollo:**

- Interfaz web de visualización de mediciones.
- Sistema de alertas por parámetros fuera de los rangos establecidos.
- Pruebas de funcionamiento y validación experimental.
- Documentación de resultados y evidencias.

## Validación del prototipo

La validación contempla pruebas de comunicación, recepción, almacenamiento y visualización de mediciones.

También se evaluará el comportamiento del sistema ante diferentes valores simulados de los parámetros de calidad del agua.

Los resultados permitirán documentar el funcionamiento del prototipo y sus limitaciones dentro del alcance académico del proyecto.

## Repositorio

Este repositorio contiene el código fuente, los componentes de software y la documentación técnica desarrollada durante la implementación del sistema IoT.

**Universidad Nacional Abierta y a Distancia – UNAD**

**Programa de Ingeniería de Sistemas**

**Proyecto de grado – 2026**

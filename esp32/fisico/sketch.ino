
#include <WiFi.h>
#include <HTTPClient.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// ======================================
// CONFIGURACION WIFI Y SERVIDOR
// ======================================

const char* WIFI_SSID = "Bayer";
const char* WIFI_PASSWORD = "*********";

const char* API_URL =
    "http://10.254.200.2/iot-calidad-agua-unad/api/recibir_medicion.php";

const int DISPOSITIVO_ID = 2;

// ======================================
// CONFIGURACION DE PINES
// ======================================

const int PIN_TEMPERATURA = 4;
const int PIN_PH = 34;
const int PIN_TURBIDEZ = 35;
const int PIN_LED = 2;

// ======================================
// SENSOR DS18B20
// ======================================

OneWire oneWire(PIN_TEMPERATURA);
DallasTemperature sensors(&oneWire);

// ======================================
// TEMPORIZACION
// ======================================

const unsigned long INTERVALO = 5000;
unsigned long ultimaTransmision = 0;
unsigned long ultimoIntentoWiFi = 0;

const unsigned long REINTENTO_WIFI = 10000;

// ======================================
// LED DE ESTADO
// ======================================

void actualizarLED() {
    digitalWrite(
        PIN_LED,
        WiFi.status() == WL_CONNECTED ? HIGH : LOW
    );
}

void destelloTransmision() {
    digitalWrite(PIN_LED, LOW);
    delay(150);
    actualizarLED();
}

// ======================================
// CONEXION WIFI
// ======================================

void conectarWiFi() {
    if (WiFi.status() == WL_CONNECTED) return;

    Serial.println("Conectando a WiFi Bayer...");

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    ultimoIntentoWiFi = millis();
}

// ======================================
// INICIALIZACION
// ======================================

void setup() {
    Serial.begin(115200);

    pinMode(PIN_LED, OUTPUT);
    digitalWrite(PIN_LED, LOW);

    analogReadResolution(12);

    analogSetPinAttenuation(PIN_PH, ADC_11db);
    analogSetPinAttenuation(PIN_TURBIDEZ, ADC_11db);

    sensors.begin();

    WiFi.mode(WIFI_STA);
    conectarWiFi();

    Serial.println("Sistema IoT iniciado");
}

// ======================================
// CICLO PRINCIPAL
// ======================================

void loop() {

    // Detectar y mostrar cambios en la conexion Wi-Fi
    static bool wifiConectadoAnterior = false;
    bool wifiConectadoActual = (WiFi.status() == WL_CONNECTED);

    if (wifiConectadoActual != wifiConectadoAnterior) {
        if (wifiConectadoActual) {
            Serial.println("WiFi conectado correctamente");
            Serial.print("Direccion IP: ");
            Serial.println(WiFi.localIP());
        } else {
            Serial.println("WiFi desconectado");
        }

        wifiConectadoAnterior = wifiConectadoActual;
    }

    actualizarLED();

    if (WiFi.status() != WL_CONNECTED) {

        if (millis() - ultimoIntentoWiFi >= REINTENTO_WIFI) {
            WiFi.disconnect();
            conectarWiFi();
        }

        delay(100);
        return;
    }

    if (millis() - ultimaTransmision < INTERVALO) {
        delay(20);
        return;
    }

    ultimaTransmision = millis();

    // ==================================
    // LECTURA DE TEMPERATURA
    // ==================================

    sensors.requestTemperatures();

    float temperatura = sensors.getTempCByIndex(0);

    if (temperatura == DEVICE_DISCONNECTED_C ||
        temperatura < -55 ||
        temperatura > 125) {

        Serial.println("Error leyendo DS18B20");
        return;
    }

    // ==================================
    // LECTURA DE POTENCIOMETROS
    // ==================================

    int adcPH = analogRead(PIN_PH);
    int adcTurbidez = analogRead(PIN_TURBIDEZ);

    // pH simulado: 0 a 14
    float ph = adcPH * 14.0 / 4095.0;

    // Turbidez simulada: 0 a 100 NTU
    float turbidez = adcTurbidez * 100.0 / 4095.0;

    // ==================================
    // MOSTRAR MEDICIONES
    // ==================================

    Serial.println("-------------------------");

    Serial.printf("Temperatura: %.2f C\n", temperatura);
    Serial.printf("pH simulado: %.2f\n", ph);
    Serial.printf("Turbidez simulada: %.2f NTU\n", turbidez);

    // ==================================
    // CONSTRUIR JSON
    // ==================================

    String json = "{";
    json += "\"dispositivo_id\":" + String(DISPOSITIVO_ID);
    json += ",\"ph\":" + String(ph, 2);
    json += ",\"turbidez\":" + String(turbidez, 2);
    json += ",\"temperatura\":" + String(temperatura, 2);
    json += "}";

    // ==================================
    // TRANSMISION HTTP
    // ==================================

    WiFiClient cliente;
    HTTPClient http;

    http.setTimeout(5000);

    if (http.begin(cliente, API_URL)) {

        http.addHeader("Content-Type", "application/json");

        // Destello al iniciar la transmisión
        destelloTransmision();

        int codigoHTTP = http.POST(json);

        Serial.printf("HTTP: %d\n", codigoHTTP);

        if (codigoHTTP > 0) {
            Serial.println(http.getString());
        } else {
            Serial.println("Error de comunicacion HTTP");
        }

        http.end();

    } else {
        Serial.println("No se pudo iniciar HTTP");
    }
}

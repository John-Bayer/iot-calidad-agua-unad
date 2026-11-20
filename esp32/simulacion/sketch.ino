#include <WiFi.h>
#include <HTTPClient.h>
#include <math.h>

// Proyecto IoT UNAD - simulacion de monitoreo durante 5 minutos.
// Datos completamente simulados; no proceden de sensores fisicos.
const char* SSID = "Wokwi-GUEST";
const char* PASSWORD = "";
const char* SERVIDOR = "http://181.53.125.215:8060/iot-calidad-agua-unad/api/recibir_medicion.php";
const int DISPOSITIVO_ID = 1;

const unsigned long INTERVALO_MS = 5000UL;
const int TOTAL_MEDICIONES = 60;  // 0, 5, ..., 295 segundos
const int INICIO_ANOMALIA = 42;  // 3:30
const int INICIO_RECUPERACION = 54; // 4:30

unsigned long inicioSimulacion = 0;
int medicionesIntentadas = 0;
int medicionesExitosas = 0;
bool finalizado = false;

float ruido(float amplitud) {
  return (random(-1000, 1001) / 1000.0f) * amplitud;
}

float limitar(float valor, float minimo, float maximo) {
  return fmaxf(minimo, fminf(maximo, valor));
}

bool conectarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return true;
  WiFi.mode(WIFI_STA);
  WiFi.begin(SSID, PASSWORD, 6);
  Serial.print("Conectando a WiFi");
  unsigned long inicio = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - inicio < 15000UL) {
    delay(250);
    Serial.print(".");
  }
  Serial.println();
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Sin conexion WiFi");
    return false;
  }
  Serial.print("WiFi conectado. IP: ");
  Serial.println(WiFi.localIP());
  return true;
}

void enviarMedicion(int numero) {
  // Un ciclo diario ficticio comprimido en 5 minutos.
  const float PI2 = 6.2831853f;
  float fase = PI2 * numero / TOTAL_MEDICIONES;
  float ph = 7.25f + 0.10f * sinf(fase) + ruido(0.04f);
  float turbidez = 1.20f + 0.22f * sinf(fase + 0.7f) + ruido(0.09f);
  float temperatura = 25.0f + 0.85f * sinf(fase - 1.0f) + ruido(0.12f);
  const char* etapa = "NORMAL";

  if (numero >= INICIO_ANOMALIA && numero < INICIO_RECUPERACION) {
    // Aumento gradual de turbidez y descenso del pH.
    float avance = (numero - INICIO_ANOMALIA + 1) /
                   float(INICIO_RECUPERACION - INICIO_ANOMALIA);
    ph = 7.20f - 1.70f * avance + ruido(0.04f);
    turbidez = 1.30f + 4.70f * avance + ruido(0.10f);
    etapa = "ANOMALIA";
  } else if (numero >= INICIO_RECUPERACION) {
    // Recuperacion progresiva, sin garantizar normalizacion completa.
    float avance = (numero - INICIO_RECUPERACION + 1) /
                   float(TOTAL_MEDICIONES - INICIO_RECUPERACION);
    ph = 5.50f + 1.75f * avance + ruido(0.03f);
    turbidez = 6.0f - 4.8f * avance + ruido(0.08f);
    etapa = "RECUPERACION";
  }

  ph = limitar(ph, 0.0f, 14.0f);
  turbidez = fmaxf(0.0f, turbidez);

  String json = "{\"dispositivo_id\":" + String(DISPOSITIVO_ID) +
                ",\"ph\":" + String(ph, 2) +
                ",\"turbidez\":" + String(turbidez, 2) +
                ",\"temperatura\":" + String(temperatura, 2) + "}";

  Serial.printf("\nMedicion %d/%d | %s | t=%ds\n", numero + 1,
                TOTAL_MEDICIONES, etapa, numero * 5);
  Serial.println(json);

  if (!conectarWiFi()) {
    Serial.println("Envio omitido por falta de conexion");
    return;
  }

  HTTPClient http;
  http.begin(SERVIDOR);
  http.setTimeout(4000);
  http.addHeader("Content-Type", "application/json");
  int codigo = http.POST(json);
  Serial.printf("HTTP: %d\n", codigo);
  if (codigo > 0) Serial.println(http.getString());
  else Serial.println(http.errorToString(codigo));
  if (codigo == 200) medicionesExitosas++;
  http.end();
}

void setup() {
  Serial.begin(115200);
  delay(500);
  randomSeed((unsigned long)micros());
  conectarWiFi();
  inicioSimulacion = millis();
  Serial.println("Inicio de simulacion: 60 mediciones, una cada 5 s.");
}

void loop() {
  if (finalizado) {
    delay(1000);
    return;
  }

  if (medicionesIntentadas < TOTAL_MEDICIONES) {
    unsigned long instante = inicioSimulacion +
                             (unsigned long)medicionesIntentadas * INTERVALO_MS;
    if ((long)(millis() - instante) >= 0) {
      enviarMedicion(medicionesIntentadas);
      medicionesIntentadas++;
    }
  }

  if (medicionesIntentadas == TOTAL_MEDICIONES) {
    finalizado = true;
    Serial.printf("\nSIMULACION FINALIZADA. Exitosas: %d/%d\n",
                  medicionesExitosas, TOTAL_MEDICIONES);
    Serial.println("No se enviaran mas mediciones hasta reiniciar.");
  }
  delay(20);
}
#include <WiFi.h>
#include <HTTPClient.h>

// Configuracion WiFi
const char* ssid = "Wokwi-GUEST";
const char* password = "";

// Servidor LAMPP
const char* servidor =
  "http://181.53.125.215:8060/iot-calidad-agua-unad/api/recibir_medicion.php";

void setup() {
  Serial.begin(115200);
  delay(1000);

  // Conexion WiFi
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password, 6);

  Serial.print("Conectando a WiFi");

  unsigned long inicio = millis();

  while (WiFi.status() != WL_CONNECTED &&
         millis() - inicio < 30000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\nERROR: No se pudo conectar a WiFi");
    return;
  }

  Serial.println("\nWiFi conectado");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());

  // Preparar solicitud HTTP
  HTTPClient http;
  http.begin(servidor);
  http.setTimeout(10000);
  http.addHeader("Content-Type", "application/json");

  // Medicion simulada
  String datos =
    "{\"dispositivo_id\":1,\"ph\":7.2,"
    "\"turbidez\":2.8,\"temperatura\":24.5}";

  Serial.println("Enviando medicion...");

  int codigo = http.POST(datos);

  Serial.print("Codigo HTTP: ");
  Serial.println(codigo);

  if (codigo > 0) {
    Serial.println("Respuesta:");
    Serial.println(http.getString());
  } else {
    Serial.println("Error:");
    Serial.println(http.errorToString(codigo));
  }

  http.end();
}

void loop() {
  // Una medicion por inicio
}

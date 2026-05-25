/* Ejemplo: enviar JSON al servidor con HTTPCient
 * Reemplazar SSID/PASS y SERVER_HOST según su red
 */
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>

const char* ssid = "TU_SSID";
const char* pass = "TU_PASS";
const char* server = "https://optoelectronica.agustingimenez.ar/save_data.php";

void setup(){
  Serial.begin(115200);
  WiFi.begin(ssid, pass);
  while(WiFi.status() != WL_CONNECTED){
    delay(500);
    Serial.print('.');
  }
  Serial.println("\nWiFi conectado");
}

void loop(){
  if(WiFi.status() == WL_CONNECTED){
    WiFiClientSecure client;
    client.setInsecure(); // Desarrollo: no verificar certificado. Reemplazar con CA en producción.
    HTTPClient https;
    https.begin(client, server);
    https.addHeader("Content-Type","application/json");

    String payload = R"({"LDR_NW":123,"LDR_NE":120,"LDR_SW":110,"LDR_SE":115,"H":5,"servo_angle":90,"Vpanel":4.8,"Vbat":3.9,"I":0.18,"P":0.864,"irradiance":150.0,"soc":78,"charging":true})";

    int code = https.POST(payload);
    String resp = https.getString();
    Serial.printf("POST %d -> %s\n", code, resp.c_str());
    https.end();
  }
  delay(2000);
}

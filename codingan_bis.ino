#include <WiFi.h>
#include <HTTPClient.h>
#include <Arduino_JSON.h>

// Ganti dengan SSID dan password WiFi Anda
const char* ssid = "meow";
const char* password = "cimel2112";

// Ganti dengan URL server PHP Anda
const char* serverUrl = "http://192.168.166.17bismillahbisaplisa/update.php";

WiFiClient client;
HTTPClient http;

// Variabel untuk menyimpan data penumpang
int passengersIn = 0;
int passengersOut = 0;

void setup() {
  Serial.begin(115200);

  // Menghubungkan ke WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  // Simulasi pembacaan RFID
  int rfidData = readRFID(); // Ganti dengan fungsi pembacaan RFID yang sesuai

  if (rfidData > 0) {
    // Penumpang masuk
    passengersIn += rfidData;
    sendDataToServer("enter", rfidData);
  } else if (rfidData < 0) {
    // Penumpang keluar
    passengersOut += abs(rfidData);
    sendDataToServer("exit", abs(rfidData));
  }

  delay(5000); // Delay untuk menghindari pengiriman data yang terlalu cepat
}

void sendDataToServer(String action, int number) {
  String postData = "action=" + action + "&number=" + String(number);
  
  http.begin(client, serverUrl);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpResponseCode = http.POST(postData);

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println(response);
  } else {
    Serial.println("Error sending POST request");
  }

  http.end();
}

int readRFID() {
  // Implementasikan logika pembacaan RFID di sini
  // Misalnya, jika RFID terdeteksi, kembalikan 1 (penumpang masuk)
  // Jika penumpang keluar, kembalikan -1
  // Untuk contoh ini, kita akan menggunakan simulasi
  static int state = 0;
  state = (state + 1) % 3; // Simulasi: 0 = tidak ada, 1 = masuk, 2 = keluar
  if (state == 1) return 1;  // 1 penumpang masuk
  if (state == 2) return -1; // 1 penumpang keluar
  return 0; // Tidak ada perubahan
}

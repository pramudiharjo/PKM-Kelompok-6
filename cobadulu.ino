#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>

#define RST_PIN         22         
#define SS_PIN          5          

MFRC522 rfid(SS_PIN, RST_PIN); 

String URL = "http://computer_ip/project_folder/project_file.php"; 

const char* ssid = "YOUR_SSID";        // Ganti dengan SSID WiFi Anda
const char* password = "YOUR_PASSWORD"; // Ganti dengan password WiFi Anda

int jumlahpenumpang = 0;          
const int maksimalpenumpang = 100; 
byte tapinkartu[maksimalpenumpang][6]; 
int jumlahtapin = 0; 

void setup() {
    Serial.begin(115200);        
    SPI.begin();                 
    rfid.PCD_Init();             
    Serial.println("tempelkan kartu anda");
    
    // Menghubungkan ke WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("Connected to WiFi");
}

void loop() {
    // Look for a new card
    if (!rfid.PICC_IsNewCardPresent()) {
        return;
    }
    
    // Select one of the cards
    if (!rfid.PICC_ReadCardSerial()) {
        return;
    }

    if (isCardTappedIn(rfid.uid.uidByte, rfid.uid.size)) {
        jumlahpenumpang--;  
        Serial.print("penumpang turun. jumlah saat ini: ");
        Serial.println(jumlahpenumpang);
        removeCardFromTappedIn(rfid.uid.uidByte, rfid.uid.size);
        sendDataToServer("down");
    } else {
        jumlahpenumpang++;  
        Serial.print("penumpang naik. jumlah saat ini: ");
        Serial.println(jumlahpenumpang);
        addCardToTappedIn(rfid.uid.uidByte, rfid.uid.size);
        sendDataToServer("up");
    }

    // Halt PICC
    rfid.PICC_HaltA();
}

bool isCardTappedIn(byte *uid, byte size) {
    for (int i = 0; i < jumlahtapin; i++) {
        if (compareUID(tapinkartu[i], uid, size)) {
            return true;
        }
    }
    return false; 
}

void addCardToTappedIn(byte *uid, byte size) {
    if (jumlahtapin < maksimalpenumpang) {
        memcpy(tapinkartu[jumlahtapin], uid, size);
        jumlahtapin++;
    } else {
        Serial.println("Maximum passengers limit reached. Cannot add more.");
    }
}

void removeCardFromTappedIn(byte *uid, byte size) {
    for (int i = 0; i < jumlahtapin; i++) {
        if (compareUID(tapinkartu[i], uid, size)) {
            for (int j = i; j < jumlahtapin - 1; j++) {
                memcpy(tapinkartu[j], tapinkartu[j + 1], 6);
            }
            jumlahtapin--;
            break;
        }
    }
}

bool compareUID(byte *uid1, byte *uid2, byte size) {
    for (byte i = 0; i < size; i++) {
        if (uid1[i] != uid2[i]) {
            return false;
        }
    }
    return true;
}

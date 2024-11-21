#include <SPI.h>
#include <MFRC522.h>

#define RST_PIN         22         
#define SS_PIN          5          

MFRC522 rfid(SS_PIN, RST_PIN);   

int jumlahpenumpang = 0;          
const int maksimalpenumpang = 100; 
byte tapinkartu[maksimalpenumpang][6]; 
int jumlahtapin = 0; 

void setup() {
    Serial.begin(115200);        
    SPI.begin();                 
    rfid.PCD_Init();             
    Serial.println("tempelkan kartu anda");
}

void loop() {
  
    if (!rfid.PICC_IsNewCardPresent()) {
        return;
    }
  
    if (!rfid.PICC_ReadCardSerial()) {
        return;
    }

    if (cektapinkartu(rfid.uid.uidByte, rfid.uid.size)) {
        jumlahpenumpang--;  
        Serial.print("penumpang turun. jumlah saat ini: ");
        Serial.println(jumlahpenumpang);
        hapustapinkartu(rfid.uid.uidByte, rfid.uid.size);
    } else {
        jumlahpenumpang++;  
        Serial.print("penumpang naik. jumlah saat ini: ");
        Serial.println(jumlahpenumpang);
        tambahtapinkartu(rfid.uid.uidByte, rfid.uid.size);
    }
    rfid.PICC_HaltA();
}

bool cektapinkartu(byte *uid, byte size) {
    for (int i = 0; i < jumlahtapin; i++) {
        if (compareUID(tapinkartu[i], uid, size)) {
            return true;
        }
    }
    return false; 
}

void tambahtapinkartu(byte *uid, byte size) {
    if (jumlahtapin < maksimalpenumpang) {
        memcpy(tapinkartu[jumlahtapin], uid, size);
        jumlahtapin++;
    } else {
        Serial.println("Maximum passengers limit reached. Cannot add more.");
    }
}

void hapustapinkartu(byte *uid, byte size) {
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

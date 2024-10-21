#include <WiFi.h>
#include <HTTPClient.h>
#include <NewPing.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_PCF8574.h>
#include <WebServer.h>

// Define pins for ultrasonic sensor, relays, and flow sensors
#define TRIGGER_PIN 5       // Trigger pin for ultrasonic sensor
#define ECHO_PIN 18         // Echo pin for ultrasonic sensor
#define MAX_DISTANCE 200    // Maximum distance to measure (in cm)
#define RELAY_PUMP  33      // Relay pin for controlling the pump
#define SOLENOID_VALVE_1 26 // Solenoid pin for controlling solenoid valve 1
#define SOLENOID_VALVE_2 27 // Solenoid pin for controlling solenoid valve 2
#define FLOW_SENSOR_1 15    // Flow sensor pin 1
#define FLOW_SENSOR_2 4     // Flow sensor pin 2

volatile int pulseCount1 = 0;
volatile int pulseCount2 = 0;
float totalLitres1 = 0.0;
float totalLitres2 = 0.0;
float calibrationFactor = 350;
float waterLevelPercentage = 0.0;
float flowRate = 0.0;

WebServer server(80);   // Create a web server object that listens on port 80

// Define limits for distance measurements
#define MIN_DISTANCE 2       // Minimum distance in cm
#define MAX_WATER_DISTANCE 32 // Maximum distance in cm

// Network credentials and server URLs
const char* ssid = "Preizim"; // WiFi SSID
const char* password = "987654321"; // WiFi password
const char* serverName = "http://192.168.211.91//PRAISE/water_management.php"; // URL for transaction processing
const char* pumpControlUrl = "http://192.168.211.91//PRAISE/control_pump.php"; // URL for pump control

// Initialize ultrasonic sensor
NewPing sonar(TRIGGER_PIN, ECHO_PIN, MAX_DISTANCE); // Create NewPing object

// Initialize LCD
LiquidCrystal_PCF8574 lcd(0x27); // You may need to adjust the address

void IRAM_ATTR pulseCounter1() {
  pulseCount1++;
}

void IRAM_ATTR pulseCounter2() {
  pulseCount2++;
}

void handleData(){
  String json ="{";
  json += "\"water_level\":" + String(waterLevelPercentage) + ",";
  json += "\"totalLitres1\":" + String(totalLitres1);
  

  json += "}";
  server.send(200,"application/json", json);
}
  

void setup() {
  Serial.begin(115200); // Begin serial communication at 115200 baud rate
 
  // Set relay pins as outputs
  pinMode(RELAY_PUMP, OUTPUT);           // Set RELAY_PUMP pin as output
  pinMode(SOLENOID_VALVE_1, OUTPUT);     // Set SOLENOID_VALVE1 pin as output
  pinMode(SOLENOID_VALVE_2, OUTPUT);     // Set SOLENOID_VALVE2 pin as output
  pinMode(FLOW_SENSOR_1, INPUT_PULLUP);
  pinMode(FLOW_SENSOR_2, INPUT_PULLUP);

  attachInterrupt(digitalPinToInterrupt(FLOW_SENSOR_1), pulseCounter1, FALLING);
  attachInterrupt(digitalPinToInterrupt(FLOW_SENSOR_2), pulseCounter2, FALLING);

  // Initialize relay states
  digitalWrite(RELAY_PUMP, HIGH);            // Set RELAY_PUMP to LOW (pump off)
  digitalWrite(SOLENOID_VALVE_1, HIGH);      // Set SOLENOID_VALVE1 to LOW (valve 1 closed)
  digitalWrite(SOLENOID_VALVE_2, HIGH);      // Set SOLENOID_VALVE2 to LOW (valve 2 closed)
 
  // Initialize the LCD
  lcd.begin(16, 2);                         // Initialize the LCD
  lcd.setBacklight(255);
  lcd.setCursor(0, 0);

  // Connect to WiFi network
  WiFi.begin(ssid, password);               // Start WiFi connection
  while (WiFi.status() != WL_CONNECTED) {   // Wait until WiFi is connected
    delay(1000);                            // Wait for 1 second before trying again
    Serial.println("Connecting to WiFi..."); // Print connection status
  }
  Serial.println("Connected to WiFi");      // Print connected status
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connected to");
  lcd.setCursor(0, 1);
  lcd.print("WiFi");

  Serial.println("Setup complete");

  // Define the route for controlling the pump
  server.on("/control", HTTP_GET, []() {
    String action = server.arg("action");
    String responseMessage;

    if (action == "on") {
      digitalWrite(RELAY_PUMP, LOW);
      responseMessage = "Pump turned on";
    } else if (action == "off") {
      digitalWrite(RELAY_PUMP, HIGH);
      responseMessage = "Pump turned off";
    } else {
      responseMessage = "Invalid action";
      server.send(400, "text/plain", responseMessage);
      Serial.println(responseMessage);
      return;
    }

    server.send(200, "text/plain", responseMessage);
    Serial.println(responseMessage);  // Print the response to Serial Monitor
  });
  server.on("/data", HTTP_GET, handleData);

  server.on("/approve", HTTP_POST, []() {
    if (server.hasArg("channel") && server.hasArg("approved_amount")) {
      int channel = server.arg("channel").toInt();
      float approvedAmount = server.arg("approved_amount").toFloat();
      Serial.println(channel);
      Serial.println(approvedAmount);

      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print(channel);
      lcd.setCursor(0, 1);
      lcd.print(approvedAmount);
      

      if (channel == 1) {
        xTaskCreatePinnedToCore(
          controlValveTask1,  // Task function
          "ValveControlTask1", // Task name
          10000,              // Stack size
          (void*)&approvedAmount, // Parameter
          1,                  // Priority
          NULL,               // Task handle
          1                   // Core
        );
      } else if (channel == 2) {
        xTaskCreatePinnedToCore(
          controlValveTask2,  // Task function
          "ValveControlTask2", // Task name
          10000,              // Stack size
          (void*)&approvedAmount, // Parameter
          1,                  // Priority
          NULL,               // Task handle
          1                   // Core
        );
      }
      String responseMessage = "You have successfully approved " + String(approvedAmount) + " Litres of water";
      server.send(200, "text/plain", responseMessage);
      
    } else {
      server.send(400, "text/plain", "Invalid request");
    }
  });

   server.on("/water_level", HTTP_GET, []() {
    int distance = sonar.ping_cm(); // Get distance in centimeters
    float waterLevelPercentage = 100.0 * (MAX_WATER_DISTANCE - distance) / (MAX_WATER_DISTANCE - MIN_DISTANCE);
    String response = "{\"waterLevel\": " + String(waterLevelPercentage) + "}";
    server.send(200, "application/json", response);
  });

  server.enableCORS();

  server.begin(); // Start the server
}

void controlValveTask1(void* parameter) {
  float approvedAmount = *((float*)parameter);
  pulseCount1 = 0;
  totalLitres1 = 0.0;
  controlValve(FLOW_SENSOR_1, SOLENOID_VALVE_1, approvedAmount, pulseCount1, totalLitres1);
  vTaskDelete(NULL);  // Delete the task when done
}

void controlValveTask2(void* parameter) {
  float approvedAmount = *((float*)parameter);
  pulseCount2 = 0;
  totalLitres2 = 0.0;
  controlValve(FLOW_SENSOR_2, SOLENOID_VALVE_2, approvedAmount, pulseCount2, totalLitres2);
  vTaskDelete(NULL);  // Delete the task when done
}

void loop() {
  server.handleClient(); // Handle client requests

  int distance = sonar.ping_cm(); // Get distance in centimeters
  if (distance >= MIN_DISTANCE && distance <= MAX_WATER_DISTANCE) {
    // Calculate water level percentage
    waterLevelPercentage = 100.0 * (MAX_WATER_DISTANCE - distance) / (MAX_WATER_DISTANCE - MIN_DISTANCE);
    Serial.print("Water Level: ");
    Serial.print(waterLevelPercentage);
    Serial.println("%");

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Water Level:");
    lcd.setCursor(0, 1);
    lcd.print(waterLevelPercentage);
    lcd.print("%");
    delay(1000);
    if (waterLevelPercentage < 37) {
      digitalWrite(RELAY_PUMP, LOW); // Turn on pump
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Pumping in");
      lcd.setCursor(0, 1);
      lcd.print("progress...");
    } else if (waterLevelPercentage >= 100) {
      digitalWrite(RELAY_PUMP, HIGH); // Turn off pump
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Pumping");
      lcd.setCursor(0, 1);
      lcd.print("stopped");
    }
  }
}

void controlValve(int flowSensorPin, int solenoidValvePin, float approvedLitres, volatile int &pulseCount, float &totalLitres) {
  float litresPerPulse = 1.0 / calibrationFactor;

  digitalWrite(solenoidValvePin, LOW);

  // Reset total litres before starting
  totalLitres = 0.0;

  unsigned long startTime = millis();
  while (totalLitres < approvedLitres) {
    flowRate = pulseCount * litresPerPulse;
  
    totalLitres += flowRate;
    totalLitres1 = totalLitres;
    pulseCount = 0;

    delay(50); // Wait for a second before checking again

    // Exit the loop if it runs too long (safety measure)
    if (millis() - startTime > 5000000) { // 60 seconds max
      break;
    }
  }

  digitalWrite(solenoidValvePin, HIGH);
}

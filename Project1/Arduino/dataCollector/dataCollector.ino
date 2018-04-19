const int pinEnterTrig = 9;
const int pinEnterEcho = 10;
const int pinExitTrig = 11;
const int pinExitEcho = 12;

void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  pinMode(pinEnterTrig, OUTPUT);
  pinMode(pinEnterEcho, INPUT);
  pinMode(pinExitTrig, OUTPUT);
  pinMode(pinExitEcho, INPUT);
}

void loop() {
  // put your main code here, to run repeatedly:
  digitalWrite(pinEnterTrig, LOW);
  digitalWrite(pinExitTrig, LOW);
  delayMicroseconds(2);

  digitalWrite(pinEnterTrig, HIGH);
  delayMicroseconds(10);
  digitalWrite(pinEnterTrig, LOW);
  float en = (pulseIn(pinEnterEcho, HIGH)/2)/29.1;

  
  digitalWrite(pinExitTrig, HIGH);
  delayMicroseconds(10);
  digitalWrite(pinExitTrig, LOW);
  float ex = (pulseIn(pinExitEcho, HIGH)/2)/29.1;
  
  
  Serial.print("A");
  Serial.println(en);
  Serial.print("B");
  Serial.println(ex);
}

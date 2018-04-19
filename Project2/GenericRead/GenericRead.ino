int enterTrigPin = 0;
int enterEchoPin = 1;
int exitTrigPin = 2;
int exitEchoPin = 3;

// Location values single character [A-Z]
char m_location;

void setup() {
  Serial.begin(115200);
  while(!Serial);
  
  pinMode(enterTrigPin, OUTPUT);
  pinMode(enterEchoPin, INPUT);
  pinMode(exitTrigPin, OUTPUT);
  pinMode(exitEchoPin, INPUT);

  m_location = 'A';
  Serial.println(m_location);
}

void loop() {
    digitalWrite(enterTrigPin, LOW);
    digitalWrite(exitTrigPin, LOW);
    delayMicroseconds(2);

    digitalWrite(enterTrigPin, HIGH);
    digitalWrite(exitTrigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(enterTrigPin, LOW);
    digitalWrite(exitTrigPin, LOW);

    float denter = pulseIn(enterEchoPin, HIGH);
    float dexit = pulseIn(exitEchoPin, HIGH);

    float distance_en = denter*0.034/2;
    float distance_ex = dexit*0.034/2;

    Serial.print("A: ");
    Serial.println(distance_en);
    Serial.print("B: ");
    Serial.println(distance_ex);
}

int pinEnter = 0;
int pinExit = 1;

void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
}

void loop() {
  // put your main code here, to run repeatedly:
  int en = analogRead(pinEnter);
  int ex = analogRead(pinExit);
  char en_w[16];
  char ex_w[16];
  sprintf(en_w, "A%d", en);
  sprintf(ex_w, "B%d", ex);
  Serial.println(en_w + " " + ex_w);
}

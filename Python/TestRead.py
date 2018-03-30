import ardcon
import serial
import time

ardu = ardcon.Arduino()
ardu.setup('COM3')
ardu.setDistance(2)

def loop():
	count = 0
	while True:
		if ardu.genericRead('A'):
			timeout = time.time() + 5;
			while time.time() < timeout:
				if ardu.genericRead('B'):
					count += 1
					print("PC: {}".format(count))
					break
			ardu.eatSerial()
		else:
			continue
				
					
def main():
	while(True):
		try:
			loop()
		except Exception:
			continue
	
main()
ardu.close()
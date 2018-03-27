import ardcon
import serial
import time

ardu = ardcon.Arduino()
ardu.setup('COM3')
ardu.setDistance(2)

while True:
	if ardu.dataAvailable():
		en, ex, status = ardu.read()
		if not status:
			print("Error reading")
		else:
			print("{}".format((en, ex)))
			if en and ex:
				break
		time.sleep(.5)
ardu.close()

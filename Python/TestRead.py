import ardcon
import serial
from datetime import datetime

ardu = ardcon.Arduino()
ardu.setup('COM3')
ardu.setDistance(2)

def loop():
	while (True):
		timestart = datetime.now();
		timeout = 2
		if (ardu.genericRead('A')):
			print('a read')
			while(True):
				print('loop2 top')
				if (ardu.genericRead('B')):
					# send data right here!
					print('I saw someone')
				if (ardu.genericRead('A')):
					print('adding time')
					timeout += 1
				if (datetime.now() - timestart).seconds > timeout:
					break
					
def main():
	loop()
	
main()

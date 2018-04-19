import ardcon
import serial
import time
import Database

ardu = ardcon.Arduino()
ardu.setup('COM3')
ardu.setDistance(2)

tab = Database.Table('Traffic', ['Num', 'Time'])
db = Database.Database()
db.Connect('database.cse.tamu.edu', 'minkjaco', 'jacobmink123', 'minkjaco')
db.SetTable(tab)

def loop():
	count = 0
	try:
		while True:
			if ardu.genericRead('A'):
				timeout = time.time() + 5;
				while time.time() < timeout:
					if ardu.genericRead('B'):
						count += 1
						print("PC: {}".format(count))
						db.Insert(['', 'CURRENT_TIMESTAMP'])
						break
				ardu.eatSerial()
			else:
				continue
	except Exception as e:
		print("Error: {}".format(e))
					
def main():
	while(True):
		try:
			loop()
		except Exception as e:
			print("Error: {}".format(e))
			continue
	
main()
ardu.close()
db.Disconnect()
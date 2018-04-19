import ardcon
import serial
import unittest

class TestArduinoMethods(unittest.TestCase):

	def test_setup_NotString(self):
		ard = ardcon.Arduino()
		with self.assertRaises(TypeError):
			ard.setup(3)
	def test_setup_NotPort(self):
		ard = ardcon.Arduino()
		with self.assertRaises(serial.SerialException):
			ard.setup('CM3')
	def test_setup_BadSerial(self):
		pass
	
	def test_setDistance_NotInt(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		with self.assertRaises(TypeError):
			ard.setDistance('1')
		ard.close()
	def test_setDistance_NegInt(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		with self.assertRaises(ValueError):
			ard.setDistance(-30)
		ard.close()
	def test_setDistance_LargeInt(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		with self.assertRaises(ValueError):
			ard.setDistance(7)
		ard.close()
		
	def test_read_BadSensorInput_Type(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		ard.setDistance(2)
		with self.assertRaises(TypeError):
			ard.genericRead(3)
		ard.close()
	def test_read_BadSensorInput_Value(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		ard.setDistance(2)
		with self.assertRaises(TypeError):
			ard.genericRead('B')
		ard.close()
		
	def test_read_Untriggered(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		ard.setDistance(2)
		print('Do not trigger')
		while True:
			if not ard.genericRead('A'):
				break
		ard.close()
	def test_read_Triggered(self):
		ard = ardcon.Arduino()
		ard.setup('COM3')
		ard.setDistance(2)
		print('Trigger')
		while True:
			if ard.genericRead('A'):
				break
		ard.close()
		
	def test_eatSerial_NotConnected(self):
		ard = ardcon.Arduino()
		with self.AssertRaises(Exception):
			ard.eatSerial()
			
			
if __name__ == '__main__':
	unittest.main()
import serial
import time
from time import gmtime, strftime
from threading import Thread

# Package requires that the user imports
# serial package to handle certain serial errors

# Error class for issues with
# connection to serial input of Arduino
class ConnectionError(Exception):
	def __init__(self, value):
		self.value = value
	def __str__(self):
		return(repr(self.value))
		
# Error class for issues with
# reading from the serial input
class ReadError(Exception):
	def __init__(self, value):
		self.value = value
	def __str__(self):
		return(repr(self.value))

# wrapper class for all Arduino connection
# and read capabilities
class Arduino:
	MAXIMUM_DISTANCE	= 3.28084
	
	def __init__(self):
		self.log = open('log.txt', 'w')
		self.ser = None
		self.baseDistance = 0
		
	# connects to the serial port and
	# creates a log file
	#
	# Preconditions: 
	# Arduino is connected to system
	# Command line is run with administrator privileges
	#
	# Exceptions:
	# TypeError - input is not of correct type
	# Connection Error - connection to serial port invalid
	def setup(self, port):
		if not isinstance(port, str):
			self.log.write('{} Type Error in setup'.format(Arduino.curTime()))
			raise TypeError("Port must be provided as a string")
		# initialize serial input
		self.ser = serial.Serial(port, 9600)
		if self.ser == None:
			self.log.write('{} Connection Error in setup'.format(Arduino.curTime()))
			raise ConnectionError("Connection to serial port failed")
		
		# create log file
		log = open('log.txt', 'w')
		log.write("{} Initial entry".format(Arduino.curTime()))
	# Postconditions: 
	# Arduino is connected via serial
	# Log file is created with initial entry
	
	# Explicitly called destructor
	#
	# Preconditions:
	# None
	# 
	# Exceptions:
	# None
	def close(self):
		if self.log:
			self.log.close()
		if self.ser:
			self.ser.close()
	# Postconditions:
	# Log file is closed
	# Serial connection is closed

	# sets the maximum distance to look for a person
	#
	# Preconditions:
	# d - distance in feet
	#
	# Exceptions:
	# TypeError - non-integer input
	# ValueError - negative distance input or distance input greater than MAXIMUM_DISTANCE
	def setDistance(self, d):
		if not isinstance(d, int):
			self.log.write('{} Type Error in setDistance'.format(Arduino.curTime()))
			raise TypeError('Distance provided must be an integer')
		elif d < 0 or d > self.MAXIMUM_DISTANCE:
			self.log.write('{} Value Error in setDistance'.format(Arduino.curTime()))
			raise ValueError('Invalid distance value')
			
		# d ft*m / 3.28084 ft
		# distance checked in centimeters
		self.baseDistance = (d / self.MAXIMUM_DISTANCE) * 100
	# Postconditions:
	# the baseDistance is now d
		
	# returns current time as a string 
	# in Y-M-D H:M:S format
	#
	# Preconditions:
	# None
	def curTime():
		return strftime("%Y-%m-%d %H:%M:%S", gmtime())
	# Postconditions:
	# None
	
	# Returns True if the sensor reads within the
	# distance given, False otherwise
	#
	# Preconditions:
	# Called in a loop to continuously test
	# setup() has been called
	# setDistance() has been called with a valid value
	# sensor - a string indicating sensor 'A' or sensor 'B'
	#
	# Exceptions:
	# Type Error - invalid input
	# Instead of raising error, returns False on input the 
	# serial reader cannot decode
	def genericRead(self, sensor):
		if not isinstance(sensor, string) or not (sensor is 'A' or sensor is 'B'):
			self.log.write("{} sensor argument error".format(Arduino.curTime()))
			raise TypeError("sensor argument must be 'A' or 'B'")
		val = 500.0
		while (True):
			raw = self.ser.readline().decode('utf-8')
			if raw is '':
				self.log.write('{} Read Error in genericRead'.format(Arduino.curTime()))
				return False
			conditioned = raw.strip()
			if conditioned is '':
				self.log.write("{} Read Error in genericRead".format(Arduino.curTime()))
				return False
			if conditioned[0] is sensor:
				val = float(conditioned[1:])
				break
		if val < self.baseDistance:
			return True
		return False
	# Postconditions:
	# Returns True if the indicated sensor reads a value
	# within the set distance
	# Returns False otherwise
		
	# Ignores serial input for 2 seconds
	#
	# Preconditions:
	# setup() has been called
	# serial input is valid
	#
	# Exceptions:
	# Read Error - serial input read issue
	def eatSerial(self):
		timeout = time.time() + 2
		while time.time() < timeout:
			if self.ser.readline() is '':
				self.log.write('{} Read Error in eatSerial'.format(Arduino.curTime()))
				raise ReadError("Error ignoring serial data")
	# Postconditions:
	# Serial input is ignored
	
	# Example loop function to read persons walking in
	# to the room
	# def loop():
	# 	count = 0
	# 	while True:
	#		if ardu.genericRead('A'):
	#			timeout = time.time() + 5;
	#			while time.time() < timeout:
	#				if ardu.genericRead('B'):
	#					count += 1
	#					print("PC: {}".format(count))
	#					break
	#			ardu.eatSerial()
	#		else:
	#			continue
		
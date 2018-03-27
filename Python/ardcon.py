import serial
import time
from time import gmtime, strftime

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
	MAXIMUM_DISTANCE	= 5	
	
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
		elif d < 0 or d > ((500 - self.MAXIMUM_DISTANCE) / 96):
			self.log.write('{} Value Error in setDistance'.format(Arduino.curTime()))
			raise ValueError('Invalid distance value')
			
		# d * (-98) + 500 sets our range of distances 
		# between 500 (0 feet) and 10 (5 feet)
		self.baseDistance = d * (-96) + 500
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
	
	def dataAvailable(self):
		return self.ser.in_waiting > 0
	
	# Returns True if the sensor reads within the
	# distance given, or False otherwise
	#
	# Preconditions:
	# Called in a loop to continuously test
	# setup() has been called
	# setDistance() has been called
	#
	# Exceptions:
	# Read Error - serial input read issue
	def read(self):
		raw = self.ser.readline().decode('utf-8')
		if raw is None:
			self.log.write('{} Read Error in read'.format(Arduino.curTime()))
			raise ReadError('Could not read from serial input')
		conditioned = raw.strip()
		if conditioned is '':
			self.log.write("{} Read Error in read".format(Arduino.curTime()))
			return (False, False, False)
		
		enter = False
		exit = False
		l = conditioned.split(' ')
		en = l[0]
		ex = l[1]
		if en[0] == 'A' and ex[0] == 'B':
			if int(en[1:]) > self.distance:
				enter = True
			if int(ex[1:]) > self.distance:
				exit = True
		
		return (enter, exit, True)
	# Postconditions:
	# returns the boolean triggered/not triggered
		
	def genericRead(self, sensor):
		val = 0
		while (true):
			raw = self.readline().decode('utf-8')
			if raw is None:
				self.log.write('{} Read Error in read'.format(Arduino.curTime()))
				continue
			conditioned = raw.strip()
			if conditioned is '':
				self.log.write("{} Read Error in read".format(Arduino.curTime()))
				continue
			if conditioned[0] is sensor:
				val = float(conditioned[1:])
				break
		if val > self.distance:
			return True
		return False
		
	def loop(self):
		timeout = time.time() + 2
		while (True):
			if (genericRead('A')):
				while(True):
					if (genericRead('B')):
						#send data
						pass
					if (genericRead('A')):
						timeout += 1
					if time.time() > timeout:
						break
		
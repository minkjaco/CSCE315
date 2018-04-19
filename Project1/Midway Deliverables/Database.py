import pymysql.cursors

# class to abstract the content of a
# MySQL table
class Table:
	# __init__(Table self, String name, String[] cols)
	# constructor
	#
	# Preconditions:
	# Name of the table and list of column names are provided
	# 
	# Postconditions:
	# Table is ready for use and represents the given table
	#
	# Exceptions:
	# None
	def __init__(self, name, cols):
		self.m_name = name
		self.m_cols = cols
		
class DatabaseException(Exception):
	def __init__(self, value):
		self.m_value = value
	def __str__(self):
		return(repr(self.value))
		
# class to abstract the concept of a
# MySQL database with a single table
class Database:

	# __init__(Database self)
	# constructor
	#
	# Preconditions:
	# Table is an initialized table instance
	#
	# Postconditions:
	# Table is available inside database instance
	#
	# Exceptions:
	# None
	def __init__(self):
		self.m_table = None
		self.m_connection = None
		self.m_cursor = None
	
	# SetTable(Database self, Table table)
	# specify the table in question as a Table class
	#
	# Preconditions:
	# Table is already initialized
	#
	# Postconditions:
	# internal table is set to the new table parameters
	#
	# Exceptions:
	# TypeError - input is not a Table
	# ValueError - input is a NoneType
	def SetTable(self, table):
		if not isinstance(table, Table):
			raise TypeError('Invalid input to setTable')
		self.m_table = table
	
	# Connect(Database self, String host, String user, String passw, String db)
	# connect to a given database host
	#
	# Preconditions:
	# All provided values are strings
	#
	# Postconditions:
	# Connection and Cursor are valid and usable
	#
	# Exceptions:
	# DatabaseError - either connection is invalid or cursor is invalid
	def Connect(self, host, user, passw, db):
		if not (isinstance(host, str) and isinstance(user, str) and isinstance(passw, str) and isinstance(db, str)):
			raise TypeError('Invalid input parameters in connect')
			
		self.m_connection = pymysql.connect(host=host, user=user, password=passw, db=db, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
		if self.m_connection is None:
			raise DatabaseError('database connection is invalid')
			
		self.m_cursor = self.m_connection.cursor()
		if self.m_cursor is None:
			raise DatabaseError('cursor is None')
	
	
	# Disconnect(Database self)
	# disconnect from current database and host
	#
	# Preconditions:
	# Connection to database is made
	#
	# Postconditions:
	# m_connection and m_cursor are None
	#
	# Exceptions:
	# None
	def Disconnect(self):
		if self.m_connection:
			self.m_connection.close()
			self.m_connection = None
		cursor = None
	
	# Insert(Database self, String[] values)
	# insert values into the database according to table specification
	#
	# Preconditions:
	# connect has been called
	# setTable has been called
	# values is a list of strings
	#
	# Postconditions:
	# database is updated
	#
	# Exceptions:
	# TypeError - values is not list, or its elements are not strings
	# DatabaseException - execute of insert query fails
	def Insert(self, values):
		if not isinstance(values, list):
			raise TypeError('values is not a list in insert')
		elif not all(isinstance(s, str) for s in values):
			raise TypeError('elements of values are not strings in insert')
		elif not (len(values) == len(self.m_table.cols)):
			raise ValueError('values dimension must match table dimension')
			
		sql = "INSERT INTO " + self.m_table.name + " VALUES ("
		for i, v in enumerate(values):
			if len(v) == 0:
				sql += '\'\''
			else:
				sql += v
			if i != len(values) - 1:
				sql += ", "
		sql += ")"
		
		if self.m_cursor.execute(sql) != 1:
			raise DatabaseException('Failed to execute SQL insert query')
		self.m_connection.commit()
	
import Database
import pymysql
import unittest

class TestDatabaseMethods(unittest.TestCase):
	# Table Creation
	def test_setTable_invalidTable(self):
		db = Database.Database()
		with self.assertRaises(TypeError):
			db.setTable(1)
	
	# Database Connection
	def test_connect_invalidInput(self):
		db = Database.Database()
		with self.assertRaises(TypeError):
			db.connect(1, 2, 3, 4)
	def test_connect_invalidConnect(self):
		db = Database.Database()
		with self.assertRaises(pymysql.OperationalError):
			db.connect('database.cse.tamu.edu', 'XXXXX', 'XXXXX', 'XXXXX')
	def test_connect_validConnect_validTable(self):
		db = Database.Database()
		db.setTable(Database.Table('Test', ['Num', 'Time']))
		db.connect('database.cse.tamu.edu', 'XXXXX', 'XXXXX', 'XXXXX')
		self.assertNotEqual(db.table, None)
		self.assertNotEqual(db.connection, None)
		db.disconnect()
	
	# Inserting to database
	def test_insert_notAList(self):
		db = Database.Database()
		db.setTable(Database.Table('Test', ['Num', 'Time']))
		db.connect('database.cse.tamu.edu', 'XXXXX', 'XXXXX', 'XXXXX')
		with self.assertRaises(TypeError):
			db.insert(1)
		db.disconnect()
	def test_insert_notStrings(self):
		db = Database.Database()
		db.setTable(Database.Table('Test', ['Num', 'Time']))
		db.connect('database.cse.tamu.edu', 'XXXXX', 'XXXXX', 'XXXXX')
		with self.assertRaises(TypeError):
			db.insert([1, 2, 3])
		db.disconnect()
	def test_insert_badDimensions(self):
		db = Database.Database()
		db.setTable(Database.Table('Test', ['Num', 'Time']))
		db.connect('database.cse.tamu.edu', 'XXXXX', 'XXXXX', 'XXXXX')
		with self.assertRaises(ValueError):
			db.insert(["1", "2", "3"])
		db.disconnect()
		

if __name__ == '__main__':
	unittest.main()
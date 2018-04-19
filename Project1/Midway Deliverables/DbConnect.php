<?php

// Class to abstract the PHP <--> MySQL database connection
// and provide some simple querying tools
class Database {
	var $m_conn;
	var $m_tblname;
	
	var $m_db;
	var $m_dbname;
	var $m_user;
	var $m_pass;
	
	/* 
	Database(String db, String dbname, String user, String pass)
	constructor + connection
	
	Preconditions:
	All inputs are strings
	
	Postconditions:
	Database is ready to use (or exception thrown)
	
	Exceptions:
	Generic Exception - from PDOException on connection error (propagated from Connect())
	*/
	function Database($db, $dbname, $user, $pass)
	{
		$this->m_db = $db;
		$this->m_dbname = $dbname;
		$this->m_user = $user;
		$this->m_pass = $pass;
		return $this->connect();
	}
	
	/*
	Connect()
	connect to the database given the database parameters
	
	Preconditions:
	Constructor has been called with correct values
	
	Postconditions:
	Database connection is valid (or exception thrown)
	
	Exceptions:
	Generic Exception - from PDOException on connection error
	*/
	function Connect()
	{
		try
		{
			$this->m_conn = new PDO('mysql:host='.$this->m_db.';dbname='.$this->m_dbname, $this->m_user, $this->m_pass);
		} catch (PDOException $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	/*
	SetTable(String tblname)
	set the current table name
	
	Preconditions:
	None
	
	Postconditions:
	m_tblname is set to given value
	
	Exceptions:
	None
	*/
	function SetTable($tblname)
	{
		$this->m_tblname = $tblname;
	}
	
	/*
	array GeneralQuery(String sql)
	given a query, execute it on the server
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	Query is executed (or exception thrown)
	Returns array of rows, which are arrays by key
	
	Exceptions:
	Generic Exception - invalid query or fetching error
	*/
	function GeneralQuery($sql)
	{
		$res = $this->m_conn->query($sql);
		if (!$res) throw new Exception("Cannot execute query $sql");
		
		$rows = array();
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			if (!$row) throw new Exception("Error fetching results from query $sql");
			array_push($rows, $row);
		}
		
		return $rows;
	}
	
	/*
	int GetTotal()
	get the total number of records in the table
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	Query is executed (or exception thrown)
	Returns value of count result
	
	Exceptions:
	Generic Exception - invalid query or fetching error
	*/
	function GetTotal()
	{
		$sql = "SELECT count(*) FROM `$this->m_tblname` WHERE 1";
		
		$res = $this->m_conn->query($sql);
		if (!$res) throw new Exception('Cannot execute query GetTotal()');
		
		$count = $res->fetch(PDO::FETCH_NUM);
		if (!$count) throw new Exception('Error fetching results from GetTotal()');
		
		return $count[0];
	}
	
	/*
	int GetTotalRange(String rangeCol, String low, String high)
	get the total number of records in the table within a certain range
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	Query is executed (or exception thrown)
	Returns value of count result
	
	Exceptions:
	Generic Exception - range error, invalid query, or fetching error
	*/
	function GetTotalRange($rangeCol, $low, $high)
	{
		if (strcmp($low, $high) > 0) throw new Exception("Invalid range $low, $high");
		
		$sql = "SELECT count(*) FROM `$this->m_tblname` WHERE $rangeCol BETWEEN $low AND $high";
		
		$res = $this->m_conn->query($sql);
		if (!$res) throw new Exception("Cannot execute query GetTotalRange($rangeCol, $low, $high)");
		
		$count = $res->fetch(PDO::FETCH_NUM);
		if (!$count) throw new Exception("Error fetching results from GetTotalRange($rangeCol, $low, $high)");
		
		return $count[0];
	}
}

?>
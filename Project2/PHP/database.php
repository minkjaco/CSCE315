<?php
//----------------------------------------------------------
// File: database.php
// Project: CSCE 315 Project 2, Spring 2018
//
// This file contains the database class that allows the creation
// of an object to communicate with the MySQL database. An SQL query
// can be issued and other modifications to the traffic_data table
// can be made.
//----------------------------------------------------------

class Database
{
	var $db="database.cs.tamu.edu";
	var $dbname="darwin.stephanus31";
	var $user="darwin.stephanus31";
	var $pass="Winwinwin25";
	var $table = "traffic";
	var $connection;
	//----------------------------------------------------------
	// Function: Database
	// Precondition: Constructor call
	// Postcondition: Creates database object with established connection
	//----------------------------------------------------------
	public function Database()
	{
		$this->connection = new mysqli($this->db, $this->user, $this->pass, $this->dbname);
		if($this->connection->connect_error){
			die("Connection failed: " . $this->connection->connect_error);
		}
	}
	
	//----------------------------------------------------------
	// Function: SRQuery
	// Precondition: An SQL query that will return a single result
	// Postcondition: Returns the resulting value
	//----------------------------------------------------------
	public function SRQuery($sql)
	{
		$value;
		$index = substr($sql,7,strpos($sql,"FROM")-8);
		$result = $this->connection->query($sql);
		$row = $result->fetch_assoc();
		$value = $row[$index];
		return $value;
	}
	
	//----------------------------------------------------------
	// Function: insert
	// Precondition: A Y-m-d h-m-s date and the number to be inserted
	// 				 if "default" is substituted for the data, it will use
	//				 the current time.
	// Postcondition: Inserts given timestamp the number of times into the database
	//----------------------------------------------------------
	public function insert($date,$num)
	{
		if($date != "default")
			$sql = "INSERT INTO `".$this->dbname."`.`".$this->table."` (`time`) VALUES ('".$date."')";
		else $sql = "INSERT INTO `".$this->dbname."`.`".$this->table."` (`time`) VALUES (CURRENT_TIMESTAMP)";
		for($i = 0; $i < $num; $i++)
		{
			if($this->connection->query($sql) !== TRUE)
				echo("error");
		}
	}
	//----------------------------------------------------------
	// Function: clearTable
	// Precondition: No precondition
	// Postcondition: Clears all data in the database
	//----------------------------------------------------------
	public function clearTable()
	{
		$sql = "TRUNCATE TABLE `".$this->dbname."`.`".$this->table."`"; 
		if($this->connection->query($sql) !== TRUE)
				echo("error");
	}
}

?>
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
	
	function GetDataPointsInRange($rangeCol, $lows, $highs)
	{
		if (count($lows) != count($highs)) {
			throw new Exception("Dimension of range arrays do not agree");
		}
		$datapoints = array();
		foreach (range(0, count($lows)) as $i) {
			$count = GetTotalRange($rangeCol, $lows[$i], $highs[$i]);
			array_push($datapoints, $count);
		}
		return $datapoints;
	}
	
	function PrintDataPoints($rangeCol, $lows, $highs)
	{
		$datapoints = GetDataPointsInRange($rangeCol, $lows, $highs);
		echo ("<table>\n");
		echo ("<tr>\n<th>Low</th>\n<th>High</th>\n<th>Count</th>\n</tr>\n");
		foreach (range(0, count($datapoints)) as $i) {
			echo ("<tr><td>$lows[$i]</td><td>$highs[$i]</td><td>$datapoints[$i]</td></tr>\n");
		}
		echo ("</table>\n");
	}
	
	function Multi_GetTotalRange($rangeCols, $lows, $highs)
	{
		$sql = "SELECT count(*) FROM `$this->m_tblname` WHERE ";
		foreach (range(0, count($rangeCols)-1) as $i)
		{
			$sql .= "($rangeCols[$i] BETWEEN $lows[$i] AND $highs[$i]) AND ";
		}
		$sql = substr($sql, 0, -5);
		
		$res = $this->m_conn->query($sql);
		if (!$res) throw new Exception("Cannot execute query Multi_GetTotalRange($rangeCols, $lows, $highs)");
		
		$count = $res->fetch(PDO::FETCH_NUM);
		if (!$count) throw new Exception("Error fetching results from GetTotalRange($rangeCol, $low, $high)");
		
		return $count[0];
	}
	
	function Multi_PrintDataPoints($rangeCols, $lowsS, $highsS)
	{
		$datapoints = array();
		foreach (range(0, count($lowsS)-1) as $i)
		{
			$data = $this->Multi_GetTotalRange($rangeCols, $lowsS[$i], $highsS[$i]);
			array_push($datapoints, $data);
		}
		
		echo("<h2>Data Table</h2>\n");
		echo("<table>\n");
		echo("<tr>\n");
		foreach ($rangeCols as $col) 
		{
			echo("<th>$col Low</th><th>$col High</th>");
		}
		echo("<th>Count</th>\n");
		echo("</tr>\n");
		foreach (range(0, count($lowsS)-1) as $j) {
			echo ("<tr>");
			foreach (range(0, count($rangeCols)-1) as $i) {
				echo('<td>'.$lowsS[$j][$i].'</td><td>'.$highsS[$j][$i].'</td>');
			}
			echo('<td>'.$datapoints[$j].'</td>\n');
			echo("</tr>\n");
		}
		echo ("</table>\n");
	}
	
	function Multi_AverageInRange($rangeCols, $lows, $highs)
	{
		$sql = "SELECT AVG(COUNT) FROM (SELECT COUNT(*) AS COUNT FROM `$this->m_tblname` WHERE ";
		foreach (range(0, count($rangeCols)-1) as $i)
		{
			$sql .= "($rangeCols[$i] BETWEEN $lows[$i] AND $highs[$i]) AND ";
		}
		$sql = substr($sql, 0, -5);
		$sql .= " GROUP BY ";
		foreach (range(0, count($rangeCols)-1) as $i)
		{
			$sql .= "CAST($rangeCols[$i] AS DATE), ";
		}
		$sql = substr($sql, 0, -2);
		$sql .= ") AS COUNTS";
		
		$res = $this->m_conn->query($sql);
		if (!$res) throw new Exception("Cannot execute query Multi_AverageInRange($rangeCols, $lows, $highs)");
		
		$avg = $res->fetch(PDO::FETCH_NUM);
		if (!$avg) throw new Exception("Error fetching results from Multi_AverageInRange($rangeCol, $low, $high)");
		
		return $avg[0];
	}
	
	function AverageInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		$current_low = date("Y-m-d H:i:s", strtotime("+$hour_low hours", strtotime($low)));
		$current_high = date("Y-m-d H:i:s", strtotime("+$hour_high hours", strtotime($low)));
		
		$sum = 0;
		$count = 0;
		
		while ($current_high <= $high) {
			$sum += $this->GetTotalRange($rangeCol, "'" . $current_low . "'", "'" . $current_high . "'");
			$count += 1;
			$current_low = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_low)));
			$current_high = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_high)));
		}
		
		return $sum / $count;
	}
	
	function AverageInHourRanges($rangeCol, $low, $high, $hour_lows, $hour_highs)
	{
		$data = array();
		foreach (range(0, count($hour_lows)-1) as $i) {
			array_push($data, $this->AverageInHourRange($rangeCol, $low, $high, $hour_lows[$i], $hour_highs[$i]));
		}
		return $data;
	}
	
	function MaxInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		$current_low = date("Y-m-d H:i:s", strtotime("+$hour_low hours", strtotime($low)));
		$current_high = date("Y-m-d H:i:s", strtotime("+$hour_high hours", strtotime($low)));
		
		$max = ~PHP_INT_MAX;
		
		while ($current_high <= $high) {
			$temp = $this->GetTotalRange($rangeCol, "'" . $current_low . "'", "'" . $current_high . "'");
			if ($temp > $max)
				$max = $temp;
			$current_low = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_low)));
			$current_high = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_high)));
		}
		
		return $max;
	}
	
	function MaxInHourRanges($rangeCol, $low, $high, $hour_lows, $hour_highs)
	{
		$data = array();
		foreach (range(0, count($hour_lows)-1) as $i) {
			array_push($data, $this->MaxInHourRange($rangeCol, $low, $high, $hour_lows[$i], $hour_highs[$i]));
		}
		return $data;
	}
	
	function MinInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		$current_low = date("Y-m-d H:i:s", strtotime("+$hour_low hours", strtotime($low)));
		$current_high = date("Y-m-d H:i:s", strtotime("+$hour_high hours", strtotime($low)));
		
		$min = PHP_INT_MAX;
		
		while ($current_high <= $high) {
			$temp = $this->GetTotalRange($rangeCol, "'" . $current_low . "'", "'" . $current_high . "'");
			if ($temp < $min)
				$min = $temp;
			$current_low = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_low)));
			$current_high = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_high)));
		}
		
		return $min;
	}
	
	function MinInHourRanges($rangeCol, $low, $high, $hour_lows, $hour_highs)
	{
		$data = array();
		foreach (range(0, count($hour_lows)-1) as $i) {
			array_push($data, $this->MinInHourRange($rangeCol, $low, $high, $hour_lows[$i], $hour_highs[$i]));
		}
		return $data;
	}
}

?>
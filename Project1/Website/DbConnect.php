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
	
	/*
	int[] GetDataPointsInRange(String rangeCol, String[] lows, String[] highs)
	get the records in the table within a certain range
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	Query is executed (or exception thrown)
	Returns value of count result
	
	Exceptions:
	Generic Exception - range error, invalid query, or fetching error
	*/
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
	
	/*
	int PrintDataPoints(String rangeCol, String[] lows, String[] highs)
	display the records in a table format
	
	Preconditions:
	Database is connected
	SetTable has been called
	Some form of table styling is defined
	
	Postconditions:
	Displays table in HTML
	
	Exceptions:
	Generic Exception - range error
	*/
	function PrintDataPoints($rangeCol, $lows, $highs)
	{
		if (count($lows) != count($highs)) {
			throw new Exception("Dimension of range arrays do not agree");
		}
		$datapoints = GetDataPointsInRange($rangeCol, $lows, $highs);
		echo ("<table>\n");
		echo ("<tr>\n<th>Low</th>\n<th>High</th>\n<th>Count</th>\n</tr>\n");
		foreach (range(0, count($datapoints)) as $i) {
			echo ("<tr><td>$lows[$i]</td><td>$highs[$i]</td><td>$datapoints[$i]</td></tr>\n");
		}
		echo ("</table>\n");
	}
	
	/*
	int Multi_GetTotalRange(String[] rangeCols, String[] lows, String[] highs)
	Get the total number of records across several ranges
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	Returns count across each range as a single number
	
	Exceptions:
	Generic Exception - range error
	*/
	function Multi_GetTotalRange($rangeCols, $lows, $highs)
	{
		if (count($lows) != count($highs)) {
			throw new Exception("Dimension of range arrays do not agree");
		}
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
	
	/*
	int Multi_PrintDataPoints(String[] rangeCols, String[] lows, String[] highs)
	display the records in a table format from several ranges
	
	Preconditions:
	Database is connected
	SetTable has been called
	Some form of table styling is defined
	
	Postconditions:
	Displays table in HTML
	
	Exceptions:
	Generic Exception - range error
	*/
	function Multi_PrintDataPoints($rangeCols, $lowsS, $highsS)
	{
		if (count($lowsS) != count($highsS)) {
			throw new Exception("Dimension of range arrays do not agree");
		}
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
			echo('<td>'.$datapoints[$j].'</td>');
			echo("</tr>\n");
		}
		echo ("</table>\n");
	}
	
	/*
	int Multi_AverageInRange(String[] rangeCols, String[] lows, String[] highs)
	Get the average number of records from several ranges
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	Returns averages
	
	Exceptions:
	Generic Exception - range error
	*/
	function Multi_AverageInRange($rangeCols, $lows, $highs)
	{
		if (count($lows) != count($highs)) {
			throw new Exception("Dimension of range arrays do not agree");
		}
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
	
	/*
	int AverageInHourRange(String rangeCol, String low, String high, int hour_low, int hour_high)
	Get the average number of records from low to high, between hour_low and hour_high
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the average number of records
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function AverageInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		if (strcmp($low > $high) > 0) {
			throw new Exception("Dates must be chronological");
		}
		if (strcmp($hour_low, $hour_high) > 0) {
			throw new Exception("Hours must be chronological");
		}
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
	
	/*
	int AverageInHourRanges(String rangeCol, String low, String high, int[] hour_lows, int[] hour_highs)
	Get the average number of records from low to high, between hour_lows and hour_highs as an array
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the average number of records
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function AverageInHourRanges($rangeCol, $low, $high, $hour_lows, $hour_highs)
	{
		$data = array();
		foreach (range(0, count($hour_lows)-1) as $i) {
			try {
				array_push($data, $this->AverageInHourRange($rangeCol, $low, $high, $hour_lows[$i], $hour_highs[$i]));
			} catch (Exception $e) {
				throw new Exception($e->GetMessage());
			}
		}
		return $data;
	}
	
	/*
	int MaxInHourRange(String rangeCol, String low, String high, int hour_low, int hour_high)
	Get the max number of records from dates low to high, between hour_low and hour_high
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the max number of records
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function MaxInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		if (strcmp($low > $high) > 0) {
			throw new Exception("Dates must be chronological");
		}
		if (strcmp($hour_low, $hour_high) > 0) {
			throw new Exception("Hours must be chronological");
		}
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
	
	/*
	int MaxInHourRanges(String rangeCol, String low, String high, int[] hour_lows, int[] hour_highs)
	Get the max number of records from dates low to high, between hour_lows and hour_highs as an array
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the max number of records as an array
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function MaxInHourRanges($rangeCol, $low, $high, $hour_lows, $hour_highs)
	{
		$data = array();
		foreach (range(0, count($hour_lows)-1) as $i) {
			try {
				array_push($data, $this->MaxInHourRange($rangeCol, $low, $high, $hour_lows[$i], $hour_highs[$i]));
			} catch (Exception $e) {
				throw new Exception($e->GetMessage());
			}
		}
		return $data;
	}
	
	/*
	int MinInHourRange(String rangeCol, String low, String high, int hour_low, int hour_high)
	Get the min number of records from dates low to high, between hour_low and hour_high
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the min number of records
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function MinInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		if (strcmp($low > $high) > 0) {
			throw new Exception("Dates must be chronological");
		}
		if (strcmp($hour_low, $hour_high) > 0) {
			throw new Exception("Hours must be chronological");
		}
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
	
	/*
	int MinInHourRanges(String rangeCol, String low, String high, int[] hour_lows, int[] hour_highs)
	Get the min number of records from dates low to high, between hour_lows and hour_highs as an array
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the min number of records as an array
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function MinInHourRanges($rangeCol, $low, $high, $hour_lows, $hour_highs)
	{
		$data = array();
		foreach (range(0, count($hour_lows)-1) as $i) {
			try {
				array_push($data, $this->MinInHourRange($rangeCol, $low, $high, $hour_lows[$i], $hour_highs[$i]));
			} catch (Exception $e) {
				throw new Exception($e->GetMessage());
			}
		}
		return $data;
	}
	
	/*
	int MedianInHourRange(String rangeCol, String low, String high, int hour_low, int hour_high)
	Get the median number of records from dates low to high, between hour_low and hour_high
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the median number of records
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function MedianInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		if (strcmp($low > $high) > 0) {
			throw new Exception("Dates must be chronological");
		}
		if (strcmp($hour_low, $hour_high) > 0) {
			throw new Exception("Hours must be chronological");
		}
		$current_low = date("Y-m-d H:i:s", strtotime("+$hour_low hours", strtotime($low)));
		$current_high = date("Y-m-d H:i:s", strtotime("+$hour_high hours", strtotime($low)));
		
		$data = array();
		
		$answer = 0;
		
		while ($current_high <= $high) {
			array_push($data, $this->GetTotalRange($rangeCol, "'" . $current_low . "'", "'" . $current_high . "'"));
			$current_low = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_low)));
			$current_high = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_high)));
		}
		
		asort($data);
		$count = count($data);
		$mid = floor(($count - 1)/2);
		if(count($data)%2 == 0){
			$low = $data[$mid];
			$high = $data[$mid + 1];
			$answer = (($low + $high) / 2);
		}
		else{
			$answer = $data[$mid];
		}
		
		return $answer;
	}
	
	/*
	int ModeInHourRange(String rangeCol, String low, String high, int hour_low, int hour_high)
	Get the mode number of records from dates low to high, between hour_low and hour_high
	
	Preconditions:
	Database is connected
	SetTable has been called
	
	Postconditions:
	returns the mode number of records
	
	Exceptions:
	Generic Exception - range error on dates or hours
	*/
	function ModeInHourRange($rangeCol, $low, $high, $hour_low, $hour_high)
	{
		if (strcmp($low > $high) > 0) {
			throw new Exception("Dates must be chronological");
		}
		if (strcmp($hour_low, $hour_high) > 0) {
			throw new Exception("Hours must be chronological");
		}
		$current_low = date("Y-m-d H:i:s", strtotime("+$hour_low hours", strtotime($low)));
		$current_high = date("Y-m-d H:i:s", strtotime("+$hour_high hours", strtotime($low)));
		
		$data = array();
		
		$answer = 0;
		
		while ($current_high <= $high) {
			array_push($data, $this->GetTotalRange($rangeCol, "'" . $current_low . "'", "'" . $current_high . "'"));
			$current_low = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_low)));
			$current_high = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($current_high)));
		}
		
		asort($data);
		
		$values = array_count_values($data); 
		$answer = array_search(max($values), $values);
		
		return $answer;
	}
}

?>
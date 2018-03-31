<html>
<head>
<title>Database Unit Tests</title>
<style>
table {
	font-family: arial, sans-serif;
	border-collapse: collapse;
	width: 100%;
}
td, th {
	border: 1px solid #dddddd;
	text-align: left;
	padding: 8px;
}

tr:nth-child(even) {
	background-color: #dddddd;
}
</style>
</head>
<body>

<?php

include('DbConnect.php');

echo("Test 1: Invalid Database constructor input<br>\n");
echo("Expect: Exception from incorrect password<br>\n");
$db;
try {
	$db = new Database('database.cse.tamu.edu', 'minkjaco', 'minkjaco', 'abcd');
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");

echo("Test 2: Valid Database constructor input<br>\n");
echo("Expect: \$db is not null<br>\n");
try {
	$db = new Database('database.cse.tamu.edu', 'minkjaco', 'minkjaco', 'jacobmink123');
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo('$db result: '.$db->m_dbname."<br>\n");
echo("<br><br><br>\n");

echo("Test 3: Setting the table<br>\n");
echo("Expect: \$db->m_tblname is not null\n");
$db->SetTable('Test');
echo('$db->m_tblname result: '.$db->m_tblname."<br>\n");
echo("<br><br><br>\n");

echo("Test 4: Check that total data gives expected result<br>\n");
echo("Expect: $data = 3<br>\n");
$data;
try {
	$data = $db->GetTotal();
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo('$data: '.$data."<br>\n");
echo("<br><br><br>\n");

echo("Test 5: Invalid range input (missing ') to GetTotalRange()<br>\n");
echo("Expect: Exception thrown<br>\n");
try {
	$data = $db->GetTotalRange('Time', "'2012-02-11 00:00:00", "'2018-02-23 03:47:00'");
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");

echo("Test 6: Invalid range input (low > high) to GetTotalRange()<br>\n");
echo("Expect: Exception thrown<br>\n");
try {
	$data = $db->GetTotalRange('Time', "'2019-02-11 00:00:00'", "'2018-02-23 03:47:00'");
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");

echo("Test 7: Check that total data in range gives expected result<br>\n");
echo("Expect: \$data = 1<br>\n");
try {
	$data = $db->GetTotalRange('Time', "'2012-02-11 00:00:00'", "'2018-02-23 03:47:00'");
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo('$data: '.$data."<br>\n");
echo("<br><br><br>\n");

echo("Test 8: Invalid SQL statement<br>\n");
echo("Expect: Exception thrown<br>\n");
try {
	$sql = "SELECT + FROM `Test`";
	$data = $db->GeneralQuery($sql);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");

echo("Test 9: Check that we get the expected result<br>\n");
echo("Expect: \$data = 3<br>\n");
try {
	$sql = "SELECT COUNT(*) FROM `Test`";
	$data = $db->GeneralQuery($sql);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo('$data: '.$data[0]['COUNT(*)']."<br>\n");
echo("<br><br><br>\n");

echo("Test 10: Check that total data in range gives expected result<br>\n");
echo("Expect: \$data = 1<br>\n");
try {
	$data = $db->Multi_GetTotalRange(array("Time"), array("'2018-03-20 00:00:00'"), array("'2018-03-21 00:00:00'"));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo('$data: '.$data."<br>\n");
echo("<br><br><br>\n");

echo("Test 11: Print data<br>\n");
try {
	$data = $db->Multi_PrintDataPoints(array("Time"), array(array("'2018-03-20 00:00:00'")), array(array("'2018-03-23 00:00:00'")));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");

echo("Test 12: Get average in range<br>\n");
try {
	$data = $db->AverageInHourRange("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", 13, 15);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("Data: $data<br>\n");
echo("<br><br><br>\n");


echo("Test 13: Get average in ranges<br>\n");
try {
	$data = $db->AverageInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");


echo("Test 14: Get max in ranges<br>\n");
try {
	$data = $db->MaxInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");

echo("Test 15: Get mode in ranges<br>\n");
try {
	$data = $db->MaxInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");

echo("Test 16: Get median in ranges(even number of entries)<br>\n");
try {
	$data = $db->MedianInHourRange("Time", "2018-03-20 00:00:00", "2018-03-21 00:00:00", 13, 15);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("Data: $data<br>\n");
echo("<br><br><br>\n");


echo("Test 17: Get median in ranges(odd number of entries)<br>\n");
try {
	$data = $db->MedianInHourRange("Time", "2018-03-27 00:00:00", "2018-03-29 00:00:00", 15, 19);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("Data: $data<br>\n");
echo("<br><br><br>\n");

?>

</body>
</html>
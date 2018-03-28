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
date_default_timezone_set('America/Chicago');

$db = new Database('database.cse.tamu.edu', 'minkjaco', 'minkjaco', 'jacobmink123');
$db->setTable('Test2');

echo("Test 1: Check that total data in range gives expected result<br>\n");
echo("Expect: \$data = 1<br>\n");
try {
	$data = $db->Multi_GetTotalRange(array("Year", "Month", "Day"), array(2018, 3, 20), array(2018, 3, 21));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo('$data: '.$data."<br>\n");
echo("<br><br><br>\n");

echo("Test 2: Attempt print<br>\n");

try {
	$data = $db->Multi_PrintDataPoints(array("Year", "Month", "Day"), array(array(2018, 3, 20), array(2019, 3, 22), array(2020, 21, 22)), array(array(2018, 3, 21), array(2020, 5, 6), array(2021, 22, 23)));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");


echo("Using Table `Test`<br>\n");
$db->setTable('Test');

echo("Test 3: Get average in range<br>\n");
try {
	$data = $db->AverageInHourRange("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", 13, 15);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("Data: $data<br>\n");
echo("<br><br><br>\n");


echo("Test 4: Get average in ranges<br>\n");
try {
	$data = $db->AverageInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");


echo("Test 5: Get max in ranges<br>\n");
try {
	$data = $db->MaxInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");



echo("Test 5: Get min in ranges<br>\n");
try {
	$data = $db->MinInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");

echo("Test 5: Print data<br>\n");
try {
	$data = $db->Multi_PrintDataPoints(array("Time"), array(array("'2018-03-20 00:00:00'")), array(array("'2018-03-23 00:00:00'")));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("<br><br><br>\n");

echo("Test 6: Get mode in ranges<br>\n");
try {
	$data = $db->MaxInHourRanges("Time", "2018-02-20 00:00:00", "2018-03-22 00:00:00", array(13, 15, 17), array(15, 17, 19));
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
foreach ($data as $d) {
	echo("Data: $d<br>\n");
}
echo("<br><br><br>\n");

echo("Test 7: Get median in ranges(even number of entries)<br>\n");
try {
	$data = $db->MedianInHourRange("Time", "2018-03-20 00:00:00", "2018-03-21 00:00:00", 13, 15);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("Data: $data<br>\n");
echo("<br><br><br>\n");


echo("Test 7: Get median in ranges(odd number of entries)<br>\n");
try {
	$data = $db->MedianInHourRange("Time", "2018-03-27 00:00:00", "2018-03-29 00:00:00", 15, 19);
} catch (Exception $e) {
	echo($e->getMessage()."<br>\n");
}
echo("Data: $data<br>\n");
echo("<br><br><br>\n");


?>
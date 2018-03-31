<html>
<head>
<title>PeopleCounter Results</title>
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

function printReturn() {
	echo("<br><br>\n");
	echo("<form action=\"/XXXXX/Project/UIPage.php\">\n");
	echo("<input type=\"submit\" value=\"Return\">\n");
	echo("</form>");
}

?>


<?php
include('DbConnect.php');

$db = new Database('database.cse.tamu.edu', 'XXXXX', 'XXXXX', 'XXXXX');
$db->setTable('Test');

$dt_low = $_POST['year_low'] . '-' . str_pad($_POST['month_low'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['day_low'], 2, "0", STR_PAD_LEFT) . 'T' . str_pad($_POST['hour_low'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($_POST['minute_low'], 2, "0", STR_PAD_LEFT);
$dt_high = $_POST['year_high'] . '-' . str_pad($_POST['month_high'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['day_high'], 2, "0", STR_PAD_LEFT) . 'T' . str_pad($_POST['hour_high'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($_POST['minute_high'], 2, "0", STR_PAD_LEFT);
var_dump($dt_low, $dt_high);

if(isset($_POST['count']))
{
	try {
		$data = $db->GetTotalRange("Time", "'$dt_low'", "'$dt_high'");
	} catch (Exception $e) {
		echo($e->GetMessage() . '<br>\n');
	}
	
	echo("<h1>Total Entry</h1>\n");
	echo("From $dt_low to $dt_high:<br>\n");
	echo("Count = $data<br>\n");
	printReturn();
}
else if(isset($_POST['average']))
{
	$hour_low = split(":", split("T", $dt_low)[1])[0];
	$hour_high = split(":", split("T", $dt_high)[1])[0];

	$dt_low = split("T", $dt_low)[0] . " 00:00:00";
	$dt_high = split("T", $dt_high)[0] . " 00:00:00";
	try {
		$data = $db->AverageInHourRange("Time", "$dt_low", "$dt_high", $hour_low, $hour_high);
	} catch (Exception $e) {
		echo($e->GetMessage() . '<br>\n');
	}
	
	
	echo("<h1>Average Entry</h1>\n");
	echo("From $dt_low to $dt_high between $hour_low, $hour_high:<br>\n");
	echo("Count = $data<br>\n");
	printReturn();
}
else if(isset($_POST['max']))
{
	$hour_low = split(":", split("T", $dt_low)[1])[0];
	$hour_high = split(":", split("T", $dt_high)[1])[0];

	$dt_low = split("T", $dt_low)[0] . " 00:00:00";
	$dt_high = split("T", $dt_high)[0] . " 00:00:00";
	
	try {
		$data = $db->MaxInHourRange("Time", "$dt_low", "$dt_high", $hour_low, $hour_high);
	} catch (Exception $e) {
		echo($e->GetMessage() . '<br>\n');
	}
	
	
	echo("<h1>Max Entry</h1>\n");
	echo("From $dt_low to $dt_high between $hour_low, $hour_high:<br>\n");
	echo("Count = $data<br>\n");
	printReturn();
}
else if(isset($_POST['median']))
{
	$hour_low = split(":", split("T", $dt_low)[1])[0];
	$hour_high = split(":", split("T", $dt_high)[1])[0];

	$dt_low = split("T", $dt_low)[0] . " 00:00:00";
	$dt_high = split("T", $dt_high)[0] . " 00:00:00";
	
	try {
		$data = $db->MedianInHourRange("Time", "$dt_low", "$dt_high", $hour_low, $hour_high);
	} catch (Exception $e) {
		echo($e->GetMessage() . '<br>\n');
	}
	
	
	echo("<h1>Median Entry</h1>\n");
	echo("From $dt_low to $dt_high between $hour_low, $hour_high:<br>\n");
	echo("Count = $data<br>\n");
	printReturn();
}
else if(isset($_POST['mode']))
{
	$hour_low = split(":", split("T", $dt_low)[1])[0];
	$hour_high = split(":", split("T", $dt_high)[1])[0];

	$dt_low = split("T", $dt_low)[0] . " 00:00:00";
	$dt_high = split("T", $dt_high)[0] . " 00:00:00";
	
	try {
		$data = $db->ModeInHourRange("Time", "$dt_low", "$dt_high", $hour_low, $hour_high);
	} catch (Exception $e) {
		echo($e->GetMessage() . '<br>\n');
	}
	
	
	echo("<h1>Mode Entry</h1>\n");
	echo("From $dt_low to $dt_high between $hour_low, $hour_high:<br>\n");
	echo("Count = $data<br>\n");
	printReturn();
}
else if(isset($_POST['min']))
{
	$hour_low = split(":", split("T", $dt_low)[1])[0];
	$hour_high = split(":", split("T", $dt_high)[1])[0];

	$dt_low = split("T", $dt_low)[0] . " 00:00:00";
	$dt_high = split("T", $dt_high)[0] . " 00:00:00";
	
	try {
		$data = $db->MinInHourRange("Time", "$dt_low", "$dt_high", $hour_low, $hour_high);
	} catch (Exception $e) {
		echo($e->GetMessage() . '<br>\n');
	}
	
	
	echo("<h1>Min Entry</h1>\n");
	echo("From $dt_low to $dt_high between $hour_low, $hour_high:<br>\n");
	echo("Count = $data<br>\n");
	printReturn();
}
else if(isset($_POST['data']))
{
	$dt_low = str_replace("T", " ", $dt_low) . ":00";
	$dt_high = str_replace("T", " ", $dt_high) . ":00";
	
	
	$db->Multi_PrintDataPoints(array("Time"), array(array("'$dt_low'")), array(array("'$dt_high'")));
	printReturn();
}

?>

</body>
</html>
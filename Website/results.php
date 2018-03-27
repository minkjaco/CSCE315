<html>
<head>
<title>PeopleCounter Results</title>
</head>
<body>

<?php

function printReturn() {
	echo("<br><br>\n");
	echo("<form action=\"/minkjaco/Project/UIPage.php\">\n");
	echo("<input type=\"submit\" value=\"Return\">\n");
	echo("</form>");
}

?>


<?php
include('DbConnect.php');

$db = new Database('database.cse.tamu.edu', 'minkjaco', 'minkjaco', 'jacobmink123');
$db->setTable('Test');

$low = 'low';
$high = 'high';

if(empty($_POST[$low]) || empty($_POST[$high]))
{
	echo("No range set!\n");
	printReturn();
}
else if(isset($_POST['count']))
{
	$dt_low = split("T", $_POST[$low])[0] . ' ' . split("T", $_POST[$low])[1] . ':00';
	$dt_high = split("T", $_POST[$high])[0] . ' ' . split("T", $_POST[$high])[1] . ':00';

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
	echo("Not yet implemented\n");
	printReturn();
}
else if(isset($_POST['max']))
{
	echo("Not yet implemented\n");
	printReturn();
}
else if(isset($_POST['median']))
{
	echo("Not yet implemented\n");
	printReturn();
}
else if(isset($_POST['mode']))
{
	echo("Not yet implemented\n");
	printReturn();
}
else if(isset($_POST['min']))
{
	echo("Not yet implemented\n");
	printReturn();
}
else if(isset($_POST['data']))
{
	$dt_low = split("T", $_POST[$low])[0] . ' ' . split("T", $_POST[$low])[1] . ':00';
	$dt_high = split("T", $_POST[$high])[0] . ' ' . split("T", $_POST[$high])[1] . ':00';
	
	var_dump($dt_low, $dt_high);
	
	$db->Multi_PrintDataPoints(array("Time"), array("'$dt_low'"), array("'$dt_high'"));
	printReturn();
}

?>

</body>
</html>
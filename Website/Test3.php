<html>
<head>
<title>Database Unit Tests</title>
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

?>

</body>
</html>
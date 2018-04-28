<html>
<head>

</head>
<body>

<?php
include('database.php');

$db = new Database();
try {
	$db->Database();
} catch (Exception $e) {
	echo("Error connecting to database: $e->GetMessage()<br>\n");
}

print_r($_POST);

if (isset($_POST['string'])) {
	echo($_POST['string']);
	$db->genericQuery($_POST['string'], 1);
}
unset($_POST['string']);
?>

</html>
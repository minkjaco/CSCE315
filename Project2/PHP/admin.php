<?php 
//----------------------------------------------------------
// File: admin.php
// Project: CSCE 315 Project 2, Spring 2018
//
// This is the admin page. It lets the user clear traffic data
// and add entries to the MySQL table. 
//----------------------------------------------------------
include('head.html');
include('database.php');
?>
<head>
	<title>Admin Page</title>
</head>
<body>
	<h1>Administrator Options</h1>
	<div style = "text-align:center">
	<form action="index.php">
		<input type="submit" value="Return to Home Page" class="admin_option">
	</form>
	<form action="data.php">
		<input type="submit" value="Return to Traffic Data" class="admin_option">
	</form>
	<form action="admin.php" method="post">
		<input type="hidden" name="insert" value="go">
		<input type="submit" value="Insert Traffic Entry" class="admin_option">
	</form>
	
	
	
<?php
if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['insert']))
{
	$connect = new Database();
	$connect->insert("default",1);
}

?>

<form action="admin.php" method="post">
		<input type="hidden" name="reset" value="delete">
		<input type="submit" onclick="return confirm('WARNING: This will delete all recorded traffic data. Do NOT select OK unless you know what you are doing!')" value="Clear Traffic Data" class="admin_option">
</form>

<?php
if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['reset']))
{
	$connect = new Database();
	$connect->clearTable();
}
?>

</div>
</body>
</html>
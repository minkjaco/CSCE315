<html>
<head>
<title>PeopleCounter Web Interface</title>

<style>
input[type=submit] {
	width: 8em; heigh: 2em;
}
</style>

</head>

<body>
<h1>PeopleCounter</h1>
<form action="/minkjaco/Project/results.php" method="POST">
<h3>Select range:</h3>
<table>
<tr>
	<td>
	Year: 
	<select name="year_low">
	<?php 
	for ($i=2018; $i < 2099; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	Month: 
	<select name="month_low">
	<?php
	for ($i = 1; $i < 13; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	Day: 
	<select name="day_low">
	<?php
	for ($i = 1; $i < 32; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	Time: 
	<select name="hour_low">
	<?php
	for ($i = 0; $i < 24; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	:
	<select name="minute_low">
	<?php
	for ($i = 0; $i < 60; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	 to 
	</td>
		<td>
	Year: 
	<select name="year_high">
	<?php 
	for ($i=2018; $i <= 2099; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	Month: 
	<select name="month_high">
	<?php
	for ($i = 1; $i < 13; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	Day: 
	<select name="day_high">
	<?php
	for ($i = 1; $i < 32; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	Time: 
	<select name="hour_high">
	<?php
	for ($i = 0; $i < 24; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
	<td>
	:
	<select name="minute_high">
	<?php
	for ($i = 0; $i < 60; $i += 1) {
		echo("<option value=\"$i\">$i</option>");
	}
	?>
	</select>
	</td>
<tr>
</table>
<br>
<h3>Action:</h3>
<table>
<tr><td align="center"><input type="submit" name="count" value="Count"></td><td align="center"><input type="submit" name="average" value="Average"></td><td align="center"><input type="submit" name="max" value="Max"></td></tr>
<tr><td align="center"><input type="submit" name="median" value="Median"></td><td align="center"><input type="submit" name="mode" value="Mode"></td><td align="center"><input type="submit" name="min" value="Min"></td></tr>
<tr><td></td><td><input type="submit" name="data" value="Data Points"></td><td></td></tr>
</table>
</form>



</body>
</html>
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
<tr><td>Low:</td><td><input type="datetime-local" name="low"></td></tr>
<tr><td>High:</td><td><input type="datetime-local" name="high"></td></tr>
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
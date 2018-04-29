<?php 
//----------------------------------------------------------
// File: data.php
// Project: CSCE 315 Project 2, Spring 2018
//
// This file contains the main page for displaying traffic data.
// It will show total numbers and averages. It lets the user select
// a single date or date range to view data for. It also links 
// to the admin page.
//----------------------------------------------------------
include('head.html'); 
include('format.php');
include('database.php');
?>
<head>
<title>Traffic Data</title>
</head>
<body>
<h1>Traffic Data</h1>

<?php
$connect = new Database();
$table = "traffic";
$sql = "SELECT COUNT(*) FROM `".$table."` WHERE 1";
$totalCount = $connect->SRQuery($sql);
$sql2 = "SELECT COUNT(*) FROM `".$table."` WHERE Entering = 1";
$enterCount = $connect->SRQuery($sql2);
$sql3 = "SELECT COUNT(*) FROM `".$table."` WHERE Exiting = 1";
$exitCount = $connect->SRQuery($sql3);





if($totalCount == 0)
	echo("<div class=\"no_traffic_msg\">No Traffic Recorded</div>");
else{
	echo("<h2>");
		echo("Overall");
	echo("</h2>");
	echo("<div class=\"main\">");
	
		$sql = "SELECT TimeStamp FROM `".$table."` WHERE Entering='1' ORDER BY 'TimeStamp' LIMIT 1";
		//$sql = "SELECT TimeStamp FROM `".$table."` ORDER BY 'TimeStamp' LIMIT 1";
		$timestamp = $connect->SRQuery($sql); 
		$startDate = formatDate($timestamp);
		$startTime = formatTime($timestamp);
		$startDay = getDay($timestamp);
		$startMonth = getMonth($timestamp);
		$startYear = getYear($timestamp);
		echo("<p1>Total Since ".$startDate." ".$startTime."</p1>");
		echo("<span class=\"data_val\">".number_format($enterCount)."</span><br><br>");
		$start = strtotime($startYear."-".$startMonth."-".$startDay);
		$end = strtotime(date("Y-m-d"));
		$days_between = ceil(abs($end - $start) / 86400);
		$weeks_between = $days_between / 7;
		if($days_between > 1)
			{ $dailyAverage = $enterlCount / $days_between; }
		else { $dailyAverage = $enterCount; }
		if($days_between <= 1){ $days_between = 1; }
		$hourlyAverage = $enterCount / ($days_between * 24);
		if($weeks_between >= 1)
			{ $weeklyAverage = $enterCount / $weeks_between; }
		else { $weeklyAverage = $enterCount; }
		echo("<p1>Daily Average</p1>");
		echo("<span class=\"data_val\">".number_format($dailyAverage,2,'.','')."</span><br><br>");
		
		echo("<p1>Weekly Average</p1>");
		echo("<span class=\"data_val\">".number_format($weeklyAverage,2,'.','')."</span><br><br>");
		
		echo("<p1>Hourly Average</p1>");
		echo("<span class=\"data_val\">".number_format($hourlyAverage,2,'.','')."</span>");
	echo("</div>");
	echo("<h2>");
		echo("Specified Date");
	echo("</h2>");
echo("
<div class=\"main\">
<form action=\"data.php\" method=\"post\">
	<input name=\"date\" type=\"date\" class=\"form_entry\">
	<input type=\"submit\" value=\"Submit\" class=\"button\">
</form>
<br>
");

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["date"] != "") 
{
	$date = $_POST["date"];
	$sql = "SELECT COUNT(*) FROM `".$table."` WHERE TimeStamp LIKE '".$date."%' AND Entering = 1;";
	$selectedNum = $connect->SRQuery($sql);
	$hourlyAverage = $selectedNum / 24;
	echo("<p1>Traffic counted for ".formatDate($date)." </p1><span class=\"data_val\">".$selectedNum."</span><br><br>");
	echo("<p1>Hourly Average</p1>");
	echo("<span class=\"data_val\">".number_format($hourlyAverage,2,'.','')."</span>");
}else{ echo("<p1>Please select a valid date</p1>"); }


echo("</div>");

echo("<h2>");
	echo("Date Range");
echo("</h2>");
echo("
<div class=\"main\">
<form action=\"data.php\" method=\"post\">
	<p1>From </p1>
	<input name=\"firstDate\" type=\"date\" class=\"form_entry\">
	<p1> To </p1>
	<input name=\"secondDate\" type=\"date\" class=\"form_entry\">
	<input type=\"submit\" value=\"Submit\" class=\"button\">
</form>
<br>
");
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["firstDate"] != "" && $_POST["secondDate"] != "" && $_POST["firstDate"] < $_POST["secondDate"]) 
{
	$firstDate = $_POST["firstDate"];
	$secondDate = $_POST["secondDate"];
	
	
	
	$start = strtotime($firstDate);
	$end = strtotime($secondDate);
	$days_between = ceil(abs($end - $start) / 86400);
	$days = array();
	$counts = array();
	$sum = 0;
	$current = $start;
	for($i = 0; $i < $days_between + 1; $i++)
	{
		$days[$i] = formatDate(gmdate("Y-m-d",$current));
		$sql = "SELECT COUNT(*) FROM `".$table."` WHERE TimeStamp LIKE '".gmdate("Y-m-d",$current)."%' AND Entering=1;";
		$counts[$i] = $connect->SRQuery($sql);
		$sum += $counts[$i];
		$current = strtotime('+1 day',$current);
	}
	
	
	//HOURLY AND DAILY AVERAGES
	if($days_between > 1)
			{ $dailyAverage = $sum / $days_between; }
		else { $dailyAverage = $sum; }
	$weeks_between = $days_between / 7;
	if($weeks_between >= 1)
			{ $weeklyAverage = $sum / $weeks_between; }
		else { $weeklyAverage = $sum; }
	$temp = $days_between;
	if($days_between <= 1) { $temp = 1; } 
	$hourlyAverage = $sum / ($temp * 24);
	//HOURLY AND DAILY AVERAGES
	
	
	echo("<p1>Total traffic counted between ".formatDate($firstDate)." and ".formatDate($secondDate)." </p1><span class=\"data_val\">".$sum."</span><br><br>");
	echo("<p1>Daily Average</p1>");
	echo("<span class=\"data_val\">".number_format($dailyAverage,2,'.','')."</span><br><br>");
	echo("<p1>Weekly Average</p1>");
	echo("<span class=\"data_val\">".number_format($weeklyAverage,2,'.','')."</span><br><br>");
	echo("<p1>Hourly Average</p1>");
	echo("<span class=\"data_val\">".number_format($hourlyAverage,2,'.','')."</span><br><br>");
	echo("
<html>
  <head>
    <script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
    <script type=\"text/javascript\">
	var vag = 20000;
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Date', 'Traffic'],");
		for($i = 0; $i < $days_between + 1; $i++)
		{
			echo("['".$days[$i]."', ".$counts[$i]."],");
		}
echo("
        ]);

        var options = {
			fontSize: 16,
			fontName: 'Helvetica',
			color: '#646464',
			titleTextStyle: { 
				color: '#646464',
				fontName: 'Helvetica',
				fontSize: '20'
			},
			backgroundColor: '#efefef',	
			legend: { position: 'bottom' },
			hAxis: {
				textStyle: {
					color: '#646464',
					fontName: 'Helvetica',
					fontSize: 16
				},
				title: 'Dates',
				titleTextStyle: { 
				color: '#646464',
				fontName: 'Helvetica',
				fontSize: '17',
				italic: 0
				}
			},
			vAxis: {
				textStyle: {
					color: '#646464',
					fontName: 'Helvetica',
					fontSize: 16
				},
				baselineColor: '#646464',
				title: 'Traffic',
				titleTextStyle: { 
				color: '#646464',
				fontName: 'Helvetica',
				fontSize: '17',
				italic: 0
				},
				minValue: 0
			},
			series: [
			{color: 'red', visibleInLegend: false}
			],
			pointShape: 'diamond',
			pointSize: 4,
			title: 'Traffic from ".formatDate($firstDate)." to ".formatDate($secondDate)."',
			tooltip: {
				textStyle: {
					color: '#646464',
					bold: 0,
					fontSize: 16
				},
				backgroundColor: '#646464'
			}
				
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);jg
      }
    </script>
  </head>
  <body>
    <div id=\"curve_chart\" style=\"width: 80vw; height: 500px\"></div>
  </body>
</html>");
}else{ echo("<p1>Please select a valid range</p1>"); }

echo("</div>");

//1ST GRAPH CODE ENDS HERE

//SECOND GRAPH BELOW

echo("<h2>");
	echo("Date Range");
echo("</h2>");
echo("
<div class=\"main\">
<form action=\"data.php\" method=\"post\">
	<p1>From </p1>
	<input name=\"startDate\" type=\"date\" class=\"form_entry\">
	<p1> To Today</p1>
	<input type=\"submit\" value=\"Submit\" class=\"button\">
</form>
<br>
");

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["startDate"] != "") 
{
	$startDate = $_POST["startDate"];
	
	
	
	$start = strtotime($startDate);
	
	$evaluationDay = time();
	//$days_between = ceil(abs( time() - $start) / 86400);
	
	
	$averages = [[]];


	
	
	for($i = 0; $i < 7; $i++){
		
		$weeksTabulated = 0;
		
		$currHour = 0;
	
	
		while(ceil($evaluationDay - $start) >= 0){
			//check the hours (for loop)
			
			for($j = 0; $j < 24; $j++){
				$averages[$i[$j]] =  "SELECT COUNT(*) FROM `".$table."` WHERE ".$table->Timestamp." BETWEEN '".$evaluationDay.$j.".:00:00' AND '".$evaluationDay.".".($j+1).".:00:00'";
				
			}
		
			
		
			//add to counter
			$weeksTabulated++;
		
			//decrement backwards
			$evaluationDay->modify('-1 week');
		
		
		}
		for($j = 0; $j < 24; $j++){
			$averages[$i[$j]] = $averages[$i[$j]]/$weeksTabulated;
		}
	
	
		
	}
	
	$days = array();
	$counts = array();
	$sum = 0;
	$current = $start;
	for($i = 0; $i < $days_between + 1; $i++)
	{
		$days[$i] = formatDate(gmdate("Y-m-d",$current));
		$sql = "SELECT COUNT(*) FROM `".$table."` WHERE TimeStamp LIKE '".gmdate("Y-m-d",$current)."%' AND Entering=1;";
		$counts[$i] = $connect->SRQuery($sql);
		$sum += $counts[$i];
		$current = strtotime('+1 day',$current);
	}
	
	
	//HOURLY AND DAILY AVERAGES
	/*
	if($days_between > 1)
			{ $dailyAverage = $sum / $days_between; }
		else { $dailyAverage = $sum; }
	$weeks_between = $days_between / 7;
	if($weeks_between >= 1)
			{ $weeklyAverage = $sum / $weeks_between; }
		else { $weeklyAverage = $sum; }
	$temp = $days_between;
	if($days_between <= 1) { $temp = 1; } 
	$hourlyAverage = $sum / ($temp * 24);
	*/
	//HOURLY AND DAILY AVERAGES
	
	
	echo("<p1>Total traffic counted between ".formatDate($firstDate)." and ".formatDate($secondDate)." </p1><span class=\"data_val\">".$sum."</span><br><br>");
	echo("<p1>Daily Average</p1>");
	echo("<span class=\"data_val\">".number_format($dailyAverage,2,'.','')."</span><br><br>");
	echo("<p1>Weekly Average</p1>");
	echo("<span class=\"data_val\">".number_format($weeklyAverage,2,'.','')."</span><br><br>");
	echo("<p1>Hourly Average</p1>");
	echo("<span class=\"data_val\">".number_format($hourlyAverage,2,'.','')."</span><br><br>");
	echo("
<html>
  <head>
    <script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
    <script type=\"text/javascript\">
	var vag = 20000;
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Date', 'Traffic'],");
		for($i = 0; $i < $days_between + 1; $i++)
		{
			echo("['".$days[$i]."', ".$counts[$i]."],");
		}
echo("
        ]);

        var options = {
			fontSize: 16,
			fontName: 'Helvetica',
			color: '#646464',
			titleTextStyle: { 
				color: '#646464',
				fontName: 'Helvetica',
				fontSize: '20'
			},
			backgroundColor: '#efefef',	
			legend: { position: 'bottom' },
			hAxis: {
				textStyle: {
					color: '#646464',
					fontName: 'Helvetica',
					fontSize: 16
				},
				title: 'Dates',
				titleTextStyle: { 
				color: '#646464',
				fontName: 'Helvetica',
				fontSize: '17',
				italic: 0
				}
			},
			vAxis: {
				textStyle: {
					color: '#646464',
					fontName: 'Helvetica',
					fontSize: 16
				},
				baselineColor: '#646464',
				title: 'Traffic',
				titleTextStyle: { 
				color: '#646464',
				fontName: 'Helvetica',
				fontSize: '17',
				italic: 0
				},
				minValue: 0
			},
			series: [
			{color: 'red', visibleInLegend: false}
			],
			pointShape: 'diamond',
			pointSize: 4,
			title: 'Traffic from ".formatDate($firstDate)." to ".formatDate($secondDate)."',
			tooltip: {
				textStyle: {
					color: '#646464',
					bold: 0,
					fontSize: 16
				},
				backgroundColor: '#646464'
			}
				
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);jg
      }
    </script>
  </head>
  <body>
    <div id=\"curve_chart\" style=\"width: 80vw; height: 500px\"></div>
	
  </body>
</html>");

}else{ echo("<p1>Please select a valid range</p1>"); }
}
echo("</div>");

?>




<br><br>
<div style = "text-align:center">
		<a href="admin.php" class="admin_option">Admin Page</a>
	</div>
</body>
</html>
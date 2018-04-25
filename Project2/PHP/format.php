<?php
//----------------------------------------------------------
// File: format.php
// Project: CSCE 315 Project 2, Spring 2018
//
// This file contains the functions for formatting dates into
// their final style for the webpage. 
//----------------------------------------------------------

//----------------------------------------------------------
// Function: formatDate
// Precondition: a date in the standard timestamp format
// Postcondition: returns a date in m/d/y format
//----------------------------------------------------------
function formatDate($date)
{
	$year = getYear($date);
	$month = getMonth($date);
	$day = getDay($date);
	if($month[0] == "0") $month = $month[1];
	if($day[0] == "0") $day = $day[1];
	return $month."/".$day."/".$year; 
}

//----------------------------------------------------------
// Function: formatTime
// Precondition: a date in the standard timestamp format
// Postcondition: returns the time in a h:mm:ss a format
//----------------------------------------------------------
function formatTime($date)
{
	$hour = substr($date,11,2);
	$minute = substr($date,14,2);
	if($hour[0] == "0") $hour = $hour[1];
	$AP = "AM";
	if($hour >= 12)
	{
		$AP = "PM";
		if($hour != "12")
			$hour = $hour - 12;
	}
	if($hour == "0") $hour = "12";
	return $hour.":".$minute." ".$AP;
}

//----------------------------------------------------------
// Function: getYear
// Precondition: a date in the standard timestamp format
// Postcondition: returns the year of a date
//----------------------------------------------------------
function getYear($date)
{
	return substr($date,0,4);
}

//----------------------------------------------------------
// Function: getDay
// Precondition: a date in the standard timestamp format
// Postcondition: returns the day of a date
//----------------------------------------------------------
function getDay($date)
{
	return substr($date,8,2);
}

//----------------------------------------------------------
// Function: getMonth
// Precondition: a date in the standard timestamp format
// Postcondition: returns the month of a date
//----------------------------------------------------------
function getMonth($date)
{ 
	return substr($date,5,2);
}
?>
<?php

/* File:  history.php
 * Author:  Timothy Pace
 * Creation Date: 8/20/2013
 * Purpose: Retrieve data points from the greenwharf database.
 *
 * Parameters: start - UTC formatted date string
 *             end - UTC formatted date string
 *
 * Response: If only start is given, the response is all data points between
 *           start and current time.  If only end is given, data points from
 *           end date are given.  If both are supplied data points between start
 *           and end are given.  If no arguments are given, the response is defaulted
 *           to the last two weeks.
 *
 *           JSON object echoed has fields (windSpeed, pyro, turbineAmps, windDir) with
 *           data formatted for highcharts.
 */
 
$directions = array("N","NNE","NE","ENE","E","ESE", "SE", "SSE","S","SSW","SW","WSW","W","WNW","NW","NNW");

//Setup windBins for counting points
for ($i = 0; $i < count($directions); $i++) {
   $windBins[$directions[$i]] = array_fill(0, 7, 0);
}

/* Function to convert degress into a direction string, i.e.
 * degrees_to_compass(10) returns "N"
 */
function degrees_to_compass($num) {
   global $directions;
   $index = (int) ($num/22.5 + 0.5);
   return $directions[($index % 16)];
}

/* Figures out which index into the windBins array to insert
 * a windSpeed point.
 */
function categorize_wind($windSpeed) {
   if ($windSpeed < 1.5) return 0;
   else if ($windSpeed >= 1.5 && $windSpeed < 5.5 ) return 1;
   else if ($windSpeed >= 5.5 && $windSpeed < 11 ) return 2;
   else if ($windSpeed >= 11 && $windSpeed < 17 ) return 3;
   else if ($windSpeed >= 17 && $windSpeed < 24.5 ) return 4;
   else if ($windSpeed >= 24.5 && $windSpeed < 32.5 ) return 5;
   else if ($windSpeed >= 32.5) return 6;
   else return -1;
}

header("Content-type: application/json");
include('db_credentials.php');

//Pull UTC and unix timestamps from GET options
$start_date_UTC = $_GET['start'];
$end_date_UTC   = $_GET['end'];
$start_ts       = strtotime($start_date_UTC);
$end_ts         = strtotime($end_date_UTC);

//Figure out which query to execute.
if (isset($_GET['start'])) {
   if (isset($_GET['end'])) {
      $wharf_query = "SELECT * FROM wharf_data WHERE utime >= " . $start_ts . " and utime <= ". $end_ts ." ORDER BY utime asc";
      $solar_query = "SELECT * FROM solar_data WHERE utime >= " . $start_ts . " and utime <= ". $end_ts ." ORDER BY utime asc";
   }
   else {
      $wharf_query = "SELECT * FROM wharf_data WHERE utime >= " . $start_ts . " ORDER BY utime asc";
      $solar_query = "SELECT * FROM solar_data WHERE utime >= " . $start_ts . " ORDER BY utime asc";
   }
}
elseif (isset($_GET['end'])) {
   $wharf_query = "SELECT * FROM wharf_data WHERE date(dateUTC) = '" . $end_date_UTC . "' ORDER BY utime asc";
   $solar_query = "SELECT * FROM solar_data WHERE date(Date_UTC) = '" . $end_date_UTC . "' ORDER BY utime asc";
}
else {
   $wharf_query = "SELECT * FROM wharf_data WHERE utime > " . (time()-24*3600*14) . " ORDER BY utime asc";
   $solar_query = "SELECT * FROM solar_data WHERE utime > " . (time()-24*3600*14) . " ORDER BY utime asc";
}


// Setup arrays for JSON object
$json['windSpeed'] = array();
$json['pyro'] = array();
$json['turbine'] = array();
$json['solar'] = array();

/* Used to count total number of points used in wind direction
 * and wind speed range calculations.
 */
$windPoints = 0;

//Connect to database and execute query
$conn = mysql_connect($host, $user, $pw) or die('Could not connect: ' . mysql_error());
mysql_select_db($db, $conn) or die('No Luck: ' . mysql_error() . "\n");
$resp = mysql_query($wharf_query);

/* Process rows of mysql query. Points that are 999 are ignored
 * as they are error points.  Timestamps are multiplied by 1000
 * as Javascript dates use milliseconds since epoch.
 *
 * windSpeed: points are pushed into an array with a timestamp
 *            that is highcharts friendly, and then is added to
 *            the JSON object.
 *
 * windDir: array used to calculate how many points fall within
 *          a given speed range for each direction.
 *
 * pyro: points are pushed into an array with a timestamp that
 *       is highcharts friendly, and then is added to the JSON
 *       object.
 *
 */
  
while ($row = mysql_fetch_assoc($resp)) {
   if ((float) $row['windSpeed'] != 999) {
      array_push($json['windSpeed'], array($row['utime']*1000, (float) $row['windSpeed']));
      if ((float) $row['windDir'] != 999) {
         $windBins[degrees_to_compass($row['windDir'])][categorize_wind($row['windSpeed'])]++;
         $windPoints++;
      }
   }
   if ((float) $row['pyro'] != 999) array_push($json['pyro'], array($row['utime']*1000, (float) $row['pyro']));
   if ((float) $row['turbineAmps'] != 999) array_push($json['turbine'], array($row['utime']*1000, (float) ($row['turbineAmps']*24.1)));
}

$resp = mysql_query($solar_query);

while ($row = mysql_fetch_assoc($resp)) {
   if ($row['AmpsOut'] != 999 && $row['BatVolts'] != 999) array_push($json['solar'], array($row['utime']*1000, (float) ($row['AmpsOut']*$row['BatVolts'])));
}
/* Calculate percent each wind direction gets at different speeds using the number
 * of points that fell within each range
 */
 
for ($i = 0; $i < count($directions); $i++) {
   for ($j = 0; $j < 6; $j++) {
      $windBins[$directions[$i]][$j] = (float)(100*($windBins[$directions[$i]][$j] / $windPoints));
   }
}
$json['windDir'] = $windBins;
echo json_encode($json);
?>
<?php

/* File:  history.php
 * Author: Zachary Graham
 * Creation Date: 9/5/2013
 * Purpose: Create and return a csv file of wharf data
 *
 * Parameters: start - UTC formatted date string
 *             end - UTC formatted date string
 *             type - (wind,solar,weather,all)
 *
 * Response:
 *
 * Response: If only start is given, the response is all data points between
 *           start and current time.  If only end is given, data points from
 *           end date are given.  If both are supplied data points between start
 *           and end are given.  If no arguments are given, the response is defaulted
 *           to the last two weeks.
 *
 *           type must be: solar, wind, weather, all. Currently solar and weather are 
 *           implemented
 *
 */

require('Archive/Tar.php');
define("output", "php://output");


function emit_file($f){
    $out=fopen(output, 'w');
    while ( ! feof($f) ) {
        fwrite($out, fgets($f));
    }
    fclose($out);
}



function query2csv($fobj, $query) {
    $rows = mysql_query($query) or die("Query Error: ". mysql_error());
    while( $row = mysql_fetch_assoc($rows) ) fputcsv($fobj, array_values($row), $delimeter=',', $enclosure=chr(0));
}

function makeWeatherCSV( $fobj, $start_ts=NULL, $end_ts=NULL) {
    $columnToHeaderMap = array( 
        'dateUTC'=>'Time (UTC)', 
        'windSpeed'=>'Windspeed (mph)', 
        'windSpeedVar'=>'Windspeed Variance', 
        'windDir'=>'Wind Heading (deg)'
    );
    fputcsv($fobj, array_values($columnToHeaderMap));

    //Figure out which query to execute.
    if ($start_ts!=NULL) {
       if ($end_ts!=NULL) {
           $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
               " FROM wharf_data WHERE utime >= " . $start_ts . " and utime <= ". $end_ts .
               " ORDER BY utime asc";
       } else {
           $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
              " FROM wharf_data WHERE utime >= " . $start_ts . " ORDER BY utime asc";
       }
    } elseif ($end_ts!=NULL) {
        $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
            " FROM wharf_data WHERE utime <= " . $end_ts . " ORDER BY utime asc";
    } else {
        $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
            " FROM wharf_data WHERE utime >= " . (time()-24*3600*14) . " ORDER BY utime asc";
    }
    query2csv($fobj, $query);
}

function makeSolarCSV( $fobj, $start_ts, $end_ts){
    $columnToHeaderMap = [
        'Date_UTC' => 'Time (UTC)',
        'VoltsIn' => 'PV Voltage',
        'AmpsIn' => 'PV Current',
        'BatVolts' => 'Battery Voltage',
        'AmpsOut' => 'Charging Current'
        ];
    fputcsv($fobj, array_values($columnToHeaderMap));

    if ($start_ts!=NULL){
        if ($end_ts!=NULL) {
            $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
                " FROM solar_data WHERE utime >= " . $start_ts . " and utime <= " . $end_ts . " ORDER BY utime asc";
        } else {
            $query = "SELECT " . join(',', array_keys($columnToHeaderMap)) .
                " FROM solar_data WHERE utime >= " . $start_ts . " ORDER BY utime asc";
        }
    } elseif ($end_ts!=NULL) {
        $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
            " FROM solar_data WHERE utime <= " . $end_ts . " ORDER BY utime asc";
    } else {
        $query = "SELECT " . join(', ', array_keys($columnToHeaderMap)) .
            " FROM solar_data  WHERE utime >= " . (time()-24*3600*14) . " ORDER BY utime asc";
    }
    
    query2csv($fobj, $query);
}

function make_tarball_and_emit($file_list, $compress=null){
    $fname='greenwarf-archive.tar'; //change to UUID of some sort
    if ($compress=='gz'){
        $fname .= '.gz';
    } elseif ($compress=='bz2'){
        $fname .= '.bz2';
    }
    $tar = new Archive_Tar($fname, $compress);
    $tar->create($file_list) or die("Error creating archive!");
    readfile($fname);
    unlink($fname); //no caching... which should be done as we scale
}


function select_query_string($start_ts, $end_ts, &$wharf_query_string=NULL, &$solar_query_string=NULL){
    if( ($wharf_query_string==NULL) and ($solar_query_string==NULL)){
        return -1;
    }

    return 1;

}

function make_wind_csv( $fobj, $start_ts, $end_ts){
    //needs way more work than SOLAR OR WEATHER
}

include('db_credentials.php');
include_once('helpers.php');

//Pull UTC and unix timestamps from GET options
$start_date_UTC = $_GET['start'];
$end_date_UTC   = $_GET['end'];
$type_of_csv    = $_GET['type'];
$start_ts       = strtotime($start_date_UTC);
$end_ts         = strtotime($end_date_UTC);


//Connect to database and execute query
$conn = mysql_connect($host, $user, $pw) or die('Could not connect: ' . mysql_error());
mysql_select_db($db, $conn) or die('No Luck: ' . mysql_error() . "\n");

/* open file */
$out=fopen(output,'w');

if ( strtolower($type_of_csv) == 'weather' ) {
    header("Content-type: txt/csv");
    header("Content-Disposition: attachment; filename=weather.csv");
    makeWeatherCSV($out, $start_ts, $end_ts); 
} elseif ( strtolower($type_of_csv) == 'solar') {
    header("Content-type: txt/csv");
    header("Content-Disposition: attachment; filename=solar.csv");
    makeSolarCSV($out, $start_ts, $end_ts);
} elseif (strtolower($type_of_csv) == 'all') {
    header("Content-type: application/gzip");
    header("Content-Disposition: attachment; filename=greenwharf-archive.tar");
    $fList=array();
    $a='tmp.txt';
    array_push($fList, $a);
    make_tarball_and_emit($fList);
} else {
    http_response_code(400);//bad request 
}
fclose($out);

?>

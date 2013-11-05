<?php

/* File:  download.php
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


$solarColumnToHeaderMap = array(
    'Date_UTC' => 'Time (UTC)',
    'VoltsIn' => 'PV Voltage (Volts)',
    'AmpsIn' => 'PV Current (Amps)',
    'BatVolts' => 'Battery Voltage (Volts)',
    'AmpsOut' => 'Charging Current (Amps)',
    'TotChg*BatVolts' => 'PV Power Stored in Batteries (Watt-hours)'
);

$wharfColumnToHeaderMap = array(
    'dateUTC'=>'Time (UTC)',
    'windSpeed'=>'Windspeed (mph)',
    'windSpeedVar'=>'Windspeed Variance',
    'windDir'=>'Wind Heading (deg)'
);

function query2csv($fobj, $query) {
    $counter=0;
    $rows = mysql_query($query) or die("Query Error: ". mysql_error());
    while( $row = mysql_fetch_assoc($rows) ) {
        fputcsv($fobj, array_values($row), $delimeter=',', $enclosure=chr(0));
    }
}

function make_weather_csv( $fobj, $query){
    global $wharfColumnToHeaderMap;
    fputcsv($fobj, array_values($wharfColumnToHeaderMap));
    query2csv($fobj, $query);
}

function make_solar_csv( $fobj, $query){
    global $solarColumnToHeaderMap;
    fputcsv($fobj, array_values($solarColumnToHeaderMap));
    query2csv($fobj, $query);
}

function make_tarball_and_emit($fobj, $file_list,$tarName, $compress=NULL){
    if ($compress=='gz'){
        $tarName .= '.gz';
    } elseif ($compress=='bz2'){
        $tarName .= '.bz2';
    }
    header("Content-type: application/tarball");
    header("Content-Disposition: attachment; filename=$tarName");
    $tar = new Archive_Tar($tarName, $compress);
    file_put_contents("php://stdout", "Tarball Created ($tarName)\n");
    foreach( $file_list as $name=>$handle){
        file_put_contents("php://stdout", "\t$name added to tarball\n");
        $tar->addString( $name, stream_get_contents($handle)) or die("Error adding $name to tarball");
    }
    $f=fopen($tarName, 'r');
    
    while ( ! feof($f) ) {
        fwrite($fobj, fgets($f));
    }

    fclose($f);
    unlink($tarName); //no caching... which should be done as we scale
}

function make_zip_archive_and_emit($fobj, $file_list, $zipName){
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$zipName");
    $zip=new ZipArchive();
    $zip->open($zipName, ZipArchive::CREATE);
    file_put_contents("php://stdout", "Zip Archive Created ($zipName)\n");
    foreach( $file_list as $name=>$handle){
        file_put_contents("php://stdout", "\t$name added to zipfile\n");
        $zip->addFromString( $name, stream_get_contents($handle)) or die("Error adding $name to zip archive");
    }
    $zip->close();
    $f=fopen($zipName, 'rb');

    while ( ! feof($f) ) {
        fwrite($fobj, fgets($f));
    }
    fclose($f);
    unlink($zipName);

}

function select_query_string($start_ts, $end_ts, &$wharf_query_string, &$solar_query_string){
    global $wharfColumnToHeaderMap;
    global $solarColumnToHeaderMap;
    if( ($wharf_query_string==NULL) and ($solar_query_string==NULL)){
        return -1;
}
    if ($start_ts!=NULL){
        if ($end_ts!=NULL) {
            if($solar_query_string){
                $solar_query_string = "SELECT " . join(', ', array_keys($solarColumnToHeaderMap)) .
                    " FROM solar_data WHERE utime >= " . $start_ts . " and utime <= " . $end_ts . " ORDER BY utime asc";
            }
            if($wharf_query_string){
                $wharf_query_string = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
                    " FROM wharf_data WHERE utime >= " . $start_ts . " and utime <= " . $end_ts . " ORDER BY utime asc";
            }
        } else {
            if($solar_query_string){
                $solar_query_string = "SELECT " . join(', ', array_keys($solarColumnToHeaderMap)) .
                    " FROM solar_data WHERE utime >= " . $start_ts . " ORDER BY utime asc";
            }
            if($wharf_query_string){
                $wharf_query_string = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
                    " FROM wharf_data WHERE utime >= " . $start_ts . " ORDER BY utime asc"; 
            }
        }
    } elseif ($end_ts!=NULL) {
        if ($solar_query_string){
            $solar_query_string = "SELECT " . join(', ', array_keys($solarColumnToHeaderMap)) .
                " FROM solar_data WHERE utime <= " . $end_ts . " ORDER BY utime asc";
        }
        if ($wharf_query_string){
            $wharf_query_string = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
                " FROM wharf_data WHERE utime <= " . $end_ts . " ORDER BY utime asc";
        }
    } else {
        if($solar_query_string){
            $solar_query_string = "SELECT " . join(', ', array_keys($solarColumnToHeaderMap)) .
                " FROM solar_data  WHERE utime >= " . (time()-24*3600*14) . " ORDER BY utime asc";
        }
        if($wharf_query_string){
            $wharf_query_string = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
                " FROM wharf_data WHERE utime >= " . (time()-24*3600*14) . " ORDER BY utime asc";
        }
    }
    

    return 1;

}

function make_wind_csv( $fobj, $start_ts, $end_ts){

    $wharfColumnToHeaderMap = array(
        'dateUTC'=>'Time (UTC)',
        'windSpeed'=>'Windspeed (MPH)',
        'windDir'=>'Wind Heading (Deg)',
        'turbineAmps' => 'Wind Turbine Current (Amps)',
        'ADC2' => 'Divert-load (Amps)',
        '24*turbineAmps' => 'Wind Turbine Power (Watts)',
        '.576*POWER(ADC2,2)' => 'Divert-load power (Watts)',
        '24*turbineAmps/6' => 'Wind Turbine Power (Watt-hours) rectangular estimate',
        '.576*POWER(ADC2,2)/6' => 'Divert-load Power (Watt-hours) rectangular estimate'
    );

    fputcsv($fobj, array_values($wharfColumnToHeaderMap));

    if ($start_ts!=NULL){
        if ($end_ts!=NULL) {
            $query = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
                " FROM wharf_data WHERE utime >= " . $start_ts . " and utime <= " . $end_ts . " ORDER BY utime asc";
        } else {
            $query = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
                " FROM wharf_data WHERE utime >= " . $start_ts . " ORDER BY utime asc"; 
        }
    } elseif ($end_ts!=NULL) {
        $query = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
            " FROM wharf_data WHERE utime <= " . $end_ts . " ORDER BY utime asc";
    } else {
        $query = "SELECT " . join(', ', array_keys($wharfColumnToHeaderMap)) .
            " FROM wharf_data WHERE utime >= " . (time()-24*3600*14) . " ORDER BY utime asc";
    }

    query2csv($fobj, $query);
    
    
}

include('db_credentials.php');
include_once('helpers.php');
date_default_timezone_set( 'UTC'); //our storage timezone
$start_ts=$end_ts=$type_of_csv=NULL;
//Pull UTC and unix timestamps from GET options
if(isset($_GET['start'])){
    $start_date_UTC = $_GET['start'];
    $start_ts       = strtotime($start_date_UTC);
} else {
    $start_ts       = NULL;
}

if(isset($_GET['end'])){
    $end_date_UTC   = $_GET['end'];
    $end_ts         = strtotime($end_date_UTC);
} else {
    $end_ts         = NULL;
}
if(isset($_GET['type'])){
    $type_of_csv    = $_GET['type'];
} else {
    $type_of_csv     = NULL;
}


//Connect to database and execute query
$conn = mysql_connect($host, $user, $pw) or die('Could not connect: ' . mysql_error());
mysql_select_db($db, $conn) or die('No Luck: ' . mysql_error() . "\n");

/* open file */
$out=fopen(output,'wb');
$_tmp=NULL; //replaced $_tmp=NULL in following function calls due to PHP-Strict pass-by-reference violations
if ( strtolower($type_of_csv) == 'weather' ) {
    header("Content-type: txt/csv");
    header("Content-Disposition: attachment; filename=weather.csv");
    $query=' ';
    select_query_string($start_ts, $end_ts, $query, $_tmp);  //$_tmp=NULL is a hack to pass the value NULL by reference
    make_weather_csv($out, $query); 
} elseif ( strtolower($type_of_csv) == 'solar') {
    header("Content-type: txt/csv");
    header("Content-Disposition: attachment; filename=solar.csv");
    $query=' ';
    select_query_string($start_ts, $end_ts, $_tmp, $query); //$_tmp=NULL is a hack to pass the value NULL by reference
    make_solar_csv($out, $query);
} elseif (strtolower($type_of_csv) == 'wind' ) {
    header("Content-type: txt/csv");
    header("Content-Disposition: attachment; filename=wind.csv");
    make_wind_csv($out, $start_ts, $end_ts);

} elseif (strtolower($type_of_csv) == 'all') {
    $weatherFile=fopen("php://temp", "rw+");
    $solarFile=fopen("php://temp", "rw+");
    $windFile=fopen("php://temp", "rw+");
    $wQuery=' ';
    $sQuery=' ';
    select_query_string($start_ts, $end_ts, $wQuery, $sQuery);   
    make_weather_csv($weatherFile, $wQuery);
    fseek($weatherFile, 0);
    make_solar_csv($solarFile, $sQuery);
    fseek($solarFile, 0);
    make_wind_csv($windFile, $start_ts, $end_ts);
    fseek($windFile, 0);
    $fList=array();
    $fList['weather.csv']=$weatherFile;
    $fList['solar.csv']=$solarFile;
    $fList['wind.csv']=$windFile;
    $name='greenwharf-archive.tar';
    make_tarball_and_emit($out, $fList, $name);
    fclose($weatherFile);
    fclose($solarFile);
    fclose($windFile);
} else if( strtolower($type_of_csv) == 'zip' ) {
    $weatherFile=fopen("php://temp", "rw+");
    $solarFile=fopen("php://temp", "rw+");
    $windFile=fopen("php://temp", "rw+");
    $wQuery=' ';
    $sQuery=' ';
    select_query_string($start_ts, $end_ts, $wQuery, $sQuery);
    make_weather_csv($weatherFile, $wQuery);
    fseek($weatherFile, 0);
    make_solar_csv($solarFile, $sQuery);
    fseek($solarFile, 0);
    make_wind_csv($windFile, $start_ts, $end_ts);
    fseek($windFile, 0);
    $fList=array();
    $fList['weather.csv']=$weatherFile;
    $fList['solar.csv']=$solarFile;
    $fList['wind.csv']=$windFile;
    $name="greenwharf-archive.zip";
    make_zip_archive_and_emit($out, $fList, $name);
    fclose($weatherFile);
    fclose($solarFile);
    fclose($windFile);
} else {
    http_response_code(400);//bad request 
}
fclose($out);

?>

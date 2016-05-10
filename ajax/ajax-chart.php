<?php 

error_reporting(E_ALL);
ini_set('memory_limit', '750M');

$total_start = microtime(true);

$type = strtolower($_POST["type"]);
$port = $_POST["port"];
$device = $_POST["device"];
$range = $_POST["range"];
$nth = $_POST["nth"];

/*
$port=1;
$device="EraCoal";
$range="all";
$nth="50";
$type="kwh";
*/

include ("../config/config-database.php");
$connect_start = microtime(true);
include ("../php/db-connect.php");
$connect_time_elapsed_secs = microtime(true) - $connect_start;

$filter = "";
if ($range == "hour") $filter   = "AND `timestamp` > (DATE_SUB(NOW(), INTERVAL 1 HOUR)))  ORDER BY timestamp ASC";  
if ($range == "day") $filter    = "AND `timestamp` > (DATE_SUB(NOW(), INTERVAL 1 DAY)))   ORDER BY timestamp ASC";  
if ($range == "week") $filter   = "AND `timestamp` > (DATE_SUB(NOW(), INTERVAL 1 WEEK)))  ORDER BY timestamp ASC"; 
if ($range == "month") $filter  = "AND `timestamp` > (DATE_SUB(NOW(), INTERVAL 1 MONTH))) ORDER BY timestamp ASC"; 
if ($range == "custom") $filter = "AND `timestamp` > '".$db['startGraph']."' AND timestamp < '".$db['endGraph']."' ORDER BY timestamp ASC)"; 
if ($range == "all")    $filter = "ORDER BY timestamp ASC)"; 
if ($range == "last") $filter   = "ORDER BY timestamp DESC LIMIT 0,".$db['lastRecords'].") ORDER BY timestamp ASC"; 

$db_start = microtime(true);
$query = "(SELECT * FROM `".$device."` WHERE `port`=".$port." ".$filter;
$exec = mysqli_query($connect,$query);
$db_time_elapsed_secs = microtime(true) - $db_start;

$cnt = 0;
$totalkwh = 0;

$start = microtime(true);
while ($a = mysqli_fetch_array($exec))
{
 if ($type == "kwh")
 {
     $secNow = strtotime($a['timestamp']);
     if ($cnt == 0) { $secPrev = strtotime($a['timestamp']); }
     $differenceInSeconds = $secNow - $secPrev;
     $hourPart = $differenceInSeconds/3600;
     $kwh = (float)$a['power'] * $hourPart;
     $totalkwh += $kwh;

     $stamp = $a['timestamp'];
     $datum = date('d. m.', strtotime($a['timestamp']));
     $cas = date('H:i', strtotime($a['timestamp']));
     $chart_data[] = array($datum,round($totalkwh,2),$cas,$stamp);

     $secPrev = $secNow;
     $cnt++;
     //var_dump(memory_get_usage());
 }
 else
 {
 $stamp = $a['timestamp'];
 $cas = date('H:i', strtotime($a['timestamp']));
 $datum = date('d. m.', strtotime($a['timestamp']));
 $chart_data[] = array($datum, (float)$a[$type],$cas,$stamp);
 }
}
$compute_time_elapsed_secs = microtime(true) - $start;

// DECIMATE
$i=0;
$decimated = array();
$chart_record_count = count($chart_data);
$every_nth = floor($chart_record_count / $nth);
foreach($chart_data as $value) {
    if ($i++ % $every_nth == 0) {
        $decimated[] = $value;
    }
}
$total_time_elapsed_secs = microtime(true) - $total_start;

$result = array();
$result['query'] = $query;
$result['chart'] = $decimated;
$result['post'] = $_POST;
$result['total_time'] = $total_time_elapsed_secs;
$result['compute_time'] = $compute_time_elapsed_secs;
$result['connect_time'] = $connect_time_elapsed_secs;
$result['db_time'] = $db_time_elapsed_secs;
$result['rows'] = mysqli_num_rows($exec);

echo json_encode($result);

?> 
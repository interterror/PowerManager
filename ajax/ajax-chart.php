<?php 

error_reporting(0);

$type = strtolower($_POST["type"]);
$port = $_POST["port"];
$device = $_POST["device"];


include ("../config/config-database.php");
include ("../php/db-connect.php");

$filter = "";
if ($db['graphInterval'] == "hour") $filter = "AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY timestamp ASC)";  
if ($db['graphInterval'] == "today") $filter = "AND timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY timestamp ASC)";  
if ($db['graphInterval'] == "week") $filter = "AND timestamp > DATE_SUB(NOW(), INTERVAL 1 WEEK) ORDER BY timestamp ASC"; 
if ($db['graphInterval'] == "month") $filter = "AND timestamp > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY timestamp ASC"; 
if ($db['graphInterval'] == "custom") $filter = "AND timestamp > '".$db['startGraph']."' AND timestamp < '".$db['endGraph']."' ORDER BY timestamp ASC"; 
if ($db['graphInterval'] == "last") $filter = "ORDER BY timestamp DESC LIMIT 0,".$db['lastRecords'].") ORDER BY timestamp ASC"; 

$query = "(SELECT * FROM ".$device." WHERE port=".$port." ".$filter;
$exec = mysqli_query($connect,$query);

//$pie_chart_data[] = array("ČAS", $type);

while ($a = mysqli_fetch_array($exec))
{
  $data[$a['timestamp']] = intval($a['power']);

 $datum = date('H:i', strtotime($a['timestamp']));
 $chart_data[] = array($datum, (float)$a[$type]);

}
//$chart_data["LOL"] = "SELECT * FROM ".$device." WHERE port=".$port." AND timestamp > '".$db['startGraph']."' AND timestamp < '".$db['endGraph']."'";

echo json_encode($chart_data);

// Instead you can query your database and parse into JSON etc etc

?> 
<?php
session_start();
error_reporting(0);
//error_reporting(E_ALL);

include ("../config/config-devices.php");
include ("../config/config-rules.php");

$device = $_GET['device'];
$task   = $_GET['task'];

$access = array();
$access['ip']      = $m[$device]['ip'];
$access['logname'] = $m[$device]['logname'];
$access['logpass'] = $m[$device]['logpass'];
$access['device']  = $device;

$otherData = array();
$sensorData = array();
$outletsResult = array();
$rulesResult = array();

function getAccessToDevice($device)
         {
          global $access;
          global $m;
          $access['ip']      = $m[$device]['ip'];
          $access['logname'] = $m[$device]['logname'];
          $access['logpass'] = $m[$device]['logpass'];
          $access['device']  = $device;
         }

function loginMfi()
         {
         global $access;

         $data = array("username" => $access['logname'],"password"=>$access['logpass']);
         $ch = curl_init($access['ip']."/login.cgi");
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
         curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
         curl_setopt($ch, CURLOPT_COOKIE,"AIROS_SESSIONID=01234567890123456789012345678901");
         $response = curl_exec($ch);
         }


function getOutlet()
         {
         global $access;
         $mfiCommand = $access['ip']."/sensors";

         $ch = curl_init($mfiCommand);         
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
       //curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
         curl_setopt($ch, CURLOPT_COOKIE,"AIROS_SESSIONID=01234567890123456789012345678901");       
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
         $response = curl_exec($ch);         
         $info = curl_getinfo($ch);
         curl_close($ch);

         if (!$response) $result = -1; else $result = $response;
         return $result;
         }

function setOutlet($ip,$data)
         {
         global $otherData;
         $mfiCommand = $ip."/sensors/".$data["outlet"];

         $ch = curl_init($mfiCommand);         
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
         curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
         curl_setopt($ch, CURLOPT_COOKIE,"AIROS_SESSIONID=01234567890123456789012345678901");    
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);       
         $response = curl_exec($ch);         
         $info = curl_getinfo($ch);

         $otherData['mfiComm'][] = $mfiCommand;
         $otherData['mfiCommData'][] = $data;


         if (strlen($info['redirect_url']) > 0) $result = -1; else $result = $info['total_time'];
         return $result;
         }


function checker($device)
         {
         global $rule;
         global $label;
         global $output;
         global $outletsResult;
         global $sensorData;
         global $m;
         global $access;

         getAccessToDevice($device);
     
         $response = getOutlet();         
         
         if ($response == -1) { loginMfi(); $response = getOutlet(); }
         
         $mfiData = json_decode($response);
         
         $subdata = $mfiData->sensors;
         $outletsResult .= $subdata;
     

         
         foreach ($subdata as $key=>$value)
                 {         
                 $sdLabel = $m[$access['device']]['outlet'][$value->port]['label'];
                 $sensorData[$sdLabel]['LABEL'] = $sdLabel;
                 $sensorData[$sdLabel]['OUTPUT'] = $value->output;
                 $sensorData[$sdLabel]['POWER'] = $value->power;
                 $sensorData[$sdLabel]['PORT'] = $value->port;
                 $sensorData[$sdLabel]['POWERFACTOR'] = $value->powerfactor;
                 $sensorData[$sdLabel]['CURRENT'] = $value->current;
                 $sensorData[$sdLabel]['VOLTAGE'] = $value->voltage;
                 $sensorData[$sdLabel]['THISMONTH'] = $value->thismonth;
                 $sensorData[$sdLabel]['DEVICE'] = $device;
                 $sensorData[$sdLabel]['IP'] = $m[$device]['ip'];
                 }


        }

function checkRules()
         {
         global $rule;
         global $output;
         global $label;
         global $rulesResult;    
         global $sensorData; 
  
         foreach ($rule as $key=>$value)
                 {
                 $rulesResult[$key]['id'] = $key;
                 $rulesResult[$key]['title'] = $value['title'];
                 $rulesResult[$key]['condition'] = $value['condition'];
                 $rulesResult[$key]['action'] = $value['action'];
                 $rulesResult[$key]['elseaction'] = $value['elseaction'];
                 

                 if (strlen($value['repeat']) < 1) $repeat = 0; else $repeat = $value['repeat'];
                 $rulesResult[$key]['repeat'] = $repeat;

                 
                 if (checkConditions($value['condition'])) { $rulesResult[$key]['status'] = "TRUE"; runActions($rulesResult[$key]); } else { $rulesResult[$key]['status'] = "FALSE"; runActions($rulesResult[$key]); }
                 }

                 //echo("<pre>");
                 //print_r($rulesResult);
         }

function checkConditions($conditions) 
         {
         global $sensorData;

         $resultCondition = true;

         foreach ($conditions as $key=>$value)
                 { 
                 $condition = $value;

                 $parsedCondition = explode(";", $condition);
                 $who            = $parsedCondition[0];
                 $what           = $parsedCondition[1];
                 $comparator     = $parsedCondition[2];
                 $number         = $parsedCondition[3];   

                 // SCHEDULE RULE
                 if ($who == "SCHEDULE") { if (checkSchedule($condition)) $condition=true; else $resultCondition=false; continue; } 

                 $compareData = $sensorData[$who][$what];

                 //echo($who." ".$what." ".$comparator." ".$number." ".$compareData);
           
                 $condition = false;
                 if ($comparator == "<") { if ($compareData < $number)  $condition=true; else $resultCondition=false; }
                 if ($comparator == ">") { if ($compareData > $number)  $condition=true; else $resultCondition=false; }
                 if ($comparator == "=") { if ($compareData == $number) $condition=true; else $resultCondition=false; }
                 }        

         return $resultCondition;
         }

function checkSchedule($condition)
         {

            $parsedCondition = explode(";", $condition);
            $starttime       = $parsedCondition[1];
            $endtime         = $parsedCondition[2];
            $dayofweek       = $parsedCondition[3];

            $parsedDaysOfWeek = explode("-", $dayofweek);

            $today = new DateTime();
            $parsedStartTime = DateTime::createFromFormat( 'H:i:s', $starttime);
            $parsedEndTime   = DateTime::createFromFormat( 'H:i:s', $endtime);     

            
            if ($parsedEndTime < $parsedStartTime) $overMidnight = true; else $overMidnight = false;

            $partialCondition = false;

            if ($overMidnight) { if (($today > $parsedStartTime) || ($today < $parsedEndTime)) $partialCondition = true; } else
                               { if (($today > $parsedStartTime) && ($today < $parsedEndTime)) $partialCondition = true; }

            if (strlen($dayofweek)>0) { if ((in_array(date("N"), $parsedDaysOfWeek)) && ($partialCondition)) $partialCondition = true; else $partialCondition = false; }

            return $partialCondition;
         }

function portByLabel($iLabel)
         {

         }

function runActions($rule)
         {
           global $output;
           global $sensorData;
           global $rulesResult;
           global $m;
           global $access;

           $ruleId = $rule['id'];
           $actions = $rule['action'];
           $repeat = $rule['repeat'];
           $status = $rule['status'];

           if ($status == "FALSE") { $actions = $rule['elseaction']; $_SESSION['ruleRepeat'][$ruleId] = 0; }

           $output["on"] = 1;
           $output["off"] = 0;


         foreach ($actions as $key=>$value)
                 { 
                 $action = $value;         
                 
                 $parsedAction = explode(";", $action);
                 $who          = $parsedAction[0];
                 $what         = $parsedAction[1];
                 $how          = strtolower($parsedAction[2]);
         
                 $port = $sensorData[$who]['PORT'];
         
                 $actionData = array(strtolower($what) => $output[$how]);
                 $actionData['outlet'] = $port;

                 $rulesResult[$ruleId]['action-command'] = $actionData;

                 if (isset($_SESSION['ruleRepeat'][$ruleId])) $storedRepeat = $_SESSION['ruleRepeat'][$ruleId]; else $storedRepeat = 0;

                 if ($sensorData[$who][$what] != $output[$how]) {                              
                                                                $rulesResult[$ruleId]['change'] = "TRUE";
                                                                
                                                                if (($repeat == 0) || ($storedRepeat < $repeat)) { $storedRepeat++; setOutlet($sensorData[$who]['IP'],$actionData); }
                                  
                                                                $_SESSION['ruleRepeat'][$ruleId] = $storedRepeat;
                                  
                                                                }
                                                                else
                                                                {
                                                                  $rulesResult[$ruleId]['change'] = "FALSE";
                                                                }
                 $rulesResult[$ruleId]['repeated'] = $storedRepeat;

                }

              //echo("<pre>");print_r($rulesResult);  
                       
         }

if ($task == "switch")
   {   
   if (isset($_GET['outlet'])) { $thesensor = $_GET['outlet']; } else { exit; }
   if (isset($_GET['mode']))   { $mode = $_GET['mode']; } else { exit; }

   if ($mode == "off") $data = array("output" => 0);
   if ($mode == "on")  $data = array("output" => 1);
   
   $data["outlet"] = $thesensor;

   setOutlet($m[$device]['ip'],$data);

   $resultData['status'] = "done";
   }

elseif ($task == "snapshot")
   { 
   include ("../config/config-database.php");
   include ("../php/db-connect.php");

   date_default_timezone_set($db['timezone']);

   foreach ($m as $key=>$value)
           {
            checker($key);

            $query = "SELECT ID FROM ".$key;
            $result = mysqli_query($connect, $query);

            if(empty($result)) {
                         $query = "CREATE TABLE IF NOT EXISTS `".$key."` (
                                  `id` int(10) NOT NULL AUTO_INCREMENT,
                                  `label` varchar(50) DEFAULT NULL,
                                  `power` decimal(5,1) DEFAULT NULL,
                                  `powerfactor` decimal(4,3) NOT NULL,
                                  `voltage` smallint(4) NOT NULL,
                                  `current` decimal(4,3) NOT NULL,
                                  `port` tinyint(1) DEFAULT NULL,
                                  `thismonth` mediumint(8) unsigned DEFAULT NULL,
                                  `output` tinyint(1) DEFAULT NULL,
                                  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
                         $result = mysqli_query($connect, $query); }
           }



    
    foreach ($sensorData as $key=>$value)
            {
            $port = $value['PORT'];
            $device = $value['DEVICE'];
            $label =  $m[$device]['outlet'][$port]['label'];
            $insert = "INSERT INTO ".$device." (port,label,output,power,powerfactor,current,voltage,thismonth) VALUES ('".$port."','".$label."','".$value['OUTPUT']."','".$value['POWER']."','".$value['POWERFACTOR']."','".$value['CURRENT']."','".$value['VOLTAGE']."','".$value['THISMONTH']."')";
            $resultData['query'] .= $insert;
            $exec = mysqli_query($connect,$insert);
            }


   $resultData['status'] = "done";
   }

elseif ($task == "rules")
   {
   checker();
   checkRules();

   $resultData['outlets'] = $outletsResult;
   $resultData['rules'] = $rulesResult;
   }

   else
   {

   foreach ($m as $key=>$value)
   {
    checker($key);
   }
   
   //echo("<pre>");
   //print_r($sensorData);
   
   checkRules();
 
   $resultData['other'] = $otherData;
   $resultData['outlets'] = $sensorData;
   $resultData['rules'] = $rulesResult;
   }


echo json_encode($resultData);

?>
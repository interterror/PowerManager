<?php
ob_start();
session_start();
error_reporting(0);
//error_reporting(E_ALL);

//unset($_SESSION);
if (!$_SESSION['user']) { header( 'Location: login-server.php' ); }

include ("config/config-devices.php");
include ("config/config-rules.php");
include ("config/config-gui.php");
include ("config/config-database.php");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
        <title>POWER MANAGER - SERVER</title>

        <link href="css/outlets.css" rel="stylesheet" type="text/css" />
        <link href="css/graphs.css" rel="stylesheet" type="text/css" />
        <link href="css/rules.css" rel="stylesheet" type="text/css" />
        <link href="css/server.css" rel="stylesheet" type="text/css" />

        <script src="js/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

        <style>

        </style>


<script>
$(document).ready(function () {

var user = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
var writeIp = '<?php echo $db['writeIp']; ?>';

var refreshHandle;

var defaultDesktop = '<?php echo $gui['defaultDesktop']; ?>';

var snapshotInterval = <?php echo $db['snapshotInterval']; ?>;
var refreshInterval = <?php echo $gui['refreshInterval']; ?>;

var snapshotStartup = '<?php echo $db['snapshotStartup']; ?>';

var disableGraphUpdateWhenSaving = '<?php echo $db['disableGraphUpdateWhenSaving']; ?>';

var outletsColumns = <?php echo $gui['outlets']['columns']; ?>;
var graphsColumns = <?php echo $gui['graphs']['columns']; ?>;
var rulesColumns = <?php echo $gui['rules']['columns']; ?>;

var snapshotStatus = "stop";
var refreshStatus = "stop";

if (refreshInterval > 0) { refreshStatus = "go"; setTimeout(getUpdates,refreshInterval*1000); }

if (snapshotStartup == "on") { snapshotStatus = "go"; makeSnapshot(); }


rearrange();
$(window).resize(rearrange);


function rearrange()
         {        
           var outletCount = $(".outlet").length;
           var outletRows = Math.ceil(outletCount/outletsColumns);
           $(".outlet").css({"width":(100/outletsColumns)+"%","height":(100/outletRows)+"%"});

           var rulesCount = $("#desktop-rules").children(".rule").length;
           var rulesRows = Math.ceil(rulesCount/rulesColumns);
           $(".rule").css({"width":(100/rulesColumns)+"%","height":(100/rulesRows)+"%"});         
         }





                function getUpdates()
                         {
                          console.log("GET UPD"); clearTimeout(refreshHandle);
                           $(".outlet").off("click");
                           $(".outlet").removeClass().addClass("outlet change"); 
                           $(".rule").removeClass().addClass("rule change"); 

                           $("#buttonRefreshData").off("click").removeClass().addClass("button mainButton change");

                           $.ajax({ method: "GET",
                                  dataType: "json",
                                       url: "ajax/ajax-scan.php",
                                      data: {  }
                                 }).always(function (data) { console.log(data);
                                                             updateOutlets(data['outlets']);
                                                             updateRules(data['rules']); 
                                                             $(".loader").css("display","none");                                                             

                                                             $("#buttonRefreshData").click(getUpdates).removeClass().addClass("button mainButton unchosen");

                                                             if (refreshStatus == "go") { refreshHandle = setTimeout(getUpdates,refreshInterval*1000); } 

                                                             
                                                           });                  
                         }



                function updateRules(data)
                         {
                          $("#desktop-rules").empty();
                           $.each(data,function(key,value)
                                 {
                                 var rule = $("#objects").children(".rule").clone();

                                 rule.find(".rTitle").html(value['title']);

                                 var conds = "";
                                 $.each(value['condition'],function(key,value) { conds += "Condition #"+key+": "+value+"<br>"; });
                                 rule.find(".rCondition").html(conds);

                                 var acts = "";
                                 $.each(value['action'],function(key,value) { acts += "Action #"+key+": "+value+"<br>"; });
                                 rule.find(".rAction").html(acts);

                                 if (value['elseaction'] != null) {
                                 var eacts = ""; 
                                 $.each(value['elseaction'],function(key,value) { eacts += "Else Action #"+key+": "+value+"<br>"; });
                                 rule.find(".rElseAction").html(eacts);
                                 }

                                 var repeat = "";
                                 if (value['repeat'] != null) repeat = "Repeat: "+value['repeat']; 
                                 if (value['repeated'] != null) repeat += " / "+value['repeated']; else repeat += " / 0";

                                 rule.find(".rRepeat").html(repeat);

                                 $("#desktop-rules").append(rule);

                                 rule.attr("id",value['id']);

                                 var statusClass = "inactive";
                                 if (value['status'] == "TRUE") statusClass = "active";
                                 $(".rule[id='"+key+"']").removeClass().addClass("rule "+statusClass);               
                                 });

                           rearrange();
         
                         }

                function updateOutlets(data)
                         {
                           $.each(data,function(key,value)
                                 {
                                 var statusClass = "inactive";
                                 if (value['OUTPUT'] == 1) statusClass = "active";
                                 $(".outlet[data-device='"+value['DEVICE']+"'][data-outlet='"+value['PORT']+"'").removeClass().addClass("outlet "+statusClass);
                                 });

                           $(".outlet").click(switchOutlet);   
                         }

                function switchOutlet()
                         {
                         var outlet  = $(this);
                         var idevice = outlet.attr("data-device");
                         var imode   = "on";
                         var ioutlet = outlet.attr("data-outlet");

                         if (outlet.hasClass("active")) imode = "off";

                         outlet.off("click");
                         outlet.removeClass().addClass("outlet change"); 

                           $.ajax({ method: "GET",
                                       url: "ajax/ajax-scan.php",
                                      data: { device: idevice, mode: imode, outlet: ioutlet, task: "switch" }
                                  })
                                       .always(function (data) { console.log(data);

                                                               if (imode == "on")  outlet.removeClass().addClass("outlet active");
                                                               if (imode == "off") outlet.removeClass().addClass("outlet inactive");

                                                               outlet.click(switchOutlet);
                                                                
                                                               });
                         }


                function makeSnapshot(device)
                         {
                          if (snapshotStatus != "go") { $("#data-save-title").html("<h1>DATA COLLECTION STOPPED</h1>"); return false; } else
                                                        $("#data-save-title").html("<h1>DATA COLLECTION RUNNING</h1>");

                           $("#buttonSaveData").off("click").removeClass().addClass("button mainButton change");

                           $.ajax({ method: "GET",
                                  dataType: "json",
                                       url: "ajax/ajax-scan.php",
                                      data: { device : device, task: "snapshot" }
                                 }).always(function (data) { console.log(data);
                                                             if (snapshotStatus == "go") { setTimeout(makeSnapshot,snapshotInterval*1000); $("#buttonSaveData").removeClass().addClass("button mainButton active"); } else { $("#buttonSaveData").removeClass().addClass("button mainButton inactive"); }
                                                             $("#buttonSaveData").click(saveData);

                                                             var currentdate = new Date();
                                                             var datetime = currentdate.getDate() + "/" + (currentdate.getMonth()+1)  + "/" 
                                                                                                  + currentdate.getFullYear() + " @ "  
                                                                                                  + currentdate.getHours() + ":"  
                                                                                                  + currentdate.getMinutes() + ":" 
                                                                                                  + currentdate.getSeconds();
                                                             $("#desktop-graphs-log").prepend("<h1>"+datetime+": DATA SAVED</h1>");

                                                           });                  
                         }



getUpdates();



$(".changeDesktop").click(changeDesktop);
$("#buttonSaveData").click(saveData);

updateMainButtons();

$("div[data-desktop='"+defaultDesktop+"']").click();

function changeDesktop()
         {
          $(".changeDesktop").removeClass().addClass("button changeDesktop unchosen");
           var el = $(this);
           var desk = el.attr("data-desktop");
         
           $(".desktop").css("z-index",444);
           $("#desktop-"+desk).css("z-index",555);

           el.removeClass().addClass("button changeDesktop active");
         }

function saveData()
         {
          if (snapshotStatus == "stop") snapshotStatus = "go"; else snapshotStatus = "stop";
          updateMainButtons();
         }

function updateMainButtons() 
         {
          if (snapshotStatus == "go") buttonHighlight = "active"; else buttonHighlight = "inactive";
          $("#buttonSaveData").removeClass().addClass("button mainButton "+buttonHighlight);

          if (snapshotStatus == "go") makeSnapshot();

          $("#snapshotIntervalDiv").html(snapshotInterval);

          if (snapshotStatus != "go") { $("#data-save-title").html("<h1>DATA COLLECTION STOPPED</h1>"); return false; } else
                                        $("#data-save-title").html("<h1>DATA COLLECTION RUNNING</h1>");
         }






});



</script>

</head>



    <body>
    
    <div id="mainMenu">

    <div class="button mainButton" id="buttonSaveData"><h1>SAVE DATA (<span id='snapshotIntervalDiv'><?php echo $db['snapshotInterval']; ?></span>)</h1></div
   ><div class="button mainButton unchosen" id="buttonRefreshData"><h1>REFRESH (<?php echo $gui['refreshInterval']; ?>)</h1></div
   ><div class="button mainButton changeDesktop unchosen" data-desktop="outlets"><h1>OUTLETS</h1></div
   ><div class="button mainButton changeDesktop unchosen" data-desktop="graphs"><h1>DATA COLLECTION</h1></div
   ><div class="button mainButton changeDesktop unchosen" data-desktop="rules"><h1>RULES</h1></div>
    </div>

    <div id="fader" class="loader"></div>
    <div id="status-info" class="loader">
    <div id="status-info-inner"><p>Scanning outlets...</p></div>
    </div>



    <div id="desktop-outlets" class="desktop">

    <?php

    foreach ($m as $key=>$value)
    {
      $device = $key;
      foreach ($m[$key]['outlet'] as $key=>$value)
      { 
      echo('<div class="outlet change" data-type="switcher" data-name="'.$key.'" data-outlet="'.$key.'" data-device="'.$device.'"><div class="outlet-title">'.$device.'<br>'.$value['label'].'</div></div>');
      }
    }

    ?>
    </div>

    <div id="desktop-graphs" class="desktop">
      <div id="desktop-graphs-info"><span id="data-save-title"><h1>DATA COLLECTION STOPPED</h1></span><div id="desktop-graphs-log"></div></div>
    </div>

    <div id="desktop-rules" class="desktop">
    </div>

        <div id="objects">
      <div class='chart_menu'><div class='chart_title'></div><div class='button chart_changetype' data-type='power'><h1>PWR</h1></div><div class='button chart_changetype' data-type='voltage'><h1>VLTG</h1></div><div class='button chart_changetype' data-type='current'><h1>CURR</h1></div><div class='button chart_changetype' data-type='powerfactor'><h1>PWRFCTR</h1></div><div class='button chart_changetype' data-type='thismonth'><h1>MNTH</h1></div><div class='button chart_changetype' data-type='output'><h1>OTPT</h1></div></div>

      <div class="rule">
         <div class="rule_inner">
            <div class="rule_record rTitle"></div>
            <div class="rule_record rCondition"></div>
            <div class="rule_record rAction"></div>
            <div class="rule_record rElseAction"></div>
            <div class="rule_record rRepeat"></div>
         </div>
      </div>

    </div>

    </body>
</html>

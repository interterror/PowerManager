<?php
ob_start();
session_start();
//error_reporting(E_ALL);

include ("config-devices.php");
include ("config-rules.php");
include ("config-gui.php");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
        <title></title>

        <script src="jquery-2.1.4.min.js"></script>
        <script src="jquery.touchSwipe.min.js"></script>
            <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <script type="text/javascript">
    
    $(document).ready(function () {

var columns = <?php echo $gui['rules']['columns']; ?>;

    var rulesData = new Object();

function rearrange()
         {        
           var rulesCount = $(".rule").length;
           var rows = Math.ceil(rulesCount/columns);
           $(".rule").css({"width":(100/columns)+"%","height":(100/rows)+"%"});
         
         }

$(window).resize(rearrange);
rearrange();

function refresh(device)
         {
          var device = "EraCoal";

           $(".rule").removeClass().addClass("rule change");
               $.ajax({ method:"GET",
                       dataType:"json",
                            url:"ajax-scan.php",
                            data: { task: "rules", device: device } })
             
                                          .always(function(data){  console.log(data); rulesData = data['rules']; updateRules(); 
         
                                           if (status == "go")
                                           {
                                           interval  = $("input[name='interval']").val()*1000;
                                           setTimeout(refresh,interval);
                                           }
         
         
                                                                });
         }

var status = "stop";
var interval  = $("input[name='interval']").val()*1000;

$("#bRefresh").click(refresh);
$("#bStart").click(function(){ status = "go"; interval  = $("input[name='interval']").val()*1000; setTimeout(refresh,interval); });
$("#bStop").click(function(){ status = "stop"; });

                function updateRules()
                {
                  $.each(rulesData,function(key,value)
                        {
                          console.log(key+" "+value['status']);
                        var statusClass = "inactive";
                        if (value['status'] == "TRUE") statusClass = "active";
                        $(".rule[id='"+key+"']").removeClass().addClass("rule "+statusClass);

                        $(".loader").css("display","none");
      
                        });

                }


});
    </script>

    <style>
h1 { font-family: Verdana,sans-serif; font-size: 12pt; }

html,body { width: 100%; height: 100%; margin: 0; padding: 0; overflow: hidden; }
#rules { width: 100%; height: 90%; position: relative; margin: 0; padding: 0; }
#menu { width: 100%; height: 5%; position: relative; margin: 0; padding: 0; z-index: 9999; }
#bottommenu { width: 100%; height: 5%; position: relative; margin: 0; padding: 0; z-index: 9999;  }
.rule { width: 48%; height: 25%; position: relative; display: inline-table; border: 1px solid #CCC; margin: 1%; box-sizing: border-box; border-spacing: collapse; }
.rule_inner { display: table-cell; vertical-align: middle; font-family: Verdana,sans-serif; text-align: center; font-size: 15pt; width: 50%; }


.chart_title { width: 100%; height: 5%; position: absolute; z-index: 999; }
.chart_graph { width: 100%; height: 100%; position: relative; display: block;} 

.chart_menu { width: auto; height: 5%; position: absolute; margin: 0; padding: 0; z-index: 9999; right: 0; top: 0; }


.button { width: auto; height: 90%; position: relative; display: inline-table; border: 1px solid #CCC; cursor: pointer; 
  font-family: Verdana,sans-serif; font-size: 12pt; padding: 5px; box-sizing: border-box; margin: 3px;  }
.button_inner { width: auto; height: 90%; position: relative; display: table-cell;vertical-align: middle; padding: 3px; }

.chart_remove { width: auto; height: 5%;  }
.chart_thismonth { width: auto; height: 5%; }

            .active { background-color: #b1ff6c; }
            .inactive { background-color: #ff7474; }
            .change { background-color: #fff959; }
    </style>
</head>



    <body>
    <div id="menu">
<div class="button" id="bRefresh">REFRESH</div><div class="button" id="bStart">START</div><div class="button" id="bStop">STOP</div><div class="button" id="bInterval"><input type="text" name="interval" value="5" /></div>
    </div>



    <div id="rules">
    <?php
     foreach ($rule as $key=>$value)
             {
             $rulesResult[$key]['id'] = $key;
             $rulesResult[$key]['title'] = $value['title'];


             foreach ($value['condition'] as $ckey=>$cvalue)
                     {
                     $rulesResult[$key]['condition'] .= "Condition #".$ckey.": ".$cvalue."<br>"; 
                     }

             foreach ($value['action'] as $akey=>$avalue)
                     {
                     $rulesResult[$key]['action'] .= "Action #".$akey.": ".$avalue."<br>"; 
                     }
             foreach ($value['elseaction'] as $ekey=>$evalue)
                     {
                     $rulesResult[$key]['elseaction'] .= "Else Action #".$ekey.": ".$evalue."<br>"; 
                     }

             echo('<div id="'.$rulesResult[$key]['id'].'" class="rule">
                   <div class="rule_inner">
              <div class="rule_record">'.$rulesResult[$key]['title'].'</div><div class="rule_record">'.$rulesResult[$key]['condition'].'</div><div class="rule_record">'.$rulesResult[$key]['action'].'</div><div class="rule_record">'.$rulesResult[$key]['elseaction'].'</div></div></div>');
             }
         ?>
</div>


    </body>
</html>

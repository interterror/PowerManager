<?php
ob_start();
session_start();
error_reporting(E_ALL);

if (!$_SESSION['user']) { header( 'Location: login.php' ); }

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

        <style>
            html,body { margin:  0; padding:  0; position:  relative; width:  100%; height:  100%; overflow: hidden;
              -webkit-user-select: none;  
              -moz-user-select: none;    
              -ms-user-select: none;      
              user-select: none; }

              p { font-family: Verdana,sans-serif; text-align: center; font-size: 15pt; }

            #desktop { width: 100%; height: 100%; position: absolute; display: block; left: 0; top: 0; }
            .slide { width: 100%; height: 100%; position: relative; display: inline-block; margin: 0; padding: 0;}

            .row { width:  100%; height:  25%; position:  relative; display:  block; }
            .outlet       { width:  33%; height:  100%; position: relative; display:  inline-table; background-color:  #EEEEEE; border: 2px solid white; cursor:  pointer; box-sizing: border-box; }
            .outlet-title { display: table-cell; vertical-align: middle; font-family: Verdana,sans-serif; text-align: center; font-size: 15pt; width: 50%; }

            .active { background-color: #b1ff6c; }
            .inactive { background-color: #ff7474; }
            .change { background-color: #fff959; }

            .ui-loader { display: none !important; }

            #volumator { position: absolute; left: 0; top: 0; z-index: 9999; background-color: white; display: none; }

            #volumator .cell { position: relative; width: 25%; height: 25%; display: inline-table; text-align: center; cursor: pointer; box-sizing: border-box; border: 0; border-right: 1px solid white; border-bottom: 1px solid white; }

            #fader { width: 100%; height: 100%; background-color: white; opacity: 0.8; position: absolute; left: 0; top: 0; z-index: 999; }
            #status-info { width: 100%; height: 100%; display: table; position: absolute; z-index: 9999; }
            #status-info-inner { width: 100%; height: 100%; display: table-cell; vertical-align: middle; text-align: center; }
        </style>


<script>
$(document).ready(function () {


var columns = <?php echo $gui['outlets']['columns']; ?>;

rearrange();

function rearrange()
         {        
           var outletCount = $(".outlet").length;
           var rows = Math.ceil(outletCount/columns);
           $(".outlet").css({"width":(100/columns)+"%","height":(100/rows)+"%"});
         
         }

$(window).resize(rearrange);




               getStatuses("EraCoal");
               getStatuses("EraTimber");

               $(".loader").css("display","none");


                function getStatuses(device)
                         {
                           $(".outlet[data-device='"+device+"']").off("click");
                           $(".outlet[data-device='"+device+"']").removeClass().addClass("outlet change"); 

                           $.ajax({ method: "GET",
                                  dataType: "json",
                                       url: "ajax-scan.php",
                                      data: { device : device }
                                 }).always(function (data) { console.log(data);
                                                             updateOutlets(device,data['outlets']);
                                                           });                  
                         }

                function updateOutlets(device,data)
                         {
                           $.each(data,function(key,value)
                                 {
                                 var statusClass = "inactive";
                                 if (value['output'] == 1) statusClass = "active";
                                 $(".outlet[data-device='"+device+"'][data-outlet='"+value['port']+"'").removeClass().addClass("outlet "+statusClass);
                                 });

                           $(".outlet[data-device='"+device+"']").click(switchOutlet);   
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
                                       url: "ajax-scan.php",
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

                           $.ajax({ method: "GET",
                                  dataType: "json",
                                       url: "ajax-scan.php",
                                      data: { device : device, task: "snapshot" }
                                 }).always(function (data) { console.log(data);
                                                           });                  
                         }

makeSnapshot("EraTimber");

            });
</script>

</head>



    <body>

    <div id="fader" class="loader"></div>
    <div id="status-info" class="loader">
    <div id="status-info-inner"><p>Scanning outlets...</p></div>
    </div>



    <div id="desktop">

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

    </div>

    </body>
</html>

<!DOCTYPE html>
<html>
    <head>
        <title>Interactive Line Graph</title>

<style>
* { margin:0; padding:0; } /* to remove the top and left whitespace */

html, body { width:100%; height:100%; } /* just to be sure these are full screen*/

canvas { display:block; } /* To remove the scrollbars */

#area_graph { width: 100%; height: 80%; margin: 0px 0 25px 0; }

#area_control { width: 100%; height: auto; }

.menu { width: 100%; height: auto; display: block; }
.button { position: relative; width: 100px; height: auto; display: inline-block; margin: 0 10px 10px 0; cursor: pointer; text-align: center; border: 1px solid #666; box-sizing: border-box; }
.selected { background-color: #ff8888; }
</style>

        <script src="js/jquery-2.1.4.min.js"></script>
        <script>

            


            $(document).ready(function() {

var graphCanvas = document.getElementById('graph');
var range = "day";
var port = 1;
var recs = 9;
var type = "power";
var unit = "kWh";
var device = "EraCoal";

function arrange(){
var ch = $("#area_control").outerHeight();
var wh = $(window).outerHeight();

$("#area_graph").height(wh-ch-25);

var w = $("#area_graph").outerWidth();
var h = $("#area_graph").outerHeight();

graphCanvas.width=(w); // in pixels
graphCanvas.height=(h); // in pixels

}

$(window).resize(arrange);
arrange();

function graphTask()
{

var task = new Object();
task['type'] = type;
task['port'] = port;
task['device'] = device;
task['range'] = range; 
task['nth'] = recs; console.log(task);

                 $.ajax({ method:"POST",
                               dataType:"json",
                                    url:"ajax/ajax-chart.php", data: task })
    
                                          .always(function(data){ console.log(data);  drawGraph(data['chart']);
                                                                });
}

$(".device").click(function(){ $(".device").removeClass("selected"); $(this).addClass("selected"); port = $(this).attr("name"); device = $(this).attr("data-device"); graphTask(); });
$(".range").click(function(){ $(".range").removeClass("selected"); $(this).addClass("selected"); range = $(this).attr("name"); graphTask(); });
$(".recs").click(function(){ $(".recs").removeClass("selected"); $(this).addClass("selected"); recs = $(this).attr("name")-1; graphTask(); });
$(".type").click(function(){ $(".type").removeClass("selected"); $(this).addClass("selected"); type = $(this).attr("name"); unit = $(this).attr("data-unit"); graphTask(); });


function drawGraph(graphData){

            var graph;
            var xPadding = 100;
            var yPadding = 50;
            // Returns the max Y value in our data list
            function getMaxY() {
                var max = 0;
                
                for(var i = 0; i < data.length; i ++) {
                    if(data[i][1] > max) {
                        max = data[i][1];
                    }
                } 
                max = Math.ceil(max); 
                return max;
            }
            
            // Return the x pixel for a graph point
            function getXPixel(val) {
                return ((graph.width() - (xPadding*2)) / (data.length-1)) * val + (xPadding * 1);
            }
            
            // Return the y pixel for a graph point
            function getYPixel(val) {
                var result = graph.height() - (((graph.height() - (yPadding*2)) / getMaxY()) * val) - yPadding;
                return result;
            }

    var data = graphData;

                graph = $('#graph');
                var c = graph[0].getContext('2d');           


                arrange(); console.log(graph.width()+" x "+graph.height());
                
                c.lineWidth = 2;
                c.strokeStyle = '#333';
                c.font = '8pt sans-serif';
                c.textAlign = "center";
                
                // Draw the axises
                c.beginPath();
                c.moveTo(xPadding, yPadding);
                c.lineTo(xPadding, graph.height() - yPadding);
                c.lineTo(graph.width()-xPadding, graph.height() - yPadding);
                c.stroke();
                

                
                // Draw the Y value texts
                c.textAlign = "right"
                c.textBaseline = "middle";
                c.lineWidth = 1;
                c.strokeStyle = '#CCC';

                var maxa = getMaxY(); if (maxa == 0) return false; console.log(maxa);
                for(var i = 0; i <= maxa; i += maxa/10) {
                    var val = Math.round(i * 100) / 100;
                    var kc = (i/1000)*5;
                    c.fillText(val+" ("+(Math.round(kc * 100) / 100)+")", xPadding - 10, getYPixel(i)); 

                    c.beginPath();
                    c.moveTo(xPadding, getYPixel(i));
                    c.lineTo(graph.width()-xPadding, getYPixel(i));
                    c.stroke();

                    c.beginPath();
                    c.arc(xPadding, getYPixel(i), 2, 0, Math.PI * 2, true);
                    c.fill();

                }
                
                c.lineWidth = 2;
                c.strokeStyle = '#f00';
                
                // Draw the line graph
                c.beginPath();
                c.moveTo(getXPixel(0), getYPixel(data[0][1]));
                for(var i = 1; i < data.length; i ++) {
                    c.lineTo(getXPixel(i), getYPixel(data[i][1]));
                }
                c.stroke();
                
                // Draw the dots
                c.fillStyle = '#333';
                c.textAlign = "left";

                // info every nth
                var val_length = maxa.toString().length * 20; console.log(maxa+ " "+val_length);
                var w = $(window).outerWidth();
                var possible_no_infotexts = Math.ceil(w/val_length);
                var wide_enough = Math.floor(w/50);
                var nth = Math.ceil(data.length / possible_no_infotexts); console.log(possible_no_infotexts+" / "+data.length);
                if (data.length < possible_no_infotexts) nth = 1;
                if (data.length < wide_enough) wide_enough = true; else wide_enough = false;

                // Draw the X value texts
                for(var i = 0; i < data.length; i += 1) {
                    c.beginPath();
                    c.arc(getXPixel(i), graph.height()-50, 2, 0, Math.PI * 2, true);
                    //console.log(i+" "+data[i][2]+" "+data[i][1]+" "+i % nth+" "+nth);
                    if (i % nth === 0) { 
                    
                    c.fillText(data[i][0], getXPixel(i), graph.height() - yPadding + 20);
                    c.fillText(data[i][2], getXPixel(i), graph.height() - yPadding + 30);
                    
                    }
                    c.fill();
                }

                // draw thin lines
                c.lineWidth = 1;
                c.strokeStyle = '#CCC';

                var prevDateDay = new Date();
                var prevDateWeek = new Date();
var start = new Date().getTime();

                for(var i = 0; i < data.length; i += 1) {

                    
c.strokeStyle = "#CCC"; c.lineWidth = 1; 

                var date = new Date(data[i][3]);
// Hours part from the timestamp
var hours = date.getHours();
// Minutes part from the timestamp
var minutes = "0" + date.getMinutes();
// Seconds part from the timestamp
var seconds = "0" + date.getSeconds();

// Will display time in 10:30:23 format
var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
var diff = (Math.abs(date - prevDateDay));
var diffWeek = (Math.abs(date - prevDateWeek));  
var hours = diff / 1000 / 60 / 60;
var hoursWeek = diffWeek / 1000 / 60 / 60;

if (hours >= 24)  { c.lineWidth = 2; c.strokeStyle = "#999"; prevDateDay = date; }
if (hoursWeek >= 168) { c.lineWidth = 3; c.strokeStyle = "#666"; prevDateWeek = date; } 

                c.beginPath();
                c.moveTo(getXPixel(i), graph.height()-50);
                c.lineTo(getXPixel(i), yPadding);
                c.stroke();

                }          

var end = new Date().getTime();
console.log("EXEC TIME: "+(end-start));

                var nth_counter = 0;

                for(var i = 0; i < data.length; i++) {  
                    c.beginPath();
                    c.arc(getXPixel(i), getYPixel(data[i][1]), 4, 0, Math.PI * 2, true);
                    //console.log(nth_counter+" "+data[i][2]+" "+data[i][1]+" "+nth_counter % nth+" "+nth);
                    nth_counter++;
                    if ((i>0) && (data[i][1]>0)) {
                        if (nth_counter % nth === 0) { 

                            var ty = getYPixel(data[i][1]);
                            if (ty > (graph.height()-100)) ty -= 50; else ty += 10;
                            c.font = 'bold 10pt sans-serif';
                    var label = data[i][1];
                    if (wide_enough) label += " "+unit;
                    c.fillText(label, getXPixel(i), ty+10);
                    c.font = '8pt sans-serif';
                    c.fillText(data[i][0], getXPixel(i), ty+20);
                    c.fillText(data[i][2], getXPixel(i), ty+30);}} else if (nth_counter % nth === 0) nth_counter--;

                    c.fill();
                }
}

            });
        </script>
    </head>
    <body><div id="area_graph">
        <canvas id="graph" width="2000" height="1100">   
        </canvas></div>
        <div id="area_control">
        <div class="menu">
        <div class="button device" name="1" data-device="EraCoal">INFINITY</div><div class="button device" name="2" data-device="EraCoal">ERA</div><div class="button device" name="3" data-device="EraCoal">AV</div><div class="button device" name="4" data-device="EraCoal">HAL</div><div class="button device" name="5" data-device="EraCoal">RECEIVER</div><div class="button device" name="6" data-device="EraCoal">LIGHTS</div><div class="button device" data-device="EraTimber" name="1">LUX</div><div class="button device" data-device="EraTimber" name="2">RECHARGE</div><div class="button device" name="3" data-device="EraTimber">OTHER</div></div>
        <div class="menu"><div class="button range" name="day">DAY</div><div class="button range" name="week">WEEK</div><div class="button range" name="month">MONTH</div><div class="button range" name="all">ALL</div></div>
        <div class="menu"><div class="button recs" name="10">10</div><div class="button recs" name="20">20</div><div class="button recs" name="30">30</div><div class="button recs" name="40">40</div><div class="button recs" name="50">50</div><div class="button recs" name="60">60</div><div class="button recs" name="70">70</div><div class="button recs" name="80">80</div><div class="button recs" name="90">90</div><div class="button recs" name="100">100</div><div class="button recs" name="150">150</div><div class="button recs" name="200">200</div></div>
        <div class="menu"><div class="button type" name="kwh" data-unit="kWh">KWH</div><div class="button type" name="power" data-unit="W">POWER</div><div class="button type" data-unit="A" name="current">CURRENT</div><div class="button type" name="output" data-unit="I/O">OUTPUT</div></div>
    </body>
</html>


<?php

// RULE IS DEFINED BY
// ID - unique key of rule array
// TITLE - your custom title

/*
 CONDITION - is constructed as a serie of parameters delimited by ";"
 1) TARGET DEVICE - label as defined in config-devices.php
 2) PARAMETER - can be power,powerfactor,voltage,current,output
 3) COMPARATOR - < or >
 4) VALUE - compared value 

 example: PC;POWER;>;100 = If outlet defined as PC has higher power than 100.

 SCHEDULE - specific condition
 If first parameter is SCHEDULE, specific rule is applied.
 2) Start time
 3) End time
 4) [OPTIONAL] Day of Week (1 - Monday, 7 - Sunday)
 ------------------------------------------------------------------------------

 ACTION - similar to condition
 1) TARGET DEVICE - label as defined in config-devices.php
 2) PARAMETER - can be output
 3) STATUS - can be ON or OFF

 example: LIGHT;OUTPUT;OFF = Set outlet defined as LIGHT OFF.
 ---------------------------------------------------------------------------

ELSE ACTION - same as action, is executed when condition is not met
-------------------------------------------------------------------

REPEAT - how many times can be action executed (0 = INFINITE) - counter is resetted when conditions are not met
-----------------------------------------------

CONDITIONS, ACTIONS, and ELSE ACTIONS are arrays. You can set any number of them for single rule.


EXAMPLE:
$rule['PC-ON-NIGHT-LIGHTS-ON']['title'] = "SWITCH LIGHTS ON WHEN PC IS ON, AND ITS NIGHT TIME";
$rule['PC-ON-NIGHT-LIGHTS-ON']['condition'][0] = "PC;POWER;>;10";
$rule['PC-ON-NIGHT-LIGHTS-ON']['condition'][1] = "SCHEDULE;16:00:00;4:00:00;";
$rule['PC-ON-NIGHT-LIGHTS-ON']['action'][0] = "LIGHTS;OUTPUT;ON";
$rule['PC-ON-NIGHT-LIGHTS-ON']['repeat'] = 1;
*/





?>

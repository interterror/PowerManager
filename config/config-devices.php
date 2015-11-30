<?php

// LIST YOUR DEVICES
// OUTLET LABEL MUST BE UNIQUE
/*
EXAMPLE:
$m['mPower']['ip'] = "10.0.0.80";
$m['mPower']['logname'] = "user";
$m['mPower']['logpass'] = "pass";
$m['mPower']['outlet'][1]['label'] = "PC";
$m['mPower']['outlet'][2]['label'] = "SERVER";
$m['mPower']['outlet'][3]['label'] = "AUDIO/VIDEO";
$m['mPower']['outlet'][4]['label'] = "LIGHTS";
$m['mPower']['outlet'][5]['label'] = "PROJECTOR";
$m['mPower']['outlet'][6]['label'] = "FAN";
*/

$m['EraCoal']['ip'] = "10.0.0.80";
$m['EraCoal']['logname'] = "admin";
$m['EraCoal']['logpass'] = "kafkaka";
$m['EraCoal']['outlet'][1]['label'] = "INFINITY";
$m['EraCoal']['outlet'][2]['label'] = "ERA";
$m['EraCoal']['outlet'][3]['label'] = "AUDIO/VIDEO";
$m['EraCoal']['outlet'][4]['label'] = "HAL";
$m['EraCoal']['outlet'][5]['label'] = "RECEIVER";
$m['EraCoal']['outlet'][6]['label'] = "LIGHTS";

$m['EraTimber']['ip'] = "10.0.0.90";
$m['EraTimber']['logname'] = "admin";
$m['EraTimber']['logpass'] = "kafkaka";
$m['EraTimber']['outlet'][1]['label'] = "LUX";
$m['EraTimber']['outlet'][2]['label'] = "RECHARGE";
$m['EraTimber']['outlet'][3]['label'] = "OTHER";

// OUTLETS SHOWN ON GRAPH PAGE - AVAILABLE GRAPHS: power,powerfactor,current,voltage,output
$m['EraCoal']['outlet'][1]['graph'] = "POWER";
$m['EraCoal']['outlet'][2]['graph'] = "POWER";
$m['EraCoal']['outlet'][3]['graph'] = "POWER";
$m['EraCoal']['outlet'][4]['graph'] = "POWER";
$m['EraCoal']['outlet'][5]['graph'] = "POWER";
$m['EraCoal']['outlet'][6]['graph'] = "POWER";
$m['EraTimber']['outlet'][1]['graph'] = "POWER";
$m['EraTimber']['outlet'][2]['graph'] = "POWER";
$m['EraTimber']['outlet'][3]['graph'] = "POWER";
?>

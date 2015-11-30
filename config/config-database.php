<?php

  // YOUR TIMEZONE
  $db['timezone'] = "Europe/Prague";

  // IP ALLOWED TO WRITE DATA TO DB
  $db['writeIp'] = "10.0.0.100";

  // INTERVAL FOR DATA SAVING IN SECONDS
  $db['snapshotInterval'] = 60;

  // START SAVING DATA WITH APP START - on/off
  $db['snapshotStartup'] = "off";

  // DISABLE GRAPHS REFRESH WHEN SAVING DATA IS ENABLED (RECOMMENDED, BROWSER SOMETIMES CRASHES WHEN UPDATING GRAPHS)
  $db['disableGraphUpdateWhenSaving'] = "on";

  // GRAPH TIME INTERVAL - last, hour, today, lastweek, custom
  $db['graphInterval'] = "today";

  // SETTINGS FOR 'LAST' GRAPH INTERVAL - will load x last records
  $db['lastRecords'] = 100;

  // SETTINGS FOR CUSTOM GRAPH INTERVAL
  $db['startGraph'] = "2015-11-28 22:00:00";
  $db['endGraph'] = "2015-11-30 22:00:00";

  // YOUR MYSQL DB
  // APP NEEDS RIGHT TO CREATE TABLES - EACH DEVICE IS PROVIDED WITH SEPARATE TABLE FOR EASY MAINTAIN
  $cfg['MySQL_Server'] = '127.0.0.1';
  $cfg['MySQL_User']   = 'user';
  $cfg['MySQL_Passwd'] = 'password';
  $cfg['MySQL_DB']     = 'powermanager';









?>

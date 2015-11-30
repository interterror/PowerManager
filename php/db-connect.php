<?php

  // CONNECTION ROUTINE
  $connect = mysqli_connect($cfg['MySQL_Server'], $cfg['MySQL_User'], $cfg['MySQL_Passwd'], $cfg['MySQL_DB']);

    if (mysqli_connect_errno()) { printf("Connect failed: %s\n", mysqli_connect_error());
                                  exit();
                                }
  
  mysqli_select_db($connect,$cfg['MySQL_DB']);
  mysqli_query($connect,"SET NAMES utf8");  
?>

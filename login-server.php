<?php
error_reporting(E_ALL);

   ob_start();
   session_start();

   unset($_SESSION['status']);
   unset($_SESSION['user']);

   $info = "";

include ("config/config-gui.php");
   // users array

   $uN[1] = $gui['logname'];
   $uP[1] = $gui['logpass'];



   if (isset($_POST['sender'])) {
   
                                $logname = mb_strtolower($_POST['logname'],'UTF-8');
                                $logpass = mb_strtolower($_POST['logpass'],'UTF-8');
                                
                                echo "L: ".$logname;
                                echo array_search($logname, $uN);

                                if (array_search($logname, $uN)) { if ($logpass == $uP[array_search($logname, $uN)]) { $_SESSION['status'] = "GRANTED";
                                                                                                                       $_SESSION['user'] = $logname;
                                                                                                                       header( 'Location: server.php' ); }; 
                                                                                                                     } 
                                                                                                                     else
                                                                                                                     { $_SESSION['status'] = "DENIED";
                                                                                                                       $info = "ACCESS DENIED.";	
                                                                                                                     }

                                if ($logname == "") { $info  = "ENTER LOGNAME.<br>"; }
                                if ($logpass == "") { $info .= "ENTER LOGPASS."; }  
                                
                                }   
  
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>POWER MANAGER LOGIN</title>
<link href="css/login.css" rel="stylesheet" type="text/css">

</head>

<body>
<div id="vertical">
<div id="logbox">
<table width="100%">
<tr><td colspan="2" id="head"><h2>POWER MANAGER LOGIN</h2></td></tr>
<form  style="padding: 0; margin: 0;" action="login-server.php" method="POST">
<tr><td><input type="text"      name="logname"  style="width: 140px" /></td><td><h1>NAME</h1></td></tr>
<tr><td><input type="password"  name="logpass"  style="width: 140px" /></td><td><h1>PASSWORD</h1></td></tr>
<tr><td colspan="2"><input name="sender" class="butt"  type="submit"  value="LOG IN"></td></tr>
</form>
</table>
</div>
<?php if ($info<>"") { printf ("<div id='error'><h1>%s</h2></div>",$info); } ?>
</div>
</body>
</html>

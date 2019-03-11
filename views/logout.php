<?php
include "/var/www/html/fuelwatchapp.com/admin/config/config.php";
include $function_and_classes;
session($root_domain_config);
if(!isset($_SESSION['username'])) {
    header("Location: fuelwatchapp.php"); 
    exit;
}
$fp = fopen($logout, 'a');
$date = date('m/d/Y h:i:s a', time());
fwrite($fp, $_SESSION['username']. "logged out: ".$date."\n");
$_SESSION['username'] = null;
header("Location: fuelwatchapp.php"); 
exit;
?>
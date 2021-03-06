<?php

include(__DIR__."/../../_settings/config.php");
include(__DIR__."/../classes/ChartService.class.php");
include(__DIR__."/../classes/Symbol.class.php");
include(__DIR__."/../../composer/vendor/autoload.php");

/******************************************************************************
* ONLY ALLOW EXISTING DOMAIN TO ACCESS
******************************************************************************/
header('Access-Control-Allow-Origin: https://' . $_SERVER[HTTP_HOST]);
//header('Access-Control-Allow-Origin: *');

/******************************************************************************
* PDO DATABASE
******************************************************************************/
try
{
 $PDO = new PDO("mysql:dbname={$database[database]}; host={$database[host]}",
                $database[username],
                $database[password]);
}
catch (PDOException $e)
{
 echo "Database Connection Error: " . $e->getMessage();
}

/******************************************************************************
* SESSION START 
******************************************************************************/
// session_name("CHARTSESSID");
// session_set_cookie_params(0, "/;SameSite=None;", "charts.tradeprofx.com", $secure = TRUE);
session_start();

/******************************************************************************
* ACCESS TYPE (default/loggedin) 
******************************************************************************/
$accesstype = "default";

if ($_SESSION["chartsettings"])
{
 $accesstype = "loggedin"; 
 $chartsettings = $_SESSION["chartsettings"]; 
}

?>
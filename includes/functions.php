<?php

include(__DIR__."/../../_settings/config.php");
include(__DIR__."/../classes/ChartService.class.php");
include(__DIR__."/../classes/Counter.class.php");

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

?>
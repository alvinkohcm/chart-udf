<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* TIME
******************************************************************************/
$chart = new ChartService($PDO);
$time = $chart->getLastUnixtime();

echo $time;

?>
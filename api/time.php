<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* TIME
******************************************************************************/
$chart = new ChartService($PDO);
echo $chart->getLastUnixtime();

?>
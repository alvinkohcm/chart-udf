<?php

include(__DIR__."/../includes/functions.php");

$symbol = $_GET[symbol];
$resolution = $_GET[resolution];
$from = $_GET[from];
$to = $_GET[to];

/******************************************************************************
* FETCH SYMBOL
******************************************************************************/
$chart = new ChartService($PDO);
$chart->setSymbol($symbol);
$chart->setResolution($resolution);
$chart->getHistoricalData($from, $to);

//history?symbol=EURUSD&resolution=D&from=1444977511&to=1476081571

?>
<?php

include(__DIR__."/../includes/functions.php");

$symbol = $_GET[symbol];
$resolution = $_GET[resolution];
$from = (int) $_GET[from];
$to = (int) $_GET[to];

//Note: Sometimes from is undefined (NaN), use default 10 second lookup
if (!$from && $to)
{
 $from = $to - 10; // 10 second backlog
}

/******************************************************************************
* FETCH SYMBOL
******************************************************************************/
include(__DIR__."/../classes/ChartService2.class.php");

$chart = new ChartService2($PDO);
$chart->debugmode = true;
$chart->setSymbol($symbol);
$chart->setResolution($resolution);
$chart->getHistoricalData($from, $to);

//history?symbol=EURUSD&resolution=D&from=1444977511&to=1476081571

?>
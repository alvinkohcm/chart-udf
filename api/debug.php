<?php

include(__DIR__."/../includes/functions.php");

$symbol = $_GET[symbol];
$resolution = $_GET[resolution];
$from = $_GET[from];
$to = $_GET[to];

// if (!ctype_digit($from))
// {
 // $from = $to - (1800 - 1);
// }

/******************************************************************************
* FETCH SYMBOL
******************************************************************************/
$chart = new ChartService($PDO);
$chart->debugmode = true;
$chart->setSymbol($symbol);
$chart->setResolution($resolution);
$chart->getHistoricalData($from, $to);


//history?symbol=EURUSD&resolution=D&from=1444977511&to=1476081571

?>
<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* CUSTOM COUNTERS
******************************************************************************/
if ($_SESSION[chartsettings][counters])
{
 $counters = $_SESSION[chartsettings][counters];
}

/******************************************************************************
* FETCH SYMBOL
******************************************************************************/
$id = $counters[$_GET[symbol]] ? $counters[$_GET[symbol]] : $_GET[symbol];
$symbol = new Symbol($PDO);
$symbol->fetch($id, $accesstype);

/******************************************************************************
* REPLACE COUNTERS WITH CUSTOM COUNTER DETAILS
******************************************************************************/
if ($counters)
{
 if ($counterid = array_search($symbol->ticker, $counters))
 {
  $symbol->symbol = $counterid;
  $symbol->name = $counterid;
  $symbol->description = $counterid;
 }
}

/******************************************************************************
* JSON
******************************************************************************/
echo json_encode($symbol,JSON_PRETTY_PRINT);

?>
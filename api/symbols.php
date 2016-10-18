<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* 
******************************************************************************/
$symbol = $_GET[symbol];

$counter = new Counter($PDO);
$counter->fetch($symbol);

echo json_encode($counter,JSON_PRETTY_PRINT);

?>
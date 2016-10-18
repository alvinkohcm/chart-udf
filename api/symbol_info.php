<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* DATABASE
******************************************************************************/
$DB = $PDO;

/******************************************************************************
* OUTPUT OBJECT
******************************************************************************/
$output = new stdClass();

/******************************************************************************
* FETCH EXCHANGE COUNTERS
******************************************************************************/
$params['exchangeid'] = $_GET['group'];

$query = "SELECT
          counter.counterid,
          counter.name AS countername,
          counter.description,
          counter.exchangeid,
          exchange.name AS exchangename,
          exchange.symboltype,
          symbol.*
          FROM counter
          LEFT JOIN symbol ON counter.symbol = symbol.symbol
          RIGHT JOIN exchange ON counter.exchangeid = exchange.exchangeid
          WHERE
          counter.exchangeid = :exchangeid";

$stmt = $DB->prepare($query);          
if ($stmt->execute($params))
{
 while($row = $stmt->fetch(PDO::FETCH_ASSOC))
 {
  $output->timezone = $row[timezone];
  $output->{'session-regular'} = $row[session];
  $output->{'exchange-listed'} = $row[exchangeid];
  $output->minmov = (int) $row[minmov];
  $output->minmov2 = (int) $row[minmov2];

  $output->symbol[] = $row[counterid];
  $output->description[] = $row[counterid];
  $output->pricescale[] = (int) $row[pricescale];
  $output->{'has-intraday'}[] = (int) $row[has_intraday];
  $output->{'has-no-volume'}[] = (int) $row[has_no_volume];
  $output->type[] = $row[symboltype];
  $output->ticker[] = $row[symbol];
 }
 
 echo json_encode($output, JSON_PRETTY_PRINT);
 exit;
}
          
          
/*

{
   "symbol": ["XAU", "EUR", "GBP"],
   "description": ["Gold", "EUR/USD", "GBP/USD"],
   "exchange-listed": "forex",
   "exchange-traded": "forex",
   "minmov": 1,
   "minmov2": 0,
   "pricescale": [1, 1, 100],
   "has-dwm": true,
   "has-intraday": true,
   "has-no-volume": [false, false, true],
   "type": ["forex", "forex", "forex"],
   "ticker": ["XAU A0-FX", "EUR A0-FX", "GBP A0-FX"],
   "timezone": "UTC",
   "session-regular": "24x7"
}

*/

?>
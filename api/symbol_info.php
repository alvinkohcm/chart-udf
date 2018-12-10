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
          symbol.symbol,
          symbol.name AS symbolname,
          IF (symbol.description != '', symbol.description, symbol.name) AS description,
          symbol.exchangeid,
          exchange.name AS exchangename,
          exchange.description AS exchange_description,
          exchange.symboltype,
          exchange.symboltype_description,
          symbol.*
          FROM symbol
          RIGHT JOIN exchange ON symbol.exchangeid = exchange.exchangeid
          WHERE
          symbol.exchangeid = :exchangeid";

$stmt = $DB->prepare($query);          
if ($stmt->execute($params))
{
 while($row = $stmt->fetch(PDO::FETCH_ASSOC))
 {
  $output->timezone = $row[timezone];
  $output->{'session-regular'} = $row[session];
  $output->{'exchange-listed'} = $row[exchange_description];
  $output->{'exchange-traded'} = $row[exchange_description];
  $output->minmov = (int) $row[minmov];
  $output->minmov2 = (int) $row[minmov2];

  $output->symbol[] = $row[symbolname];
  $output->description[] = $row[description];
  $output->pricescale[] = (int) $row[pricescale];
  $output->{'has-intraday'}[] = (int) $row[has_intraday];
  $output->{'has-no-volume'}[] = (int) $row[has_no_volume];
  $output->type[] = $row[symboltype_description];
  $output->ticker[] = $row[symbol];
 }
 
 echo json_encode($output, JSON_PRETTY_PRINT);
}
else
{
 //print_r($DB->errorInfo());
}

?>
<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* DATABASE
******************************************************************************/
$DB = $PDO;

/******************************************************************************
* BUILD QUERY CONDITIONS
******************************************************************************/
if ($_GET['limit'])
{
 $limit = $_GET['limit'] < 30 ? $_GET['limit'] : 30;
 $limit_filter = "LIMIT $limit";
}

if ($_GET['query'])
{
 $params['symbol'] = "%" . $_GET['query'] . "%";
 $params['symbolname'] = "%" . $_GET['query'] . "%";
 
 $where['symbol'] = "(symbol.symbol LIKE :symbol OR symbol.name LIKE :symbolname)"; 
}

if ($_GET['type'])
{
 $params['symboltype'] = $_GET['type'];
 $where['symboltype'] = "exchange.symboltype = :symboltype";
}

if ($_GET['exchange'])
{
 $params['exchangeid'] = $_GET['exchange'];
 $where['exchangeid'] = "symbol.exchangeid = :exchangeid";
}

/******************************************************************************
* CUSTOM COUNTERS / DETERMINE IF KEYWORDS MATCH ANY CUSTOM COUNTERIDs
******************************************************************************/
if ($counters = $_SESSION['chartsettings']['counters'])
{ 
 foreach ($counters AS $counterid => $symbol)
 {
  // Search based on custom counterid
  if(strpos($counterid, $_GET['query']) !== false) // Must use === operator
  {
   $search_counters[$counterid] = $symbol;
  }
 }

 //### ONLY SHOW COUNTERS THAT EXIST IN CUSTOM COUNTERS
 $where['counters'] = "symbol.symbol IN ('".implode("','", $counters)."')"; 
 
 //### SHOW ANY MATCHING COUNTERIDs
 if ($search_counters)
 {
  $where['symbol'] = "(symbol.symbol LIKE :symbol
                       OR symbol.name LIKE :symbolname
                       OR symbol.symbol IN ('".implode("','", $search_counters)."'))                       
                       ";
 }
}


/******************************************************************************
* ONLY SHOW COUNTERS WITH ACTIVE PRICES WITHIN 1 WEEK
******************************************************************************/
$where['active'] = "pricecache.unixtime > UNIX_TIMESTAMP(NOW()-INTERVAL 1 WEEK)";


/******************************************************************************
* WHERE CONDITION
******************************************************************************/
if (count($where)>0)
{
 $where_filter = "WHERE " . implode(" AND ", $where);
}


/******************************************************************************
* FIND MATCHING COUNTERS
******************************************************************************/
$output = array();

$query = "SELECT
          symbol.name AS symbol,
          symbol.name AS full_name,
          IF(symbol.description != '', symbol.description, symbol.name) AS description,
          exchange.description AS exchange,
          symbol.symbol AS ticker,
          exchange.symboltype_description AS type
          FROM symbol
          LEFT JOIN pricecache ON (symbol.symbol = pricecache.symbol AND pricecache.interval = 'day')
          LEFT JOIN exchange USING (exchangeid)
          $where_filter
          $limit_filter
          ";     
          
$stmt = $DB->prepare($query);
if ($stmt->execute($params))
{
 while ($row = $stmt->fetchObject())
 {
  $output[] = $row;
 } 
}

/******************************************************************************
* REPLACE COUNTER NAMES
******************************************************************************/
if ($counters)
{
 foreach ($output AS $i => $row)
 {
  if ($symbol = array_search($row->ticker, (array) $counters))
  {
   $output[$i]->symbol = $symbol;
  }
 }
}

echo json_encode($output,JSON_PRETTY_PRINT);

?>
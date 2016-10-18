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
 $limit = $_GET['limit'];
 $limit_filter = "LIMIT $limit";
}

if ($_GET['query'])
{
 $params['countername'] = "%" . $_GET['query'] . "%";
 $params['symbol'] = "%" . $_GET['query'] . "%";
 
 $where['countername'] = "(counter.name LIKE :countername OR counter.symbol LIKE :symbol)";
}

if ($_GET['type'])
{
 $params['symboltype'] = $_GET['type'];
 $where['symboltype'] = "exchange.symboltype = :symboltype";
}

if ($_GET['exchange'])
{
 $params['exchangeid'] = $_GET['exchange'];
 $where['exchangeid'] = "counter.exchangeid = :exchangeid";
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
          counter.counterid AS symbol,
          counter.name AS full_name,
          IF(counter.description, counter.description, counter.name) AS description,
          exchange.name AS exchange,
          counter.symbol AS ticker,
          exchange.symboltype AS type
          FROM counter
          LEFT JOIN symbol ON counter.symbol = symbol.symbol
          LEFT JOIN pricecache ON (counter.symbol = pricecache.symbol AND pricecache.interval = 'day')
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
// else
// {
 // print_r($stmt->errorInfo());
 // print_r($params);
 // echo $query;
// }

echo json_encode($output,JSON_PRETTY_PRINT);

?>
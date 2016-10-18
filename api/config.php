<?php

include(__DIR__."/../includes/functions.php");

/******************************************************************************
* DATABASE
******************************************************************************/
$DB = $PDO;

/******************************************************************************
* CHARTING CONFIG
******************************************************************************/
$config = new stdClass();
$config->supports_search = true; 
$config->supports_group_request = $config->supports_search ? false : true;
$config->supports_marks = false;
$config->supports_timescale_marks = false; 
$config->supports_time = true;
$config->exchanges = array();
$config->symbolsTypes = array();
$config->supportedResolutions = array("1S","10S", "30S","1", "5", "15", "30", "60", "1D", "1W", "1M");

/******************************************************************************
* EXCHANGES
******************************************************************************/
$config->exchanges[] = array("value"=>"","name"=>"All Exchanges","desc"=>"All Exchanges");
$config->symbolsTypes[] = array("name"=>"All Types","value"=>"");

$query = "SELECT exchangeid, name, description, symboltype, symboltype_description FROM exchange";
$stmt = $DB->query($query);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
 $exchange = array();
 $exchange[name] = $row[name];
 $exchange[value] = $row[exchangeid];
 $exchange[desc] = $row[description];
 
 $symboltype = array();
 $symboltype[name] = $row[symboltype_description];
 $symboltype[value] = $row[symboltype];
 
 $config->exchanges[] = $exchange;
 $config->symbolsTypes[] = $symboltype;
}

echo json_encode($config, JSON_PRETTY_PRINT);

?>

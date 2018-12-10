<?php

class Symbol
{
 public $symbol;
 
 public $name;
 public $ticker;
 public $description;
 public $session;
 public $timezone;
 
 public $pointvalue; // Integer
 public $pricescale; // Integer
 public $minmov; // Integer
 public $minmov2; // Integer
 public $has_seconds; // Boolean
 public $has_intraday; // Boolean
 public $has_daily; // Boolean
 public $has_no_volume; // Boolean
 
 public $type;
 public $exchange;
 public $listed_exchange; 
 
 public $seconds_multipliers;
 public $intraday_multipliers;
 public $has_weekly_and_monthly;
 public $supported_resolutions; 
 
 private $DB;
 
 //-----------------------------------------------------------------------------
 public function __construct($DB)
 {
  $this->DB = $DB;
  
  $this->seconds_multipliers = array("1");
  $this->intraday_multipliers = array("1");
  $this->has_weekly_and_monthly = false;   
  //$this->supported_resolutions = array("1", "5", "15", "30", "60", "1D", "1W", "1M");    
  $this->supported_resolutions = array("5S","10S", "30S","1", "5", "15", "30", "60", "1D", "1W", "1M");    
 }
 
 //-----------------------------------------------------------------------------
 public function fetch($symbol, $accesstype = "default")
 {
  $params[symbol] = $symbol;
  
  $query = "SELECT
            symbol.symbol,
            symbol.name,
            IF(symbol.description != '', symbol.description, symbol.name) AS description,
            session, timezone, pointvalue, pricescale, minmov, minmov2,
            has_seconds, has_intraday, has_daily, has_no_volume,            
            exchange.exchangeid, exchange.name AS exchangename,
            exchange.exchangeid, exchange.description AS exchange_description,
            exchange.symboltype_description
            FROM symbol
            LEFT JOIN exchange USING (exchangeid)
            WHERE
            symbol = :symbol
            ";
            
  $stmt = $this->DB->prepare($query);
  $stmt->execute($params);
  if ($stmt->rowCount()==1)
  {
   $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
   $this->name = $row[name];
   $this->description = $row[description];
   
   $this->exchange = $row[exchange_description];
   $this->listed_exchange = $row[exchange_description];
   
   $this->symbol = $row[name];
   $this->ticker = $row[symbol];
   $this->session = $row[session];
   $this->timezone = $row[timezone];
   $this->pointvalue = (int) $row[pointvalue];
   $this->pricescale = (int) $row[pricescale];
   $this->minmov = (int) $row[minmov];
   $this->minmov2 = (int) $row[minmov2];
   $this->has_seconds = $row[has_seconds] ? true : false; // Boolean
   $this->has_intraday = $row[has_intraday] ? true : false; // Boolean
   $this->has_daily = $row[has_daily] ? true : false; // Boolean
   $this->has_no_volume = $row[has_no_volume] ? true : false; // Boolean
   
   $this->type = $row[symboltype_description]; // DEV
   
   switch($accesstype) // Show more resolutions for Logged in users
   {
    case "loggedin":
      $this->supported_resolutions = array("5S","10S", "30S","1", "5", "15", "30", "60", "1D", "1W", "1M");   
      break;
   }     
  }
 }
}

?>

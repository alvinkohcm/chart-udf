<?php

class Counter
{
 public $counterid;
 public $symbol;
 public $session;
 public $timezone;
 public $pointvalue;
 public $pricescale;
 public $minmov;
 public $minmov2;
 public $has_seconds;
 public $has_intraday;
 public $has_daily;
 public $has_no_volume;
 
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
  $this->supported_resolutions = array("1S","10S", "30S","1", "5", "15", "30", "60", "1D", "1W", "1M");   
 }
 
 //-----------------------------------------------------------------------------
 public function fetch($counterid)
 {
  $params[counterid] = $counterid;
  $params[symbol] = $counterid;
  
  $query = "SELECT
            counter.counterid,
            counter.name,
            symbol.*,
            exchange.exchangeid, exchange.name AS exchangename
            FROM counter
            LEFT JOIN symbol USING (symbol)
            LEFT JOIN exchange USING (exchangeid)
            WHERE
            counterid = :counterid
            OR symbol = :symbol
            ";
            
  $stmt = $this->DB->prepare($query);
  $stmt->execute($params);
  if ($stmt->rowCount()==1)
  {
   $counter = $stmt->fetch(PDO::FETCH_ASSOC);

   $this->counterid = $counter[counterid];   
   $this->name = $counter[name];
   
   $this->{'exchange-traded'} = $counter[exchangename];
   $this->{'exchange-listed'} = $counter[exchangename];
   
   $this->symbol = $counter[symbol];
   $this->ticker = $counter[symbol];
   $this->session = $counter[session];
   $this->timezone = $counter[timezone];
   $this->pointvalue = $counter[pointvalue];
   $this->pricescale = $counter[pricescale];
   $this->minmov = $counter[minmov];
   $this->minmov2 = $counter[minmov2];
   $this->has_seconds = $counter[has_seconds];
   $this->has_intraday = $counter[has_intraday];
   $this->has_daily = $counter[has_daily];
   $this->has_no_volume = $counter[has_no_volume];
  }
 }
}

?>

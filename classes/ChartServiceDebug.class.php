<?php

class ChartServiceDebug
{
 public $symbol;
 public $resolution;
 public $resolution_type; // seconds, minutes, days
 public $datasource; // seconds, intraday, dailyprice
 public $from;
 public $to;
 
 public $debugmode;
 
 private $DB;
 
 //-----------------------------------------------------------------------------
 public function __construct($DB)
 {
  $this->DB = $DB;  
  $this->DB->query("SET time_zone='+00:00'");
  
  // Default
  $this->resolution_type = "minutes";
  $this->datasource = "intraday";
  $this->debugmode = true;
 }
 
 //-----------------------------------------------------------------------------
 public function setSymbol($symbol)
 {
  $this->symbol = $symbol;
 }
 
 //-----------------------------------------------------------------------------
 public function setResolution($resolution)
 {  
  $this->resolution = $resolution;
  
  switch(substr($resolution, -1))
  {
   case "S":
     $this->resolution_type = "seconds";
     $this->datasource = "seconds";
     break;
   
   case "D":
   case "W":
   case "M":
   case "Y":
     $this->resolution_type = "days";
     $this->datasource = "dailyprice";
     break;
     
   default:
     $this->resolution_type = "minutes";
     $this->datasource = "intraday";
     break;
  }
 } 
 
 //-----------------------------------------------------------------------------
 public function getHistoricalData($from, $to)
 {
  if (!ctype_digit($from) || !ctype_digit($to))
  {
   $output = new stdClass();
   $output->s = "error";
   $output->errmsg = "Invalid from or to value";
   echo json_encode($output);
   exit;
  }
  else
  {
   $this->from = $from;
   $this->to = $to;   
  }

  switch($this->resolution_type)
  {
   case "days":

    $session_offset = $this->getSessionOffset();
   
    $query = "SELECT
              unixtime,
              close, high, low, open
              FROM {$this->datasource}
              WHERE
              symbol = :symbol
              AND utcdatetime BETWEEN FROM_UNIXTIME(:from) AND FROM_UNIXTIME(:to)
              ";                      
              
     $stmt = $this->DB->prepare($query);
     $stmt->execute([
        "symbol" => $this->symbol,
        "from" => $this->from,
        "to" => $this->to
     ]);
     
     
     $query_render = "SELECT
              unixtime,
              close, high, low, open
              FROM {$this->datasource}
              WHERE
              symbol = '{$this->symbol}'
              AND utcdatetime BETWEEN FROM_UNIXTIME({$this->from}) AND FROM_UNIXTIME({$this->to})
              ";
     
     while ($row = $stmt->fetchObject()) // Fetch Object to retain value type (float).
     {     
      $unixtime = $row->unixtime;
         
      if ($session_offset) { $unixtime += $session_offset; }

      echo "Before: $unixtime (".date("Ymd H:i:s", $unixtime).")" . PHP_EOL;

      $unixtime = (new DateTime((new DateTime())->setTimestamp($unixtime + (24 * 3600))->format("Y-m-d")))->format("U");
      
      echo "After: $unixtime (".date("Ymd H:i:s", $unixtime).")" . PHP_EOL . PHP_EOL;
      
      if (!$data[$unixtime])
      {
       $data[$unixtime] = $row;
      }
      else
      {
       if ($row->low < $data[$unixtime]->low) { $data[$unixtime]->low = $row->low; }
       if ($row->high > $data[$unixtime]->high) { $data[$unixtime]->high = $row->high; }
       $data[$unixtime]->close = $row->close;
      }
     }                     
     break;     
     
   //------------------------------------------------------------------------------
   default:
    $query = "SELECT
              unixtime,
              close, high, low, open
              FROM {$this->datasource}
              WHERE
              symbol = :symbol
              AND utcdatetime BETWEEN FROM_UNIXTIME(:from) AND FROM_UNIXTIME(:to)
              ";      
              
     $stmt = $this->DB->prepare($query);
     $stmt->execute([
        "symbol" => $this->symbol,
        "from" => $this->from,
        "to" => $this->to
     ]);
     
     while ($row = $stmt->fetchObject()) // Fetch Object to retain value type (float).
     {
      $data[$row->unixtime] = $row;
     }      
     break;
  }                      
  
  /******************************************************************************
  * CONVERT TO UDF FORMAT
  ******************************************************************************/
  echo $this->_generateUDF($data);   
 }
 
 //-----------------------------------------------------------------------------
 public function getLastUnixtime()
 {
  $query = "SELECT UNIX_TIMESTAMP() AS lastunixtime";
  $stmt = $this->DB->query($query);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  
  return $row[lastunixtime];
 }
 
 //-----------------------------------------------------------------------------
 private function _generateUDF($data, $debuginfo = false)
 {
  $output = new stdClass();
  
  $output->range = $this->secondsToTime($this->to - $this->from);  
  $output->from_display = gmdate("H:i:s (Y-m-d)",$this->from);  
  $output->to_display = gmdate("H:i:s (Y-m-d)",$this->to);  
  
  if ($data)
  {      
   $output->lastunixtime = $this->getLastUnixtime(); // Realtime server time
   $output->lastunixtime_display = gmdate("H:i:s (Y-m-d)",$this->getLastUnixtime()); // Realtime server time

   $output->s = "ok";
   
   $counter = 0;
   foreach($data AS $unixtime => $row)
   {
    //### Output human-readable time for debugging
    //$output->ts[] = "{$counter}:" . gmdate("H:i:s (Y-m-d)",$unixtime);
    //$counter++;
    
    $output->t[] = ($unixtime);
    $output->c[] = (float) $row->close;
    $output->h[] = (float) $row->high;
    $output->l[] = (float) $row->low;
    $output->o[] = (float) $row->open;
   }   
  }
  else
  {
   if ($nexttime = $this->getNextTime())
   {
    $output->s = "no_data";
    $output->nextTime = $nexttime; // Skip to next time range
   }
   else
   {
    $output->s = "no_data";
   }
  }
  return json_encode($output, JSON_PRETTY_PRINT);
 }

 //------------------------------------------------------------------------------
 private function getNextTime()
 {
  $query = "SELECT
            MAX(unixtime) AS nexttime
            FROM {$this->datasource}
            WHERE
            symbol = :symbol
            AND utcdatetime < FROM_UNIXTIME(:from)
            ";

  $stmt = $this->DB->prepare($query);
  $stmt->execute([
     "symbol" => $this->symbol,
     "from" => $this->from
  ]);
  
  if ($stmt->rowCount())
  {
   $row = $stmt->fetchObject();
   return (int) $row->nexttime;
  }
  return false;
 }
 
 //------------------------------------------------------------------------------
 private function getSessionOffset()
 {
  $query = "SELECT
            session
            FROM symbol
            WHERE
            symbol = :symbol
            ";   
            
  $stmt = $this->DB->prepare($query);
  $stmt->execute([
     "symbol" => $this->symbol,
  ]);
  $row = $stmt->fetchObject();
  
  if (preg_match("/^(\d{2})(\d{2})\-\d{4}/i",$row->session, $matches))
  {
   $hours = $matches[1];
   $seconds = $matches[2];
   return (int) (($hours * 60) + $seconds);
  }  
  return false;
 }
 
 //-----------------------------------------------------------------------------
 private function secondsToTime($seconds)
 {
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
 }
 
 
 //-----------------------------------------------------------------------------
 private function debugQuery($query, $params)
 {
  foreach ($params AS $key => $value)
  {
   $query = str_replace(":{$key}", "'{$value}'", $query);
  }
  $query = preg_replace("/\s+/i"," ", $query);
  $query = trim($query);
  return $query;
 }

}

?>
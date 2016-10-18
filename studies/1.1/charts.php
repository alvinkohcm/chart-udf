<?php

include(__DIR__."/../../includes/functions.php");

/******************************************************************************
* DATABASE
******************************************************************************/
$DB = $PDO;

/******************************************************************************
* RESPONSE
******************************************************************************/
$response = new stdClass();

/******************************************************************************
* ACTION
******************************************************************************/
if ($_POST) { $action = "save"; }
if (!$_POST && $_GET[client] && $_GET[user]) { $action = "list"; }
if (!$_POST && $_GET[client] && $_GET[user] && $_GET[chart]) { $action = "load"; }
if ($_SERVER['REQUEST_METHOD']=='DELETE') { $action = "delete"; }

/******************************************************************************
* SAVE CHART
******************************************************************************/
switch($action)
{
 case "load":
   $params['clientid'] = $_GET['client'];
   $params['userid'] = $_GET['user'];
   $params['id'] = $_GET['chart'];

   $query = "SELECT
             content,
             unixtime AS timestamp,
             id, name
             FROM studies
             WHERE
             clientid = :clientid
             AND userid = :userid
             AND id = :id
             ";
                          
   $stmt = $DB->prepare($query);
   if ($stmt->execute($params))
   {
    $response->status = "ok";   
    $row = $stmt->fetchObject();
    $response->data = $row;
   }
   else
   {
    $response->status = "error"; 
   }      
   
   break;
 
 //-----------------------------------------------------------------------------
 case "list":
   $params['clientid'] = $_GET['client'];
   $params['userid'] = $_GET['user'];
   
   $query = "SELECT
             *, unixtime AS timestamp
             FROM studies
             WHERE
             clientid = :clientid
             AND userid = :userid
             ";
                          
   $stmt = $DB->prepare($query);
   if ($stmt->execute($params))
   {
    $response->status = "ok"; 
    $response->data = array();    
     
    while ($row = $stmt->fetchObject())
    {
     $response->data[] = $row;
    }  
   }
   else
   {
    $response->status = "error"; 
   }              
   break;
 
 //-----------------------------------------------------------------------------
 case "save":
   $params['clientid'] = $_GET['client'];
   $params['userid'] = $_GET['user'];
   $params['name'] = $_POST['name'];
   $params['content'] = $_POST['content'];
   $params['symbol'] = $_POST['symbol'];
   $params['resolution'] = $_POST['resolution'];

   $query = "INSERT INTO studies
             SET
             clientid = :clientid,
             userid = :userid,
             name = :name,
             content = :content,
             symbol = :symbol,
             resolution = :resolution,
             unixtime = UNIX_TIMESTAMP()
             ";
             
   $stmt = $DB->prepare($query);
   if ($stmt->execute($params))
   {
    $id = $DB->lastInsertId();
    $response->status = "ok"; 
    $response->id = (int) $id;
   }
   else
   {
    $response->status = "error"; 
   } 
   break;
   
 //-----------------------------------------------------------------------------
 case "delete";
   $params['clientid'] = $_GET['client'];
   $params['userid'] = $_GET['user'];
   $params['id'] = $_GET['chart'];
   
   $query = "DELETE
             FROM studies
             WHERE
             clientid = :clientid
             AND userid = :userid
             AND id = :id
             ";
                          
   $stmt = $DB->prepare($query);
   if ($stmt->execute($params))
   {
    $response->status = "ok";    
   }
   else
   {
    $response->status = "error";
   }
     
   break;   
}

echo json_encode($response, JSON_PRETTY_PRINT);

?>
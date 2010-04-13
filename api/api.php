<?php

if(file_exists('../sc_lib.php')) {
  require_once '../sc_lib.php';
}

function api_call() {
  try { 
    $method_array = array();
    //if(SC::getParam("controller")) $method_array[] = SC::getParam("controller");
    //if(SC::getParam("action")) $method_array[] = SC::getParam("action");
    
    $uri = $_SERVER["REQUEST_URI"];
    
    $route_parts = SCRoutes::parseUrl(true);
    
    if(!$route_parts) {
      throw new Exception("This api page does not exist", 404);
    }
    //var_dump($route_parts);
    $_GET = array_merge($_GET, $route_parts);
    
    $method_array[] = $route_parts["controller"];
    $method_array[] = $route_parts["action"];
    
    $methodname = implode("_", $method_array);
    
    $api = new SCApi();
    if(method_exists($api, $methodname)) {
      $resp =  $api->$methodname();
      return $api->handleResponse($resp, $methodname);
    }
    else {
      throw new APIException("This method does not exist in the API", 404);
    }
  }
  catch(Exception $ex) {
    $code = $ex->getCode() or $code = 400;
    header($ex->getMessage(), true, $code);
    return $ex->getMessage();
  }
}


echo api_call();


?>

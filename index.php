<?php
  require_once "config/environment.php";
	require_once "sc_lib.php";
  
  try {
    
    if(strpos($_SERVER["REQUEST_URI"], "index.php")>-1) {
      SC::transfer();
    }
    
    
    $uri = $_SERVER["REQUEST_URI"];
    
    $route_parts = SCRoutes::parseUrl();
    
    if(!$route_parts) {
      throw new Exception("This page does not exist", 404);
    }
    
    $route_parts["__content_type"] = SC::getResponseContentType();
    
    $_GET = array_merge($_GET, $route_parts);
    //var_dump($route_parts);
  
    $controller_file = SCBASEPATH."/controllers/".$route_parts["controller"].".php";
    if (file_exists($controller_file)) {
      require_once($controller_file);
      if($controller) {
        if(method_exists($controller, $route_parts["action"])) {
          $controller->$route_parts["action"]();
        }
        else {
          throw new Exception("The ".$route_parts["action"]." action has not been defined for the ".$route_parts["controller"]." controller.", 500);
        }
      }
      else {
        throw new Exception("The ".$route_parts["controller"]." controller has not been initialized", 500);
      }
    }
    else {
      throw new Exception("The ".$route_parts["controller"]." controller does not exist", 500);
    }
  
    //echo $_SESSION["redir"];
    
  }
  catch(Exception $ex) {
    SC::handleException($ex);
  }
  
?>

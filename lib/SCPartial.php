<?php

class SCPartial {

  static function renderToString($partial, $locals=false) {
    if(!$locals) $locals = array();
    $partial_path = explode("/", $partial);
    $partial_path[sizeof($partial_path)-1] = "_".$partial_path[sizeof($partial_path)-1];
    $partial = "partials/".implode("/", $partial_path) .".php";
    
    foreach($locals as $var=>$val) {
      $$var = $val;
    }
    global $current_user;
    
    ob_start();
    include $partial;
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  }
  
  static function render($partial, $locals=false) {
    if(!$locals) $locals = array();
    $partial_path = explode("/", $partial);
    $partial_path[sizeof($partial_path)-1] = "_".$partial_path[sizeof($partial_path)-1];
    $partial = "partials/".implode("/", $partial_path) .".php";
    
    foreach($locals as $var=>$val) {
      $$var = $val;
    }
    
    
    global $current_user;
    include $partial;
  }
  
}

?>

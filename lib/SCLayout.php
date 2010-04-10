<?php

class SCLayout {
  
  static $content_sections;

  static function yield($section) {
    if(isset(SCLayout::$content_sections[$section])) {
      echo SCLayout::$content_sections[$section];
    }
  }
  
  static function render($layout, $locals=false, $content_sections=false) {
    if(!$locals) $locals = array();
    if(!$content_sections) $content_sections = array();
    $layout = "layouts/$layout.php";
    
    foreach($locals as $var=>$val) {
      $$var = $val;
    }
    
    foreach($content_sections as $var=>$val) {
      $var = "___$var";
      $$var = $val;
    }
    
    SCLayout::$content_sections = $content_sections;
    
    
    $controller = $_GET["controller"];
    $action = $_GET["action"];
    $flash_message = SC::flashMessage();
    
    include $layout;
  }
  
}

?>

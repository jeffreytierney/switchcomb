<?php

class SCBase {

  function __construct() {
  }
  function __destruct() {
  }
  
  public function updateAttributes($arr) {
    foreach($arr as $key=>$value) {
      if(property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
    return $this;
  }
  
  public function toArray($exclude=false) {
    if(!$exclude) {
      $exclude = array();
    }
    $props = array();
    foreach($this as $key=>$value) {
      if(!in_array($key, $exclude)) {
        
        if(method_exists($value, "toArray")) {
          $props[$key] =  $value->toArray();
        }
        else {
          $props[$key] =  $value;
        }
      }
    }
    return $props;
  }
  
  public function jsonify($callback=false) {
		$props = $this->toArray();
			
		return SC::jsonify($props, $callback);
	}
}

?>

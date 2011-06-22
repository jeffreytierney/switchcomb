<?php

class SCBlock {
    
  static $blocks = array();
  
  static function render($block, $val="") {
        
    if(!isset(SCBlock::$blocks[$block])) { SCBlock::$blocks[$block] = $val; }
    echo SCBlock::$blocks[$block];
  }
  
  static function set($block, $val="") {
    SCBlock::$blocks[$block] = $val;
  }
  
}

?>
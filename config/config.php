<?php

  date_default_timezone_set('UTC');
  
  define("SC_MESSAGE_EMAIL_FROM", "Switchcomb");
  //define("SC_MESSAGE_EMAIL_FROM_ADDRESS", ":boardid"."@boards.switchcomb.com");
  define("SC_INVITE_EMAIL_FROM", "Switchcomb");
  define("SC_INVITE_EMAIL_FROM_ADDRESS", "admin@switchcomb.com");
  
  define('IS_AJAX', (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
  
?>

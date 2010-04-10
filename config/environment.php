<?php

  require_once "define_environment.php";
  
  switch(SC_ENVIRONMENT) {
  
    case "development":
      define("SC_ROOT", "/switchcomb/");
      define("SC_DBSERVER", "127.0.0.1");
      define("SC_DBSCHEMA", "sc_mb");
      define("SC_DBUSER", "sc_mb");
      define("SC_DBPWD", "scmessageboard");
      define("SC_PAGETITLEBASE", "switchcomb");
      define("SC_CANEMAIL", false);
      define("SCBASEPATH", "/webroot/switchcomb");
      break;
   
    case "mobiledevelopment":
      define("SC_ROOT", "/mswitchcomb/");
      define("SC_DBSERVER", "127.0.0.1");
      define("SC_DBSCHEMA", "sc_mb");
      define("SC_DBUSER", "sc_mb");
      define("SC_DBPWD", "scmessageboard");
      define("SC_PAGETITLEBASE", "mobile switchcomb");
      define("SC_CANEMAIL", false);
      define("SCBASEPATH", "/webroot/mswitchcomb;/webroot/switchcomb");
      break;

    case "test":
      define("SC_ROOT", "/");
      define("SC_DBSERVER", "localhost");
      define("SC_DBSCHEMA", "jeffr28_scmbtest");
      define("SC_DBUSER", "jeffr28_scmbtest");
      define("SC_DBPWD", "scmessageboard");
      define("SC_PAGETITLEBASE", "switchcomb");
      define("SC_CANEMAIL", true);
      define("SCBASEPATH", "/webroot/switchcomb");
      break;

    case "mobiletest":
      define("SC_ROOT", "/");
      define("SC_DBSERVER", "localhost");
      define("SC_DBSCHEMA", "jeffr28_scmbtest");
      define("SC_DBUSER", "jeffr28_scmbtest");
      define("SC_DBPWD", "scmessageboard");
      define("SC_PAGETITLEBASE", "mobile switchcomb");
      define("SC_CANEMAIL", true);
      define("SCBASEPATH", "/webroot/switchcomb");
      break;

    case "production":
      define("SC_ROOT", "/");
      define("SC_DBSERVER", "localhost");
      define("SC_DBSCHEMA", "jeffr28_scmb");
      define("SC_DBUSER", "jeffr28_scmb");
      define("SC_DBPWD", "scmessageboard");
      define("SC_PAGETITLEBASE", "switchcomb");
      define("SC_CANEMAIL", true);
      define("SCBASEPATH", "/webroot/switchcomb");
      break;

    case "mobileproduction":
      define("SC_ROOT", "/");
      define("SC_DBSERVER", "localhost");
      define("SC_DBSCHEMA", "jeffr28_scmb");
      define("SC_DBUSER", "jeffr28_scmb");
      define("SC_DBPWD", "scmessageboard");
      define("SC_PAGETITLEBASE", "mobile switchcomb");
      define("SC_CANEMAIL", true);
      define("SCBASEPATH", "/webroot/switchcomb");
      break;
  }
  
  
  $inc_path = array_merge(array(get_include_path()),explode(";",SCBASEPATH));
  set_include_path(implode(PATH_SEPARATOR,$inc_path));


?>

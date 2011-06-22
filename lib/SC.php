<?php

class SC {

  static $return_types = array(
    "application/json"=>"json",
    "text/javascript"=>"json",
    "text/html"=>"html",
  );

  static function getResponseContentType($default="html") {
    if(isset($_POST["__content_type"])) {
      return $_POST["__content_type"];
    }
    $accepts = explode(", ", $_SERVER["HTTP_ACCEPT"]);
    foreach(SC::$return_types as $accept_type=>$response_type) {
      if(in_array($accept_type, $accepts)) {
        return $response_type;
      }
    }
    return $default;
  }

	static function root() {
		return SC_ROOT;
	}

	static function privacy($privacy) {
		if($privacy) return "private";
		else return "public";
	}

	static function getParam($name, $force_post=false) {
		if(isset($_POST[$name])) return $_POST[$name];
		if($force_post) return false;
		if(isset($_REQUEST[$name])) return urldecode($_REQUEST[$name]);
		return false;
	}

	static function jsonify($obj, $callback=false) {
		$jsonified = json_encode($obj);
		if($callback) $jsonified = "$callback($jsonified);";
		return $jsonified;
	}

  static function toArrayAll($obj, $callback=false) {
		if(method_exists($obj, "toArray")) {
      $jsonified = $obj->toArray();
    }
    else if (is_array($obj)) {
      $jsonified = array();
      foreach($obj as $key=>$value) {
        $jsonified[$key] = SC::toArrayAll($value);
      }
    }
    else {
      $jsonified = $obj;
    }

		return $jsonified;
	}

	static function getCookie($name) {
		if ($_COOKIE && isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return false;
	}

	static function dbString($str, $quotes=true) {
		$ret_str = str_replace("'", "\'", str_replace("\'", "'", $str));
    if($quotes) $ret_str = "'$ret_str'";

    return $ret_str;
	}

	static function dbDate($time=false, $quotes=true) {
		if(!$time || $time==="now") $time = time();
		$ret_str =  date ("Y-m-d H:i:s", $time);
    if($quotes) $ret_str = "'$ret_str'";

    return $ret_str;

	}

	static function pageTitle($page = false) {
		return SC_PAGETITLEBASE . ($page ? " - $page" : "");
	}

	static function loginRequired($redirect=false, $save_redir=false) {
    if(!$redirect) $redirect = SC::root();
		if(!SC::isLoggedIn()) {
			//header("Location: $redirect");
      SC::transfer($redirect, $save_redir);
		}
	}

	static function notLoggedinRequired($redirect=false) {
    if(!$redirect) $redirect = SC::root();
		if(SC::isLoggedIn()) {
			header("Location: $redirect");
		}
	}

	static function isLoggedIn() {
    //$current_user = SCUserSession::loggedInUser();

    global $current_user;

    if($current_user) {
      return $current_user;
    }
    else {
      return false;
    }
	}

	static function transfer($to=false, $redir=false) {
		if(!$to) {
			$to = SC::root();
		}
    session_start();
    if($redir) {
      $_SESSION["redir"] = $redir;
    }
    else {
      $_SESSION["redir"] = null;
      unset($_SESSION["redir"]);
    }
    session_write_close();
		header("Location: $to");
    //echo ".";
	}

  static function checkRedir($otherwise=false) {
    $redir = $otherwise;
    if($_SESSION["redir"]) {
      $redir = $_SESSION["redir"];
    }
    SC::transfer($redir);
  }

	static function getHeaderLoginParams($page) {
		$page = substr($page, strrpos($page, "/")+1);
		switch($page) {
			default:
				return "";
		}
	}

  static function imagePath($image) {
		return SC::root()."images/$image";
	}
  static function cssPath($css) {
		return SC::root()."css/$css.css";
	}
  static function jsPath($js) {
		return SC::root()."js/$js.js";
	}

  static function setFlashMessage($message, $status="info") {
    session_start();
    $_SESSION["flash_message"] = array(
      "message"=>$message,
      "status"=>$status
    );
    session_write_close();
  }

  static function flashMessage() {//$message=false, $status="info") {
    /*
    if($message) {
      return "<p id=\"flash\" class=\"$status\">$message</p>";
    }
    */
    if($_SESSION["flash_message"]) {
      session_start();
      $message = $_SESSION["flash_message"]["message"];
      $status = $_SESSION["flash_message"]["status"];
      unset($_SESSION["flash_message"]);
      session_write_close();

      return "<p id=\"flash\" class=\"$status\">$message</p>";
    }

    return "";
  }

	/*
	static function emailList() {
		if(SC::isTest()) {
			$list = "jeffrey.tierney@gmail.com";
		}
		else {
			$list = "stephen.skordinski@gmail.com";
		}
		return $list;
	}*/
	/*
	static function boardLink($boardid) {
		return SC::root()."board/$boardid";
	}

	static function threadLink($threadid) {
		return SC::root()."thread/$threadid";
	}
  */
  static function handleException($ex) {
    $code = $ex->getCode() or $code = 400;

    if(in_array($code, array(401,403))) {
      SC::transfer();
    }
    else {
      header($ex->getMessage(), true, $code);
      die($ex->getMessage());
    }
  }

  static function updateSessionUser() {
    global $current_user;
    global $user_session;

    $current_user = $user_session->updateSessionUser();
  }
  
  static function timeAgo($then, $now=null) {
      
      if($now === null) { $now = time(); }
      

      $seconds = $now - $then;
      $minutes = floor($seconds / 60);
      $hours = floor($seconds / 3600);
      $days = floor($seconds / 86400);
      $years = floor($seconds / (86400*365));
      
      if ($years) { $time_str = "over a year ago"; }
      else if($days) { $time_str = $days . " day" . ($days > 1 ? "s ":" ") . "ago"; }
      else if($hours) { $time_str = "about " . $hours . " hour" . ($hours > 1 ? "s ":" ") . "ago"; }
      else if($minutes) { $time_str = $minutes . " minute" . ($minutes > 1 ? "s ":" ") . "ago"; }
      else { $time_str = "just now"; }
      

      return $time_str;
      
  }
}

?>

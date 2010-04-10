<?php

class SCUserSession {
	function __construct() {
    session_start();
  }
	function __destruct() {
    session_write_close();
  }
  
  public function setSessionUser($user_id, $md5_pw, $remember=false) {
      $_SESSION["sc_user"] = new SCUser($user_id);
      
      if($remember) {
				$this->setAuthCookie($md5_pw);
      }
      return $_SESSION["sc_user"];
  }
  
  public function updateSessionUser() {
    $session_user = $this->getSessionUser();
    if($session_user) {
      $md5_pw = $session_user->getCryptedPw();
      if ($md5_pw) {
        $remember = SC::getCookie("sc_auth");
        $this->destroy();
        return $this->setSessionUser($session_user->userid, $md5_pw, $remember);
      }
    }
    return false;
  }
  
  public function getSessionUser() {
    if(isset($_SESSION["sc_user"])) {
      return $_SESSION["sc_user"];
    }
    
    $auth_cookie = SC::getCookie("sc_auth");
    
    if($auth_cookie) {
      return $this->userFromCookie();
    }
    
    return false;
  }
  
  public function create($login=false, $password=false, $remember=false) {
    
    if(!$login || !$password) {
      throw new UserSessionException("Username or password is missing.", 400);
    }
    if(strpos($login, "@")) {
			$login_snippet = "user_email='$login'";
		}
		else {
			$login_snippet = "user_name='$login'";
		}
		
		$db = new SCDB();
    
    $check_password = SCUser::saltPassword($password);
    
		$sql = "SELECT user_id FROM users WHERE $login_snippet AND user_password='$check_password'";
    
		$result = $db->queryArray($sql);
  		
		if(sizeof($result)) {
			$user_array = $result[0];
			$user_id = intval($user_array["user_id"]);
      return $this->setSessionUser($user_id, $check_password, $remember);
		}
		else {
			throw new UserSessionException("Username or password is incorrect.", 401);
		}
		
    
	}
  
  
  private function setAuthCookie($md5_pw) {
    $remember_time = time() + (60*60*24*30);
    $user = $this->getSessionUser();
    $auth_token = $this->createAuthToken($user->userid, $user->email, $md5_pw);
    setCookie("sc_auth", $auth_token, $remember_time);
  }
  
  private function createAuthToken($user_id, $user_email, $md5_pw) {
    return base64_encode($user_id."||".md5($user_email . $md5_pw));
  }
  
  private function userFromCookie() {
    $cookie = SC::getCookie("sc_auth");
    if(!$cookie) return false;
    
    $cookie_array = explode("||", base64_decode($cookie));
    
    //echo (var_dump($cookie_array));
    
    $user_id = $cookie_array[0];
    $cookie_auth_token = $cookie_array[1];
    
    $sql = "SELECT user_password, user_email from users WHERE user_id=".$user_id;
    $db = new SCDB();
    $result = $db->queryArray($sql);
    if(sizeof($result)) {
      
      //$auth_token = $this->createAuthToken($user_id, $result[0]["user_password"]);
      $auth_token = md5($result[0]["user_email"] . $result[0]["user_password"]);
      if(strcmp($cookie_auth_token, $auth_token) === 0) {
        
        $this->setSessionUser($user_id, SCUser::saltPassword($result[0]["user_password"]));
        
        return $this->getSessionUser();
      }
    }
    
    return false;
    
  }
  
  public function destroy() {
    if(SC::getCookie("sc_auth")) {
      $expire = time() - 3600;
      setcookie("sc_auth", "", $expire, SC::root());
    }
    
    unset($_SESSION["sc_user"]);
    return true;
  }
  
  static function loggedInUser() {
    $user_session = new SCUserSession();
    return $user_session->getSessionUser();
  }
	
  
}

class UserSessionException extends Exception {}

?>

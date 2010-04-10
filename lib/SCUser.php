<?php

class SCUser extends SCBase {
	public $existing = false;
	
	public $username;
	public $email;
	public $userid;
	public $fname;
	public $lname;
	public $displayname;
  public $avatar;
  
  public $hideboards;
	private $membership_set = false;
	
	function __construct($seed=null) {
    if($seed) {
      if(is_numeric($seed)) {
        $seed = intval($seed);
      }
      switch(gettype($seed)) {
        case "integer":
            $db = new SCDB();
            
            $result = $db->q(
              array("*"), 
              array("users"), 
              array("user_id"=>$seed)
            );
            if(sizeof($result)) {
              $this->fromArray($result);
            }
            else {
              throw new UserException("User not found", 404);
            }
          break;
        case "string":
            $db = new SCDB();
            
            $field = strpos($seed, "@") ? "user_email" : "user_name";
            
            $result = $db->q(
              array("*"), 
              array("users"), 
              array($field=>SC::dbString($seed, true))
            );
            
            if(sizeof($result)) {
              $this->fromArray($result);
            }
            else {
              throw new UserException("User not found", 404);
            }
          break;
        case "array":
          $this->fromArray($seed);
          break;
      }
		}
	}

	function __destruct() {
		$this->setNull();
	}

  private function fromArray($arr) {
    
    $this->setNull();
    
    if(sizeof($arr)) {
      if(is_array($arr[0])) {
        $user_array = $arr[0];
      }
      else {
        $user_array = $arr;
      }
      $this->username = $user_array["user_name"] or $this->username = $user_array["username"];
      $this->email = $user_array["user_email"] or $this->email = $user_array["email"];
      $this->userid = $user_array["user_id"] or $this->userid = $user_array["userid"];
      $this->fname = $user_array["user_fname"] or $this->fname = $user_array["fname"];
      $this->lname = $user_array["user_lname"] or $this->lname = $user_array["lname"];
      $this->displayname = $user_array["user_displayname"] or $this->displayname = $user_array["displayname"];
      $this->avatar = $user_array["user_avatar"] or $this->avatar = $user_array["avatar"];
      $this->membership_set = $user_array["membership_set"] or $this->membership_set = false;
      
      if ($this->userid) {
        $this->existing = true;
      }
      
      if(isset($user_array["hideboards"])) {
        $this->hideboards = $user_array["hideboards"];
      }
    }
  }
  
	public function goHome() {
		SC::transfer();
	}	
	
	private function setNull() {
		$this->existing = null;
		$this->username = null;
		$this->email = null;
		$this->userid = null;
		$this->fname = null;
		$this->lname = null;
		$this->displayname = null;
    $this->avatar = null;
    $this->hideboards = null;
		$this->membership_set = null;
	}
  
  public function memberships() {
    if(!$this->hideboards) {
      if($this->userid) {
        if($this->membership_set) {
          return $this->membership_set->memberships;
        }
        
        return $this->loadMemberships()->memberships();
      }
      else {
        return array();
      }
    }
    
  }
  
  public function loadMemberships() {
    $this->membership_set = new SCMembershipSet($this->userid);
    $this->membership_set->loadMemberships();
    return $this;
  }
	/*
	public function createMembership($boardid=false, $admin_level=0, $receives_emails=false) {
		if(!$this->userid) {
      throw new UserException("Error Joining Board: Not a valid User", 401);
    }
		if(!$boardid) {
      throw new UserException("Error Joining Board: No boardid specified", 400);
    }
    
		//$sql = "INSERT INTO memberships (mem_user_id, mem_board_id, mem_admin_level, mem_joindate) VALUES($this->userid, " . $boardid . ", $admin_level, '" . SC::dbDate() . "')";
		
    $insert_array = array(
      "mem_user_id"=>$this->userid, 
      "mem_board_id"=>$boardid, 
      "mem_admin_level"=>$admin_level, 
      "mem_joindate"=>SC::dbDate(false, true),
      "mem_receives_emails"=>$receives_emails ? 1 : 0
    );
    
    $db = new SCDB();
    $db->insertFromArray($insert_array, "memberships");
		if(mysql_insert_id($db->conn)) return true;
    else throw new UserException(mysql_error($db->conn));
	}
  */
  
	public function createMembership($boardid=false, $admin_level=0, $receives_emails=false) {
    if(!$this->userid) {
      throw new UserException("You must have an existing user to join a board");
    }
    $board = new SCBoard($boardid);
		$ok = false;
		$invite_id = 0;
    $invite = null;
		if($invite_code) {
			$invite = new SCInvite($invite_code);
			$invite_id = $board->checkInvite($invite);
			if ($invite_id) $ok = true;
		}
		else {
			if(!$board->privacy || $admin_level==10) $ok=true;
		}
		if($ok) {
      $membership = new SCMembership();
      $membership->userid = $this->userid;
      $membership->boardid = $board->boardid;
      return $membership->create($invite);
    }
    else {
      throw new UserException("This is a private board.  if you have an email invitation, please use the link from there to join.");
    }
  }
	
	public function isMemberOf($boardid) {
		if(!$this->userid) {
      throw new UserException('Failed to check membership: Not a valid user', 401);
    }
		if(!$boardid) {
      throw new UserException('Failed to check membership: No board id specified', 400);
    }
		/*
		$db = new SCDB();
    
    $membership = $db->q(
      array("mem_board_id","mem_admin_level"),
      array("memberships"),
      array("mem_user_id"=>$this->userid,"mem_board_id"=>$boardid)
    );
    
		if(sizeof($membership)&& $membership[0]["mem_board_id"]==$boardid) return ($membership[0]["mem_admin_level"]+1);
		else return 0;
    */
    try {
      $membership = new SCMembership($this, $boardid);
      return $membership->admin_level + 1;
    }
    catch(Exception $ex) {
      return 0;
    }
    
	}
	
	public function isAdminOf($boardid) {
		$admin_level = $this->isMemberOf($boardid);
		if($admin_level > 5) {
			return true;
		}
		return false;
	}
  static function saltPassword($password) {
    return md5("SCpre_salt".$password."SCpost_salt");
  }

  public function create($password=null, $confirm_password=null) {
    if($this->existing) {
      throw new Exception("This is an existing user... you can not create an existing user", 400);
    }
    if(!$password || !$confirm_password) {
      throw new Exception("Password and Password Confirmation are required", 400);
    }
    if(strcmp($password, $confirm_password) !== 0) {
      throw new Exception("Password and Password Confirmation do not match", 400);
    }
    
    $create_array = $this->toArray(true);
    $create_array["user_password"] = SC::dbString(SCUser::saltPassword($password), true);
    $create_array["user_createdate"] = SC::dbDate();
    
    
    $db = new SCDB();
    $db->insertFromArray($create_array, "users");
    $user_id = mysql_insert_id($db->conn);
    if($user_id) {
      $user = new SCUser($user_id);
      $this->fromArray($user->toArray());
		}
		else {
      throw new UserException(mysql_error($db->conn));
		}
    
    return $this;
  }
  
  public function save() {
    if(!$this->existing) {
      throw new Exception("This is not existing user... you must call create, not save", 401);
    }
    
    $update_array = $this->toArray(true);
    $db = new SCDB();
    $db->updateFromArray($update_array, "users", "WHERE user_id=".$this->userid);
    if(mysql_error($db->conn) !== "") {
      throw new UserException(mysql_error($db->conn));
		}
    
    $user = new SCUser($this->userid);
    $this->fromArray($user->toArray());
    
    return $this;
  }
  
  public function getThreadViews($threadids) {
    return new SCViewed($this, $threadids);
    
  }
  
  public function setThreadViews($threadid, $count) {
    $viewed = new SCViewed($this, $threadid);
    return $viewed->setViewCount($count);
  }
  
  public function getCryptedPw() {
    if($this->userid) {
      $db = new SCDB();
      $user_data = $db->q(
          array("user_password"),
          array("users"),
          array("user_id"=>$this->userid)
      );
      if(sizeof($user_data)) {
        return $user_data[0]["user_password"];
      }
      return false;
    }
    return false;
  }
  
	public function toArray($for_db = false) {
    if(!$for_db) {
      $exclude = array("membership_set");
      $props = parent::toArray($exclude);
      $props["memberships"] = $this->memberships();
      
    }
    else {
      $props = array(
        "user_name"=>$this->username ? SC::dbString($this->username, true) : null,
        "user_email"=>$this->email ? SC::dbString($this->email, true) : null,
        "user_id"=>$this->userid,
        "user_fname"=>$this->fname ? SC::dbString($this->fname, true) : null,
        "user_lname"=>$this->lname ? SC::dbString($this->lname, true) : null,
        "user_displayname"=>$this->displayname ? SC::dbString($this->displayname, true) : null,
        "user_avatar"=>$this->avatar ? SC::dbString($this->avatar, true) : null,
      );
    }
			
		return $props;
	}
	
}

class UserException extends Exception {}

?>

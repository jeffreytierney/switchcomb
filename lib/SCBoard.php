<?php

class SCBoard extends SCBase {
	public $existing;
	public $boardid;
	public $boardname;
	public $boarddescription;
	public $createdate;
	public $creatorid;
	public $privacy;
  public $lastpost;

  public $hidecreator;
  public $hidethreads;
  
  private $creatinguser;
	private $threadset;
  private $membershipset;
  // public function creator()
  // public function threads()
  
	
	function __construct($seed=false, $count=false, $userid=false) {
    if($seed) {
      if(is_numeric($seed)) {
        $seed = intval($seed);
      }
      switch(gettype($seed)) {
        case "integer":
          $this->loadInfo($seed, $count);
          break;
        case "array":
          $this->fromArray($seed);
          break;
      }
		}
    else {
      $this->setNull();
    }
	}
	
	function __destruct() {
		$this->setNull();
	}
	
	
	private function setNull() {
		$this->existing = null;
		$this->boardid = null;
		$this->boardname = null;
		$this->boarddescription = null;
		$this->createdate = null;
		$this->creatorid = null;
		$this->privacy = null;
    $this->lastpost = null;
    
    $this->hidecreator = null;
    $this->hidethreads = null;
    
    $this->creatinguser = null;
		$this->threadset = null;
    $this->membershipset = null;
	}
	
  private function fromArray($arr, $count=false) {
    $this->setNull();
    if(sizeof($arr)) {
      if(is_array($arr[0])) {
        $boardinfo = $arr[0];
      }
      else {
        $boardinfo = $arr;
      }
      
			$this->boardid = $boardinfo["brd_id"] or $this->boardid = $boardinfo["boardid"];
			$this->boardname = $boardinfo["brd_name"] or $this->boardname = $boardinfo["boardname"];
			$this->boarddescription = $boardinfo["brd_description"] or $this->boarddescription =  $boardinfo["boarddescription"];
			$this->createdate = $boardinfo["brd_createdate"] or $this->createdate =  $boardinfo["createdate"];
			$this->creatorid = $boardinfo["brd_creator"] or $this->creatorid = $boardinfo["creatorid"];
			//$this->creatorusername = $boardinfo["user_displayname"] or $boardinfo["creatorusername"];
			$this->privacy = $boardinfo["brd_privacy"] or $this->privacy = $boardinfo["privacy"];
			$this->lastpost = $boardinfo["lastpost"];
      
      if(isset($boardinfo["creatinguser"])) {
        $this->creatinguser = $boardinfo["creatinguser"];
      }
			//$this->message = "success";
			$this->threadset = array();
			$this->membershipset = array();
      /*
      if(isset($boardinfo["membershipset"])) {
        $this->membershipset = $boardinfo["membershipset"];
      }
      */
      
      if($this->creatorid) {
        if($boardinfo["user_id"] && intval($boardinfo["user_id"]) == $this->creatorid) {
          $this->creatinguser = new SCUser($boardinfo);
        }
      }
        /*
        else {
          $this->creator = new SCUser($this->creatorid);
        }
        */
     
      if($this->boardid) {
        $this->existing = true;
      }
      
      if(isset($boardinfo["hidecreator"])) {
        $this->hidecreator = $boardinfo["hidecreator"];
      }
      if(isset($boardinfo["hidethreads"])) {
        $this->hidethreads = $boardinfo["hidethreads"];
      }
      
    }
    
    return $this;
  }
  
  private function loadInfo($boardid, $count=false) {
		//$sql = "SELECT * FROM boards b, users u WHERE b.brd_id=$boardid AND b.brd_creator=u.user_id";
		$db = new SCDB();
		//$boardinfo = $db->queryArray($sql);
		
    $boardinfo = $db->q(
      array("*"),
      array("boards b, users u"),
      array("b.brd_id"=>$boardid, "b.brd_creator"=>"u.user_id")
    );
    
		if(sizeof($boardinfo)) {
      $this->fromArray($boardinfo, $count);
		}
    else {
      throw new BoardException("Board not found", 404);
    }
    
    return $this;
	}
  
  public function creator() {
    if(!$this->hidecreator) {
      if($this->creatinguser) {
        return $this->creatinguser;
      }
      else if($this->creatorid) {
        $this->creatinguser = new SCUser($this->creatorid);
        return $this->creatinguser;
      }
    }
    return false;
  }
  
  public function threads() {
    if(!$this->hidethreads) {
      if($this->threadset && is_array($this->threadset)) {
        return $this->threadset;
      }
      else {
        return $this->loadThreads()->threadset;
      }
    }
  }
  
  public function memberships($receives_emails_only=false) {
    if($this->membershipset && is_a($this->membershipset, "SCMembershipSet")) {
      return $this->membershipset;
    }
    else {
      return $this->loadMembers($receives_emails_only)->membershipset;
    }
  }
  
  public function create() {
    if(!$this->boardname) {
			throw new BoardException("You must give the board a name", 400);
		}
    
		if(!$this->description) $description = "";
    
		if(!$this->privacy) $this->privacy = 0;
		else $this->privacy = 1;
		/*
    if((!$this->creating || !$this->creator()->existing) && $this->creatorid) {
      $this->creator = new SCUser($this->creatorid);
    }
    */
    if($this->creator()->existing) {
      $db = new SCDB();
      
      $this->createdate = SC::dbDate();
      //$sql = "INSERT INTO boards (brd_name, brd_creator, brd_createdate, brd_privacy, brd_description) VALUES('" . SC::dbString($boardname) ."', " . $userid . ", '" . $createdate . "', $privacy, '" . SC::dbString($description) . "')";
      //$db->query($sql);
      
      $insert_array = array(
        "brd_name"=>SC::dbString($this->boardname, true), 
        "brd_creator"=>$this->creator()->userid, 
        "brd_createdate"=>$this->createdate, 
        "brd_privacy"=>$this->privacy,
        "brd_description"=>SC::dbString($this->description, true)
      );
      
      $db->insertFromArray($insert_array, "boards");
      
      if(mysql_insert_id($db->conn)) {
        $new_board = new SCBoard(mysql_insert_id($db->conn));
        $this->fromArray($new_board->toArray());
        
        $this->addUser($this->creatorid, 10);
        return $this;
      }
      else {
        $this->setNull();
        //$this->message = mysql_error($db->conn) . "\n" . $sql;
        //echo($this->message);
      }
    }
    else {
      throw new BoardException("You must have a valid user to create a board", 401);
    }
	}

	public function hasMessage($messageid) {
		//$sql = "SELECT * from messages WHERE msg_id=$messageid AND msg_board_id=".$this->boardid;
		$db = new SCDB();
    //$hasMessage = $db->queryArray($sql);
		
    $hasMessage = $db->q(
      array("*"),
      array("messages"),
      array("msg_id"=>$messageid, "msg_board_id"=>$this->boardid)
    );
    
    if(sizeof($hasMessage) && $hasMessage[0]["msg_id"] == $messageid) return true;
		else return false;
	}
  
  public function loadMembers($receives_emails_only=false) {
    if(!$this->boardid) {
      throw new BoardException("You must have an existing board to check memberships", 400);
    }
    $membershipset = new SCMembershipSet(false, $this->boardid);
    $membershipset->loadMembers($receives_emails_only);
    
    $this->membershipset = $membershipset;
    return $this;
  }
  
  public function loadThreads($start=false, $num=false, $hidemessages=false) {
    
		//$sql = "SELECT * FROM messages m, users u WHERE m.msg_board_id=".$this->boardid." AND m.msg_thread=0 AND m.msg_author=u.user_id ORDER BY m.msg_id DESC LIMIT $start, " . ($start + $num);
    $db = new SCDB();
		//$threads = $db->queryArray($sql);
    if($start && !$num) {
      $threads = $db->q(
        array("*"),
        array("messages m", "users u"),
        array("m.msg_board_id"=>$this->boardid, "m.msg_thread"=>0, "m.msg_author"=>"u.user_id", "m.msg_id>"=>$start+1),
        array("ORDER BY m.msg_id DESC")
      );
    }
    else {
      if(!$start && !$num) {
        if(!$start) $start = 0;
        if(!$num) $num=10;
      }
      $threads = $db->q(
        array("*"),
        array("messages m", "users u"),
        array("m.msg_board_id"=>$this->boardid, "m.msg_thread"=>0, "m.msg_author"=>"u.user_id"),
        array("ORDER BY m.msg_id DESC LIMIT $start, ".($start+$num))
      );
    }
    if(sizeof($threads)) {
			foreach($threads as $id=>$thread) {
				$threads_in[] = $thread["msg_id"];
				$temp_threads[$thread["msg_id"]] = new SCThread($thread);
			}
			//$sql = "SELECT count(msg_id) as the_count, msg_thread FROM messages WHERE msg_thread IN (" . implode(",", $threads_in) . ") GROUP BY msg_thread ORDER BY msg_thread DESC";
			//$thread_counts = $db->queryArray($sql);
      $thread_counts = $db->q(
        array("count(msg_id) as the_count", "msg_thread"),
        array("messages"),
        array("msg_thread"=>$threads_in),
        array("GROUP BY msg_thread", "ORDER BY msg_thread DESC")
      );
			if(sizeof($thread_counts)) {
				foreach($thread_counts as $id=>$thread_count) {
					$temp_threads[$thread_count["msg_thread"]]->message_count = $thread_count["the_count"];
				}
			}
			$this->threadset = array();
			foreach($temp_threads as $id=>$thread) {
        if($hidemessages) {
          $thread->hidemessages = $hidemessages;
        }
				$this->threadset[] = $thread;
			}
			//echo sizeof($temp_threads);
		}
    
    return $this;
	}
  
  public function getThreadIds() {
    $thread_ids = array();
    $threads = $this->threads();
    
    foreach($threads as $i=>$thread) {
      $thread_ids[] = $thread->messageid;
    }
    
    return $thread_ids;
  }
	
	
	
	public function addUser($userid, $admin_level=0, $receives_emails=false, $invite_code=false) {
		if(!$this->boardid) {
      throw new BoardException("You must have an existing board to join");
    }
		$ok = false;
		$invite_id = 0;
    $invite = null;
		if($invite_code) {
			$invite = new SCInvite($invite_code);
			$invite_id = $this->checkInvite($invite);
			if ($invite_id) $ok = true;
		}
		else {
			if(!$this->privacy || $admin_level==10) $ok=true;
		}
		if($ok) {
      $membership = new SCMembership();
      $membership->userid = $userid;
      $membership->boardid = $this->boardid;
      return $membership->create($invite);
    }
    else {
      throw new BoardException("This is a private board.  if you have an email invitation, please use the link from there to join.");
    }
	}
	
	public function checkInvite($invite) {
		if($invite->board_id==$this->boardid) return $invite->id;
		return false;
	}
  
  public function addThread($thread_init) {
    $thread = new SCThread($thread_init);
    $thread->boardid = $this->boardid;
    
    return $thread->create();
    
	}
	
	public function isExisting() {
		return $this->existing;
	}

	public function go_to() {
		SC::transfer("board.php?boardid=".$this->boardid);
	}
	
	public function sendInvites($list, $user=false) {
		if(!$user) {
      global $current_user;
			$user = $current_user;
		}
    else {
      if(is_numeric($user)) {
        $user = new SCUser($user);
      }
      else {
        $user = $user;
      }
    }
    
		$sent_list = array();
		$address_list = explode(",",$list);
		
		foreach($address_list as $id=>$address) {
			$to = trim($address);
      $invite = new SCInvite();
      $invite->create($this, $to, $user);
			$sent_list[] = $invite->email;
		}
		
		return $sent_list;
		
	}

  public function toArray($for_db=false) {
    if(!$for_db) {
      $props = parent::toArray($exclude);
      $props["creator"] = $this->creator();
      $props["threads"] = $this->threads();
    }
    
    return $props;
  }

}

class BoardException extends Exception {}

?>

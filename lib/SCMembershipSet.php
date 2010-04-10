<?php

class SCMembershipSet extends SCBase {
	public $hasmemberships;
	public $userid;
  public $boardid;
	public $memberships;
	
	function __construct($userid=false, $boardid=false) {
		if($userid) {
			$this->setNull();
			$this->userid = $userid;
		}
    else if ($boardid) {
      $this->setNull();
			$this->boardid = $boardid;
    }
		else {
			$this->setNull();
		}
	}
	
	function __destruct() {
		$this->setNull();
	}
	
	
	private function setNull() {
		$this->hasmemberships = null;
		$this->userid = null;
    $this->boardid = null;
		$this->memberships = array();
	}
	
	private function doLoadMemberships($privacy=false, $count=false, $start=0, $which=0, $orderbymostrecent=false) {
		$limitclause = $privacyclause = "";
		if(!$start) $start = 0;
		if($count) $limitclause = " LIMIT $start, $count";
		if($privacy !== false && $privacy !== null) $privacyclause = " b.brd_privacy=$privacy ";
		
		if($this->userid && $which) {
			if($which>0) {
				if($orderbymostrecent) {
					//$sql = "SELECT q1.*, q2.max_date FROM (SELECT * FROM boards b, memberships m WHERE b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . ") as q1 LEFT OUTER JOIN (SELECT MAX(msg_date) as max_date, mem_board_id FROM messages msg, memberships mem WHERE msg.msg_board_id=mem.mem_board_id GROUP BY mem_board_id) as q2 ON q1.brd_id=q2.mem_board_id ORDER BY max_date DESC $limitclause";
					$sql = "SELECT q1.*, q2.lastpost FROM (SELECT * FROM boards b, memberships m, users u WHERE b.brd_creator=u.user_id AND b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . ") as q1 LEFT OUTER JOIN (SELECT MAX(msg_date) as lastpost, mem_board_id FROM messages msg, memberships mem WHERE msg.msg_board_id=mem.mem_board_id GROUP BY mem_board_id) as q2 ON q1.brd_id=q2.mem_board_id ORDER BY lastpost DESC $limitclause";
				}
				else {
					//$sql = "SELECT * FROM boards b, memberships m WHERE b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC ".$limitclause;
					$sql = "SELECT * FROM boards b, memberships m, users u WHERE b.brd_creator=u.user_id AND b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC ".$limitclause;
				}
			}
      /*
			elseif($which<0) {
				//$sql = "SELECT * FROM boards b WHERE b.brd_id NOT IN (SELECT mem_board_id FROM memberships m WHERE m.mem_user_id=".$this->userid.") ". ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC $limitclause";
				$sql = "SELECT * FROM boards b, users u WHERE b.brd_creator=u.user_id AND b.brd_id NOT IN (SELECT mem_board_id FROM memberships m WHERE m.mem_user_id=".$this->userid.") ". ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC $limitclause";
			}
      */
		}/*
		else {
			$sql = "SELECT * FROM boards b, users u WHERE b.brd_creator=u.user_id " . ($privacyclause ? " AND $privacyclause" : "") . $limitclause;
		}
    */
    //echo $sql;
		$db = new SCDB();
		$memberships = $db->queryArray($sql);
		foreach($memberships as $id=>$membership) {
      $new_membership = new SCMembership($membership);
      $new_membership->hideUser();
      $this->memberships[] = $new_membership;
			/*
      $this->boards[] = array(
				"id"=>$board["brd_id"],
				"name"=>$board["brd_name"],
				"creator"=>$board["brd_creator"],
				"creatorname"=>$board["user_name"],
				"createdate"=>$board["brd_createdate"],
				"privacy"=>$board["brd_privacy"],
				"description"=>$board["brd_description"],
				"max_date"=>(isset($board["max_date"]) ? $board["max_date"] : null),
				"messages"=>array()
			);
      */
		}
		$this->hasmemberships=sizeof($memberships);
		return $this;
	}
	
	public function loadMemberships($privacy=false, $count=false, $start=0) {
		return $this->doLoadMemberships($privacy, $count, $start, 1);
	}
	
	public function loadTopMembershipsByDate($count, $start=0) {
		return $this->doLoadMemberships(false, $count, $start, 1, true);
	}
	
	public function loadTopMessagesForBoards($count=3) {
		if($this->memberships) {
			foreach($this->memberships as $id=>$membership) {
        if($membership->board) {
          $membership->board->loadThreads(0,$count);
          $membership->board->hidethreads = false;
        }
        /*
				$sc_board = new SCBoard($board->boardid, 3);
				//$sc_board->loadThreads(0,$count);
				$this->boards[$id]["messages"] = $sc_board->threads;
        */
			}
		}
	}
  
  public function loadMembers($only_receive_emails=false) {
    if(!$this->boardid) {
      throw new MembershipSetException("Load Members can only be called if board id is set", 400);
    }
    $db = new SCDB();
    
    $conditions_array = array("mem_board_id"=>$this->boardid,"brd_id"=>"mem_board_id","mem_user_id"=>"user_id");
    if($only_receive_emails) {
      $conditions_array["mem_receives_emails"] = 1;
    }
    
    $memberships = $db->q(
      array("*"),
      array("memberships", "boards", "users"),
      $conditions_array
    );
    
    foreach($memberships as $id=>$membership) {
      $new_membership = new SCMembership($membership);
      $new_membership->hideBoard();
      $new_membership->board->hidecreator = true;
      $new_membership->board->hidethreads = true;
      $new_membership->user->hideBoards = true;
      $this->memberships[] = $new_membership;
      
		}
		$this->hasmemberships=sizeof($memberships);
		return $this;
    
  }
  
	/*
	public function jsonify($callback) {
		$props = array(
			"hasboards"=>$this->hasboards,
			"userid"=>$this->userid,
			"boards"=>$this->boards,
			"message"=>$this->message
		);
		return SC::jsonify($props, $callback);
	}
  */
}

class MembershipSetException extends Exception {}


?>

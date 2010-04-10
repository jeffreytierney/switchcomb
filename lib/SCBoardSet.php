<?php

class SCBoardSet extends SCBase {
	public $hasboards;
	public $userid;
	public $boards;
	public $message;
	
	function __construct($userid=false) {
		if($userid) {
			$this->setNull();
			$this->userid = $userid;
		}
		else {
			$this->setNull();
		}
	}
	
	function __destruct() {
		$this->setNull();
	}
	
	
	private function setNull() {
		$this->hasboards = null;
		$this->userid = null;
		$this->boards = array();
		$this->message = null;
	}
	
	public function doLoadBoards($privacy=false, $count=false, $start=0, $which=0, $orderbymostrecent=false) {
		$limitclause = $privacyclause = "";
		if(!$start) $start = 0;
		if($count) $limitclause = " LIMIT $start, $count";
		if($privacy !== false && $privacy !== null) $privacyclause = " b.brd_privacy=$privacy ";
		
		if($this->userid && $which) {
			if($which>0) {
        /*
				if($orderbymostrecent) {
					//$sql = "SELECT q1.*, q2.max_date FROM (SELECT * FROM boards b, memberships m WHERE b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . ") as q1 LEFT OUTER JOIN (SELECT MAX(msg_date) as max_date, mem_board_id FROM messages msg, memberships mem WHERE msg.msg_board_id=mem.mem_board_id GROUP BY mem_board_id) as q2 ON q1.brd_id=q2.mem_board_id ORDER BY max_date DESC $limitclause";
					$sql = "SELECT q1.*, q2.lastpost FROM (SELECT * FROM boards b, memberships m, users u WHERE b.brd_creator=u.user_id AND b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . ") as q1 LEFT OUTER JOIN (SELECT MAX(msg_date) as lastpost, mem_board_id FROM messages msg, memberships mem WHERE msg.msg_board_id=mem.mem_board_id GROUP BY mem_board_id) as q2 ON q1.brd_id=q2.mem_board_id ORDER BY lastpost DESC $limitclause";
				}
				else {
					//$sql = "SELECT * FROM boards b, memberships m WHERE b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC ".$limitclause;
					$sql = "SELECT * FROM boards b, memberships m, users u WHERE b.brd_creator=u.user_id AND b.brd_id = m.mem_board_id AND m.mem_user_id=".$this->userid . ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC ".$limitclause;
				}
        */
			}
			elseif($which<0) {
				//$sql = "SELECT * FROM boards b WHERE b.brd_id NOT IN (SELECT mem_board_id FROM memberships m WHERE m.mem_user_id=".$this->userid.") ". ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC $limitclause";
				// TODO: create method on memberships class that will return a comma separated list of board memberships
        $sql = "SELECT * FROM boards b, users u WHERE b.brd_creator=u.user_id AND b.brd_id NOT IN (SELECT mem_board_id FROM memberships m WHERE m.mem_user_id=".$this->userid.") ". ($privacyclause ? "AND $privacyclause" : "") . " ORDER BY b.brd_id DESC $limitclause";
			}
		}
		else {
			$sql = "SELECT * FROM boards b, users u WHERE b.brd_creator=u.user_id " . ($privacyclause ? " AND $privacyclause" : "") . $limitclause;
		}
    //echo $sql;
		$db = new SCDB();
		$boards = $db->queryArray($sql);
		foreach($boards as $id=>$board) {
      $board["hidethreads"] = true;
      $this->boards[] = new SCBoard($board);
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
		$this->hasboards=sizeof($boards);
		return $this;
	}
	
	public function loadBoards($privacy=false, $count=false, $start=0, $which=0) {
		return $this->doLoadBoards($privacy, $count, $start, $which);
	}
	
	public function loadUnjoinedBoards($privacy=false, $count=false, $start=0) {
		return $this->doLoadBoards($privacy, $count, $start, -1);
	}
	/*
	public function loadJoinedBoards($privacy=false, $count=false, $start=0) {
		return $this->doLoadBoards($privacy, $count, $start, 1);
	}
	
	public function loadTopJoinedByDate($count, $start=0) {
		return $this->doLoadBoards(false, $count, $start, 1, true);
	}
	*/
	public function loadTopMessagesForBoards($count) {
		if($this->boards) {
			foreach($this->boards as $id=>$board) {
        $board->loadThreads(0,3);
        /*
				$sc_board = new SCBoard($board->boardid, 3);
				//$sc_board->loadThreads(0,$count);
				$this->boards[$id]["messages"] = $sc_board->threads;
        */
			}
		}
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



?>

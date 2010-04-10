<?php

class SCInvite {
	
	public $id;
	public $email;
	public $board_id;
	public $hash;
	public $accepted;
	public $date;
  public $from_id;
  
  private $from;
  private $board;
	
	function __construct($invitecode=false) {
		if ($invitecode) {
			$this->loadData($invitecode);
		}
		else {
			$this->setNull();
		}
	}
  
  public function create($board, $email, $from=null) {
    $this->board = $board;
    $this->board_id = $board->boardid;
    $this->email = $email;
    $this->hash = md5(uniqid(rand(), true));
    $this->date = time();
    if($from) {
      $this->from = $from;
      $this->from_id = $from->userid;
    }
    
    //$sql = "INSERT INTO invitations (inv_email, inv_board_id, inv_hash, inv_date) VALUES('$to', " . $this->boardid . ", '$hash', '" .SC::dbDate() . "')";
    $insert_array = array(
      "inv_email" => SC::dbString($this->email),
      "inv_board_id" => $this->board->boardid,
      "inv_hash" => SC::dbString($this->hash),
      "inv_date" => SC::dbDate($this->date, true),
      "inv_from_id" => $this->from_id
    );
    
    $db = new SCDB();
    $db->insertFromArray($insert_array, "invitations");
    
    $invite_id = mysql_insert_id($db->conn);
    if($invite_id) {
      $this->id = $invite_id;
		}
		else {
      throw new InviteException(mysql_error($db->conn));
		}
    
    $this->sendEmail();
    
    return $this;
  }
  
  public function board() {
    if($this->board) {
      return $this->board;
    }
    else {
      $this->board = new SCBoard($this->board_id);
      return $this->board;
    }
  }
  
  public function from() {
    if($this->from) {
      return $this->from;
    }
    else {
      $this->from = new SCUser($this->from_id);
      return $this->from;
    }
  }
  
  public function sendEmail() {
    if(!$this->id) {
      throw new InviteException("You can only send an email for an existing invitation");
    }
    if($this->accepted) {
      throw new InviteException("You can not re-send an accepted invitation");
    }
    
    $invite_email = SCEmail::newInviteEmail($this);
    $invite_email->sendEmail();
    
    return $this;
  }
	
	private function loadData($invitecode) {
		$sql = "SELECT * FROM invitations WHERE inv_hash='$invitecode'";
		$db = new SCDB();
		$result = $db->queryArray($sql);
		
		if(sizeof($result)) {
			$this->id = $result[0]["inv_id"];
			$this->email = $result[0]["inv_email"];
			$this->board_id = $result[0]["inv_board_id"];
      $this->from_id = $result[0]["inv_from_id"];
			$this->hash = $result[0]["inv_hash"];
			$this->accepted = $result[0]["inv_accepted"];
			$this->date = $result[0]["inv_date"];
		}
		else {
			$this->setNull();
		}
	}
	
	public function setAccepted() {
		$db = new SCDB();
		//$sql = "UPDATE invitations set inv_accepted=1 WHERE inv_id=".$this->id;
    $update_array = array(
      "inv_accepted"=>1
    );
		$db->updateFromArray($update_array, "invitations", "WHERE inv_id=".$this->id);
    if(mysql_error($db->conn) !== "") {
      throw new UserException(mysql_error($db->conn));
		}
    return true;
	}
	
	public function isValid() {
		if(!$this->id) return false;
		if($this->accepted) return false;
		//if($this->date) return false;
		return true;
	}
	
	private function setNull() {
		$this->id = null;
		$this->email = null;
		$this->board_id = null;
		$this->hash = null;
		$this->accepted = null;
		$this->date = null;
	}
}

class InviteException extends Exception {}

?>

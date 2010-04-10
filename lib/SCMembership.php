<?php

class SCMembership extends SCBase {
  public $membershipid;
  public $userid;
  public $boardid;
  public $admin_level;
  public $join_date;
  public $receives_emails;
  
  public $user;
  public $board;
  
  private $hideuser;
  private $hideboard;
  
  function __construct($param1=false, $param2=false) {
    if($param1) {
      if($param2) {
        if(is_numeric($param1) && is_numeric($param2)) {
          /*
          $this->userid = $param1;
          $this->user = new SCUser($this->userid);
          $this->boardid = $param2;
          $this->board = new SCBoard($this->boardid);
          */
          $this->loadFromUserBoard($param1, $param2);
        }
        else {
          if(is_numeric($param1)) {
            $this->userid = $param1;
            //$this->user = new SCUser($this->userid);
          }
          else if(is_array($param1)) {
            $this->user = new SCUser($param1);
            $this->userid = $this->user->userid;
          }
          else if($param1 instanceof SCUser) {
            $this->user = $param1;
            $this->userid = $this->user->userid;
          }
          if(is_numeric($param2)) {
            $this->boardid = $param2;
            //$this->board = new SCBoard($this->boardid);
          }
          else if(is_array($param2)) {
            $this->board = new SCBoard($param2);
            $this->boardid = $this->board->boardid;
          }
          else if($param2 instanceof SCBoard) {
            $this->board = $param2;
            $this->boardid = $this->board->boardid;
          }
          $this->loadJustMembership();
        }
      }
      else {
        if(is_numeric($param1)) {
          $this->loadFromMembershipId($param1);
        }
        if(is_array($param1)) {
          $this->fromArray($param1);
        }
      }
    }
    else {
      $this->setNull();
    }
    
    if($this->user) {
      $this->user->hideboards = true;
    }
    if($this->board) {
      $this->board->hidethreads = true;
    }
    
  }
  
  function __destruct() {
    
  }
  
  private function setNull() {
    $this->admin_level = 0;
    $this->join_date = null;
    $this->receives_emails = 0;
    $this->membershipid = null;
    $this->userid = null;
    $this->boardid = null;
    $this->user = null;
    $this->board = null;
    
    $this->hideboard = null;
    $this->hideuser = null;
  }
  
  private function fromArray($arr, $set_null=true) {
    if($set_null) {
      $this->setNull();
    }
    
    if(sizeof($arr)) {
      if(is_array($arr[0])) {
        $mem_array = $arr[0];
      }
      else {
        $mem_array = $arr;
      }
      
      $this->admin_level = $mem_array["mem_admin_level"] or $this->admin_level = $mem_array["admin_level"];
      $this->join_date = $mem_array["mem_join_date"] or $this->join_date = $mem_array["join_date"];
      $this->receives_emails = $mem_array["mem_receives_emails"] or $this->receives_email = $mem_array["receives_emails"];
      $this->membershipid = $mem_array["mem_id"] or $this->membershipid = $mem_array["membershipid"];
      
      if($mem_array["user_id"]) {
        $this->userid = $mem_array["user_id"];
        $this->user = new SCUser($mem_array);
      }
      elseif($mem_array["userid"]) {
        $this->userid = $mem_array["userid"];
      }
      
      if($mem_array["brd_id"]) {
        $this->boardid = $mem_array["brd_id"];
        $this->board = new SCBoard($mem_array);
      }
      elseif($mem_array["boardid"]) {
        $this->boardid = $mem_array["boardid"];
      }
      
    }
    
    return $this;
    
  }
  
  private function loadJustMembership() {
    $db = new SCDB();
    
    $membership = $db->q(
      array("*"),
      array("memberships"),
      array("mem_user_id"=>$this->userid,"mem_board_id"=>$this->boardid)
    );
    
    if(sizeof($membership)) {
      $this->fromArray($membership, false);
    }
    else {
      throw new MembershipException("Membership not found", 404);
    }
    
  }
  
  private function loadFromUserBoard($userid, $boardid) {
    $db = new SCDB();
    
    $membership = $db->q(
      array("*"),
      array("memberships", "boards", "users"),
      array("mem_user_id"=>$userid,"mem_board_id"=>$boardid,"brd_id"=>"mem_board_id","mem_user_id"=>"user_id")
    );
    
    if(sizeof($membership)) {
      $this->fromArray($membership);
    }
    else {
      throw new MembershipException("Membership not found", 404);
    }
    
  }
  
  private function loadFromMembershipId($membershipid) {
    $db = new SCDB();
    
    $membership = $db->q(
      array("*"),
      array("memberships", "boards", "users"),
      array("mem_id"=>$membershipid,"mem_board_id"=>"brd_id","mem_user_id"=>"user_id")
    );
    
    if(sizeof($membership)) {
      $this->fromArray($membership);
    }
    else {
      throw new MembershipException("Membership not found", 404);
    }
    
  }
  
  public function hideUser($hide=true) {
    if($hide) {
      $this->hideuser = true;
    }
    else {
      $this->hideuser = null;
    }
  }
  public function hideBoard($hide=true) {
    if($hide) {
      $this->hideboard = true;
    }
    else {
      $this->hideboard = null;
    }
  }
  
  public function toArray() {
    $exclude = array();
    if($this->hideuser) {
      $exclude[] = "user";
    }
    if($this->hideboard) {
      $exclude[] = "board";
    }
    return parent::toArray($exclude);
  }
  
  public function create($invite= false) {
    if(!$this->userid) {
      throw new MembershipException("You can not create a memebership without a userid", 400);
    }
    if(!$this->boardid) {
      throw new MembershipException("You can not create a memebership without a boardid", 400);
    }
    
    $this->join_date = SC::dbDate();
    $db = new SCDB();
    $insert_array = array(
      "mem_user_id"=>$this->userid,
      "mem_board_id"=>$this->boardid,
      "mem_admin_level"=>$this->admin_level,
      "mem_joindate"=>$this->join_date,
      "mem_receives_emails"=>$this->receives_emails
    );
    $db->insertFromArray($insert_array, "memberships");
    
    $membership_id = mysql_insert_id($db->conn);
    if($membership_id) {
      $this->loadFromMembershipId($membership_id);
      if($invite && ($invite instanceof SCInvite)) {
        $invite->setAccepted();
      }
		}
		else {
      throw new MembershipException(mysql_error($db->conn));
		}
    
    return $this;
  }
  
  public function save() {
    if(!$this->userid) {
      throw new MembershipException("You can not save a memebership without a userid", 400);
    }
    if(!$this->boardid) {
      throw new MembershipException("You can not save a memebership without a boardid", 400);
    }
  
    $db = new SCDB();
    $update_array = array(
      "mem_user_id"=>$this->userid,
      "mem_board_id"=>$this->boardid,
      "mem_admin_level"=>$this->admin_level,
      "mem_joindate"=>$this->join_date,
      "mem_receives_emails"=>$this->receives_emails
    );
    $where = " WHERE mem_id = " . $this->membershipid;
    $db->updateFromArray($update_array, "memberships", $where);
    
    if(mysql_error($db->conn) !== "") {
      throw new MembershipException(mysql_error($db->conn));
    }
    
    //$membership = new SCMembership($this->membershipid);
    //$this->fromArray($membership->toArray());
    
    return $this;
  }
  
  public function delete() {
    if(!$this->membershipid) {
      throw new MembershipException("You can not delete a memebership without a membership", 400);
    }
    
    $db = new SCDB();
    $db->query("DELETE FROM memberships WHERE mem_id=".$this->membershipid);
    $membership_id = mysql_insert_id($db->conn);
    if(mysql_error($db->conn) !== "") {
      throw new MembershipException(mysql_error($db->conn));
    }
    return true;
  }
  
}

class MembershipException extends Exception {}

?>

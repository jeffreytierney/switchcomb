<?php

class SCViewed extends SCBase{

  public $user;
  public $threads;
  public $view_counts;
  public $view_ids;

  function __construct($user, $threads) {
    $this->user = $user;
    $this->threads = $threads;
    
    $this->getViewCounts();
  }
  
  function __destruct() {
  }
  
  public function getViewCounts() {
    $this->view_counts = array();
    $this->view_ids = array();
    $db = new SCDB();
    
    $view_counts = $db->q(
      array("*"),
      array("viewed"),
      array("view_user_id"=>$this->user->userid,"view_msg_id"=>$this->threads)
    );
    
    if(sizeof($view_counts)) {
      foreach($view_counts as $id=>$view_count) {
        $this->view_counts[$view_count["view_msg_id"]] = $view_count["view_msg_count"];
        $this->view_ids[$view_count["view_msg_id"]] = $view_count["view_id"];
      }
    }
    return $this;
  }
  
  public function setViewCount($count, $threadid=false) {
    $thread = $this->threads;
    if(is_array($this->threads)) {
      if($threadid) {
        $thread = $this->threads[$thread];
      }
      else {
        throw new ViewedException("You have to specify a single thread to update the view count for");
      }
    }
    
    $db = new SCDB();
    if(isset($this->view_ids[$thread])) {
      $update_array = array(
        "view_msg_count"=>$count
      );
      $where = "WHERE view_id = " . $this->view_ids[$thread];
      
      $db->updateFromArray($update_array, "viewed", $where);
      if(mysql_error($db->conn) !== "") {
        throw new ViewedException(mysql_error($db->conn));
      }
    }
    else {
      $insert_array = array(
        "view_user_id"=>$this->user->userid,
        "view_msg_count"=>$count,
        "view_msg_id"=>$thread
      );
      
      $db->insertFromArray($insert_array, "viewed");
      $view_id = mysql_insert_id($db->conn);
      if($view_id) {
        $this->view_ids[$thread] = $view_id;
      }
      else {
        throw new ViewedException(mysql_error($db->conn));
      }
      
    }
    
    $this->view_counts[$thread] = $count;
    
    return $this;
  }
}

class ViewedException extends Exception {}

?>

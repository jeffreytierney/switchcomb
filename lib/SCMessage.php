<?php

class SCMessage extends SCBase {
	public $existing;
	public $messageid;
	public $threadid;
	public $boardid;
	public $subject;
  public $text;
	public $created;
	public $authorid;
	public $nsfw;
  public $source;
  public $type;
  public $media;
  public $caption;

  private $authoringuser;

  static $acceptable_types = array(
    "text"=>1,
    "image"=>2,
    "video"=>3,
    "link"=>4,
  );


  function __construct($seed=false) {
    if($seed) {
      if(is_numeric($seed)) {
        $seed = intval($seed);
      }
      switch(gettype($seed)) {
        case "integer":
            $this->loadInfo($seed);
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

  protected function setNull() {
    $this->existing = null;
		$this->messageid = null;
		$this->threadid = null;
		$this->boardid = null;
		$this->subject = null;
		$this->text = null;
		$this->created = null;
		$this->authorid = null;
		$this->authoringuser = null;
		$this->nsfw = null;
    $this->source = SC_MSGSOURCE;
    $this->type = "text";
		$this->media = null;
		$this->caption = null;
  }

  private function loadInfo($messageid, $count=false) {
    //$sql = "SELECT * FROM messages m WHERE m.msg_id=$messageid";
    $db = new SCDB();
		//$messageinfo = $db->queryArray($sql);
		$messageinfo = $db->q(
      array("*"),
      array("messages m"),
      array("m.msg_id"=>$messageid)
    );

		if(sizeof($messageinfo)) {
      $this->fromArray($messageinfo);
    }
    else {
      throw new MessageException("Message not found", 404);
    }
  }

  protected function fromArray($arr) {
    $this->setNull();
    if(sizeof($arr)) {
      if(is_array($arr[0])) {
        $messageinfo = $arr[0];
      }
      else {
        $messageinfo = $arr;
      }

			$this->messageid = $messageinfo["msg_id"] or $this->messageid = $messageinfo["messageid"];
			$this->threadid = $messageinfo["msg_thread"] or $this->threadid = $messageinfo["threadid"];
			$this->boardid = $messageinfo["msg_board_id"] or $this->boardid = $messageinfo["boardid"];
			$this->subject = $messageinfo["msg_subject"] or $this->subject = $messageinfo["subject"];
			$this->text = $messageinfo["msg_text"] or $this->text = $messageinfo["text"];
			$this->created = $messageinfo["msg_date"] or $this->created = $messageinfo["created"];
			$this->authorid = $messageinfo["msg_author"] or $this->authorid = $messageinfo["authorid"];
			$this->type = $messageinfo["msg_type"] or $this->nsfw = $messageinfo["type"];

      if(!isset(SCMessage::$acceptable_types[$this->type])) {
        $this->type = "text";
      }

      $this->media = $messageinfo["msg_media"] or $this->nsfw = $messageinfo["media"];
      $this->caption = $messageinfo["msg_media_caption"] or $this->nsfw = $messageinfo["caption"];

      if($messageinfo["msg_source"] || $messageinfo["source"]) {
        $this->source = $messageinfo["msg_source"] or $this->source = $messageinfo["source"];
      }

      if($this->messageid) {
        $this->existing = true;
      }

      if(isset($messageinfo["authoringuser"])) {
        $this->authoringuser = $messageinfo["authoringuser"];
      }


      if($messageinfo["author"] && $messageinfo["author"]->existing) {
        $this->authoringuser = $messageinfo["author"];
      }
      /*
      else {
        if($this->authorid) {
          if($messageinfo["user_id"]) {
            $this->author = new SCUser($messageinfo);
          }
          else {
            $this->author = new SCUser($this->authorid);
          }
        }
      }
      */

    }

    return $this;

  }
  public function author() {
    if($this->authoringuser) {
      return $this->authoringuser;
    }
    else if($this->authorid) {
      $this->authoringuser = new SCUser($this->authorid);
      return $this->authoringuser;
    }

    return false;
  }

  public function create() {
    /*
    if((!$this->author || !$this->author->existing) && $this->authorid) {
      $this->author = new SCUser($this->authorid);
    }
    */
    if(!$this->author() || !$this->author()->userid) {
      throw new MessageException("You need a valid userid to create a message");
    }
    if(!$this->threadid) {
      throw new MessageException("You need a valid threadid to create a message");
    }
    if(!$this->boardid) {
      throw new MessageException("You need a valid boardid to create a message");
    }
    if(!$this->text) {
      throw new MessageException("You need a valid message to create a message");
    }

		//$sql = "INSERT INTO messages (msg_date, msg_author, msg_subject, msg_text, msg_board_id" . ($source ? ", msg_source" : "") . ") VALUES('".SC::dbDate()."', $userid, '".SC::dbString($subject) ."', '" .SC::dbString($text) ."', " . $this->boardid  . ($source ? ", '" . SC::dbString($source) . "'" : "") . ")";
		$db = new SCDB();
		//$db->query($sql);


    $insert_array = array(
      "msg_date"=>SC::dbDate(),
      "msg_author"=>SC::dbString($this->author()->userid, true),
      "msg_subject"=>SC::dbString($this->subject, true),
      "msg_text"=>SC::dbString($this->text, true),
      "msg_thread"=>$this->threadid,
      "msg_board_id"=>$this->boardid,
      "msg_source"=>SC::dbString($this->source, true),
    );

    $db->insertFromArray($insert_array, "messages");

		$newmessage = mysql_insert_id($db->conn);
		if($newmessage) {
      $message = new SCMessage($newmessage);
      $this->fromArray($message->toArray());

      try{
        $messageMail = SCEmail::newMessageEmail($message);
        $messageMail->sendEmail();
      }
      catch (Exception $ex) {
      }

			return $this;
		}
		else {
      throw new MessageException(mysql_error($db->conn));
    }
  }

  public function toArray($for_db=false) {
    if(!$for_db) {
      $props = parent::toArray();
      $props["author"] = $this->author();
    }

    return $props;
  }

}

class MessageException extends Exception {}

?>

<?php

class SCThread extends SCMessage {
  public $message_count;
  public $hidemessages;

	private $messageset;
  // public function messages()

	function __construct($seed=false, $count=false) {
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
    else {
      $this->setNull();
    }
	}

  function __destruct() {
    $this->setNull();
  }

	protected function setNull() {
		parent::setNull();
    $this->message_count = 0;
		//$this->messageset = array();
    $this->messageset = null;
	}


	private function loadInfo($threadid, $count=false) {
		$limitclause = "";
		if($count) $limitclause = " LIMIT 0, $count";
    //$sql = "SELECT * FROM messages m, users u WHERE (m.msg_id=$threadid OR m.msg_thread=$threadid) AND u.user_id=m.msg_author ORDER BY m.msg_id ASC $limitclause";
    $db = new SCDB();
		//$threadinfo = $db->queryArray($sql);
		$threadinfo = $db->q(
      array("*"),
      array("messages m"),
      array("m.msg_id"=>$threadid, "m.msg_thread"=>0)
    );

		if(sizeof($threadinfo)) {
      $this->fromArray($threadinfo);
		}
    else {
      throw new ThreadException("Thread not found", 404);
    }
	}

  public function messages() {
    if(!$this->hidemessages) {
      if($this->messageset !== null && is_array($this->messageset)) {
        return $this->messageset;
      }
      else {
        return $this->getMessages()->messageset;
      }
    }
    else {
      return array();
    }
  }

  public function getMessages($count=false, $since=false) {
    $limitclause = "";
		if($count) $limitclause = " LIMIT 0, $count";
    $sinceclause = "";
		if($since) $sinceclause = " AND m.msg_id > $since ";

    $sql = "SELECT * FROM messages m, users u WHERE (m.msg_id=".$this->messageid." OR m.msg_thread=".$this->messageid.") AND u.user_id=m.msg_author $sinceclause ORDER BY m.msg_id ASC $limitclause";
    $db = new SCDB();
		$threadinfo = $db->queryArray($sql);
    $this->messageset = array();
    foreach($threadinfo as $id=>$msg) {
      $this->messageset[] = new SCMessage($msg);
    }
    return $this;
  }

  public function getMessageCount() {

    $db = new SCDB();
		$threadinfo = $db->q(
      array("count(msg_id) as msg_count"),
      array("messages"),
      array("msg_thread"=>$this->messageid)
    );
    if(sizeof($threadinfo)) {
      $count = intval($threadinfo[0]["msg_count"]);
    }
    $this->message_count = $count;

    return $this;
  }

  protected function fromArray($arr) {
    parent::fromArray($arr);
    $this->message_count = 0;
    if(sizeof($arr)) {
      if(is_array($arr[0])) {
        $threadinfo = $arr[0];
      }
      else {
        $threadinfo = $arr;
      }
      if(isset($threadinfo["hidemessages"])) {
        $this->hidemessages = $threadinfo["hidemessages"];
      }
    }

    return $this;

  }

  public function create() { //$userid, $subject, $text, $source=false) {
    if(!$this->author() || !$this->author()->userid) {
      throw new ThreadException("You need a valid userid to create a thread");
    }
    if(!$this->boardid) {
      throw new ThreadException("You need a valid boardid to create a thread");
    }
    if(!$this->subject) {
      throw new ThreadException("You need a subject to create a thread");
    }
    if(!$this->text&& !$this->media) {
      throw new ThreadException("You need a valid message to create a thread");
    }
    if(!$this->author()->isMemberOf($this->boardid)) {
      throw new ThreadException("You may only create threads for boards you belong to", 401);
    }

    if($this->type == "image") {
        $asset = new SCAsset($this->author()->userid, $this->media);
        $this->media = $asset->hash;
      }
		//$sql = "INSERT INTO messages (msg_date, msg_author, msg_subject, msg_text, msg_board_id" . ($source ? ", msg_source" : "") . ") VALUES('".SC::dbDate()."', $userid, '".SC::dbString($subject) ."', '" .SC::dbString($text) ."', " . $this->boardid  . ($source ? ", '" . SC::dbString($source) . "'" : "") . ")";
		$db = new SCDB();
		//$db->query($sql);


    $insert_array = array(
      "msg_date"=>SC::dbDate(),
      "msg_author"=>SC::dbString($this->author()->userid, true),
      "msg_subject"=>SC::dbString($this->subject, true),
      "msg_text"=>SC::dbString($this->text, true),
      "msg_board_id"=>$this->boardid,
      "msg_source"=>SC::dbString($this->source, true),
      "msg_type"=>SC::dbString($this->type, true),
    );

    if($this->media) {
      $insert_array["msg_media"] = SC::dbString($this->media, true);
    }
    if($this->caption) {
      $insert_array["msg_media_caption"] = SC::dbString($this->caption, true);
    }

    $db->insertFromArray($insert_array, "messages");

		$newthread = mysql_insert_id($db->conn);
		if($newthread) {
      /*
			if(!SC::isLocal()) {
				$user = SCUser::newFromId($userid);
				$Name = "Switchcomb"; //senders name
				$email = $this->boardid."@boards.switchcomb.com"; //senders e-mail adress
				$recipient = ""; //recipient
				$mail_body = "Posted By: " . $user->displayname . "\n\n" .str_replace("<br/>", "\n", $text); //mail body
				$subject = "[" . $newthread . "] $subject"; //subject
				$header = "From: ". $Name . " <" . $email . ">\r\nBcc:" . SC::emailList() ."\r\n"; //optional headerfields

				mail($recipient, $subject, $mail_body, $header); //mail command :)
			}
      */
      $thread = new SCThread($newthread);
      $this->fromArray($thread->toArray());

      try{
        $messageMail = SCEmail::newMessageEmail($thread);
        $messageMail->sendEmail();
      }
      catch (Exception $ex) {
      }

			return $this;
		}
		else {
      throw new ThreadException(mysql_error($db->conn));
    }
	}

	public function addMessage($message_init) {
    //$message_init["threadid"] = $this->messageid;
    //$message_init["boardid"] = $this->boardid;
    $message = new SCMessage($message_init);
    $message->threadid = $this->messageid;
    $message->boardid = $this->boardid;

    //echo $message->jsonify();

    return $message->create();

	}

	public function hasMessage($messageid) {
		if($messageid=$this->messageid) return true;
		//$sql = "SELECT * from messages WHERE msg_id=$messageid AND msg_thread=".$this->messageid;
		$db = new SCDB();
		$hasMessage = $db->q(
      array("*"),
      array("messages"),
      array("msg_id"=>$message_id, "msg_thread"=>$this->messageid)
    );
		if(sizeof($hasMessage) && $hasMessage[0]["msg_id"] == $messageid) return true;
		else return false;
	}
  /*
	// TODO: get rid of
	public function loadMessages($threadid, $since=false) {
		$sinceclause = "";
		if($since) $sinceclause = " AND m.msg_id > $since ";
		$sql = "SELECT * FROM messages m, users u WHERE (m.msg_id=$threadid OR m.msg_thread=$threadid) AND u.user_id=m.msg_author $sinceclause ORDER BY m.msg_id ASC";
		$db = new SCDB();
		$new_messages = $db->queryArray($sql);
		$messages=array();
		if(sizeof($new_messages)) {
			foreach($new_messages as $id=>$msg) {
				$messages[] = array(
					"id"=>$msg["msg_id"],
					"date"=>$msg["msg_date"],
					"subject"=>$msg["msg_subject"],
					"text"=>$msg["msg_text"],
					"authorid"=>$msg["msg_author"],
					"authorname"=>$msg["user_displayname"],
					"replyto"=>$msg["msg_replyto"],
					"source"=>$msg["msg_source"],
					"nsfw"=>$msg["msg_nsfw"],
					"ext_id"=>$msg["msg_extid"],
					"threadid"=>$msg["msg_thread"]
				);
			}
		}
		return $messages;
	}
  */

  public function emailAddress() {
    return str_replace(":boardid", $this->messageid.".".$this->boardid, SC_MESSAGE_EMAIL_FROM_ADDRESS);
  }

  public function toArray() {
    $props = parent::toArray();
    $props["messages"] = $this->messages();
    return $props;
  }
}

class ThreadException extends Exception {}

?>

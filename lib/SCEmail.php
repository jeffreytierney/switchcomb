<?php

class SCEmail {

  private $from;
  private $sender;
  private $subject;
  private $body;
  private $to;
  private $bcc;
  private $header;
  
  function __construct() {
    
  }
  
  function __destruct() {
    
  }
  
  static function newMessageEmail($message) {
    $mail = new SCEmail();
    $mail->bcc = $mail->loadMessageEmailRecipients($message);
    
    $mail->from = SC_MESSAGE_EMAIL_FROM . "<" . str_replace(":boardid", $message->boardid, SC_MESSAGE_EMAIL_FROM_ADDRESS) . ">";
    $mail->to = "";
    
    $mail->author = $message->author();
    $thread = $message->threadid ? new SCThread($message->threadid) : $message;
    
    $mail->body = "Posted By: " . $mail->author->displayname . "\n\n" .str_replace("<br/>", "\n", $message->text); //mail body
    $mail->subject = "[" . $message->threadid . "] " . $thread->subject; //subject
    
    $mail->headers = "From: " . $mail->from . "\r\n".
                     "Bcc: " . $mail->bcc . "\r\n".
                     "Reply-To: " . str_replace(":boardid", $message->boardid, SC_MESSAGE_EMAIL_FROM_ADDRESS) . "\r\n" .
                     "X-Mailer: PHP/" . phpversion();
    
    
    
    return $mail;
  }
  
  static function newInviteEmail($invite) {
    $mail = new SCEmail();
    
    $mail->from = SC_INVITE_EMAIL_FROM . "<" . str_replace(":board_id", $invite->board()->boardid, SC_INVITE_EMAIL_FROM_ADDRESS) . ">";

    $mail->to = $invite->email;
    
    $mail->body = $message = "You have been invited to join the following board on switchcomb.com\n" . 
				$invite->board()->boardname . 
				"\n\n" . 
				"Click on the following link to join:\n" .
        "http://switchcomb.com".SCRoutes::set("boards", "invitations_redeem", array("invitecode"=>$invite->hash))."\n\n" .
				"Or, if you are on a mobile device, click the following mobile friendly link:\n" .
        "http://m.switchcomb.com".SCRoutes::set("boards", "invitations_redeem", array("invitecode"=>$invite->hash))."\n\n" .
        
    $mail->subject = "switchcomb.com board invitation"; //subject
    
    $mail->headers = "From: " . $mail->from . "\r\n".
                     "Reply-To: " . SC_INVITE_EMAIL_FROM_ADDRESS . "\r\n" .
                     "X-Mailer: PHP/" . phpversion();
    
    
    
    return $mail;
  }
  
  private function loadMessageEmailRecipients($message) {
    $board = new SCBoard($message->boardid);
    $memberships = $board->memberships(true);
    
    if(!sizeof($memberships->memberships)) {
      throw new EmailException("No members on this board receive emails");
    }
    $email_list = array();
    foreach($memberships->memberships as $id=>$membership) {
      $email_list[] = $membership->user->email;
    };
    
    return implode(", ", $email_list);
    
  }
  
  public function sendEmail() {
    if(SC_CANEMAIL) {
      mail($this->to, $this->subject, $this->body, $this->headers); //mail command :)
    }
    /*
    else {
      echo $this->jsonify();
    }
    */
  }
  
  /*
  $user = SCUser::newFromId($userid);
  $Name = "Switchcomb"; //senders name
  $email = $this->boardid."@boards.switchcomb.com"; //senders e-mail adress
  $recipient = ""; //recipient
  $mail_body = "Posted By: " . $user->displayname . "\n\n" .str_replace("<br/>", "\n", $text); //mail body
  $subject = "[" . $newthread . "] $subject"; //subject
  $header = "From: ". $Name . " <" . $email . ">\r\nBcc:" . SC::emailList() ."\r\n"; //optional headerfields
  */
  
  public function jsonify($callback=null) {
    $fields = array("from", "author", "subject", "body", "to", "bcc", "header");
    $props = array();
    foreach($fields as $id=>$field) {
      $props[$field] = $this->$field;
    }
    
    return SC::jsonify($props, $callback);
  }

}

class EmailException extends Exception {}

?>

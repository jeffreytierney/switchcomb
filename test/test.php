<?php

require_once "../sc_lib.php";


// read from stdin
$fd = fopen("emails/iphone_attach.txt", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);

$sc_email = new SCEmailParser($email);

//var_dump($sc_email->attachment);


try {
  if($sc_email->boardid) {
    $board = new SCBoard($sc_email->boardid);
    $user = new SCUser($sc_email->from_address);

    $user_id = $user->userid;
    if($user->isMemberOf($board->boardid)) {
      $message_array = array(
        "authorid"=>$user_id,
        "text"=>$sc_email->body,
        "source"=>"email"
      );

      if ($sc_email->attachment) {
        $message_array["type"] = "image";
        $message_array["attachment"] = array("uploadmedia"=>$sc_email->attachment);
      }
      if($sc_email->threadid) {
        if($board->hasMessage($sc_email->threadid)) {
          $thread = new SCThread($sc_email->threadid);
          $thread->addMessage($message_array);
        }
        else {
          throw new Exception("thread " . $sc_email->threadid . " not in board " . $sc_email->boardid);
        }
      }
      else {
        $message_array["subject"] = $sc_email->subject;
        $board->addThread($message_array);
      }
    }
    else {
     throw new Exception("you dont belong to board " .$sc_email->boardid);
    }
  }
  else {
    throw new Exception("no board id was passed");
  }
}
catch (Exception $ex) {
 echo($ex->getMessage());
}

//mail("jeffrey.tierney@gmail.com", "message processed", $email);
return false;

?>


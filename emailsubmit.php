#!/usr/bin/php
<?php
//$from_email="prod";
require_once('sc_lib.php');

// read from stdin
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);


if(trim($email) == "") return true;

//$thread_id=117;
//$user_id=1;

// handle email
$lines = explode("\n", $email);

// empty vars
$from = "";
$subject = "";
$headers = "";
$tempmessage = "";
$message = "";
$message_array = array();
$to = "";
$splittingheaders = true;


for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
	if (preg_match("/^To: (.*)/", $lines[$i], $matches)) {
            $to = $matches[1];
        }
    } else {
        // not a header, but message
        $tempmessage[] = $lines[$i];
    }

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}

	$found_first_line = false;
	$found_end_header = false;
	$found_message_start = false;
	$found_message_end = false;
	
	$first_line = "";
foreach($tempmessage as $id=>$line) {
	
	if(trim($line) !== "" && !$found_first_line) {
		$first_line = $line;
		$found_first_line = true;
	}
	else if (trim($line) === "" && ($found_first_line && !$found_end_header)) {
		$found_end_header = true;
	}
	else if (trim($line) !== "" && ($found_first_line && $found_end_header && !$found_message_start)) {
		$found_message_start = true;
		$message_array[] = $line;
	}
	else if (trim($line) !== "" && ($found_first_line && $found_end_header && $found_message_start && !$found_message_end)) {
		if($line == $first_line) {
			$found_message_end = true;
		}
		else {
			$message_array[] = $line;
		}
	}
	else {
		continue;
	}
	
	
}

$message = implode("<br/>", $message_array);


//echo "From: $from <br/>subject: $subject<br/>Message: $message";



//$user_id=1;
$thread_re = "/^(?:re\:[\s]*)?\[*([0-9]+)\]/i";
$board_re = "/(.+)\@/i";
$from_re = "/\<([^\>]+)\>/i";

if(preg_match($from_re, $to, $matches)) {
	$to = $matches[1];
}
if(preg_match($board_re, $to, $matches)) {
	$board_id = $matches[1];
}
else {
	$board_id=0;
}

if(preg_match($thread_re, $subject, $matches)) {
	$thread_id = $matches[1];
}
else {
	$thread_id = 0;
}

if(preg_match($from_re, $from, $matches)) {
	$from = $matches[1];
}
try {
  if($board_id) {
    $board = new SCBoard($board_id);
    $user = new SCUser($from);
      
    $user_id = $user->userid;
    if($user->isMemberOf($board->boardid)) {
      if($thread_id) {
        if($board->hasMessage($thread_id)) {
          $thread = new SCThread($thread_id);
          $thread->addMessage(array(
            "authorid"=>$user_id, 
            "text"=>$message,
            "source"=>"email")
            );
        }
        else {
          throw new Exception("thread $thread_id not in board $board_id");
        }
      }
      else {
      
        $board->addThread(array(
          "authorid"=>$user_id,
          "subject"=>$subject,
          "text"=>$message,
          "source"=>"email")
        );
      }
    }
    else {
     throw new Exception("you dont belong to board $board_id");
    }
  }
  else {
    throw new Exception("no board id was passed");
  }
}
catch (Exception $ex) {
  mail($from, $ex->getMessage(), $ex->getMessage());
}

//mail("jeffrey.tierney@gmail.com", "message processed", $email);
return true;
?>


<?php

require_once "../sc_lib.php";

echo SCRoutes::set("users", "memberships_index", array("userid"=>1,"something"=>"else","another"=>"hooha"));

/*
$route = SCRoutes::routeToRegex("/boards/:boardid/thread/:threadid");

$match_count = preg_match($route, "/boards/3241/thread/3409", $matches);

if($match_count) {
  $params = array();
  foreach($matches as $match_name=>$match_value) {
    if(is_string($match_name)) {
      $params[$match_name] = $match_value;
    }
  }
  
  echo var_dump($params);
}
else  {echo "nope";}
*/
/*
echo getcwd();

if($current_user) {
  //echo $current_user->jsonify();
}
else {
  //echo $user_session->create("jeff", "jtswitchcomb")->jsonify();
  //echo "false";
}

$board = new SCBoard(1);
$view_counts = $current_user->getThreadViews($board->getThreadIds());

$partial = SCPartial::renderToString("board/board", array("board"=>$board,"view_counts"=>$view_counts));
echo $partial;
*/
/*
$views = $current_user->getThreadViews(7851);
echo $views->jsonify();

$views = $current_user->setThreadViews(7851, 1111);

echo $views->jsonify();
*/
/*
$thread = new SCThread(7851);
$thread->getMessages(false, 7857);
$thread->getMessageCount();
echo $thread->jsonify();
//$message = $thread->addMessage(array("authoringuser"=>$current_user,"text"=>"Test"));
*/

/*
echo "<br/>";
$board = new SCBoard(1);
$board->sendInvites("me@jeffreytierney.com");
*/
//echo $board->jsonify();

/*
$board = new SCBoard(1);
//$memberships = new SCMembershipSet(false, 1);
$memberships = $board->memberships(1);

echo $memberships->jsonify();

$email_list = array();
foreach($memberships->memberships as $id=>$membership) {
  $email_list[] = $membership->user->email;
};

echo "<br/>" . implode(", ", $email_list);
*/
/*
$db = new SCDB();

$sql = "SELECT * FROM users WHERE length(user_password) < 30";

$result = $db->queryArray($sql);

if(sizeof($result)) {
  foreach($result as $id=>$user) {
    $sql2 = "UPDATE users set user_password='". md5("SCpre_salt".$user["user_password"]."SCpost_salt") ."' WHERE user_id=".$user["user_id"];
    
    echo ($sql2 . "<br/>");
    
    $db2 = new SCDB();
    
    $db2->query($sql2);
  }
}
*/
?>


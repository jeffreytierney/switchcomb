<?php

class SCApi {
  
  static $return_types = array(
    "application/json"=>"json",
    "text/javascript"=>"json",
    "text/html"=>"html",
  );
  
  public function handleResponse($resp, $methodname) {
    $content_type = $this->getResponseContentType();
    switch ($content_type) {
      case "html": 
        $partial = $this->$methodname(array("__partial"=>true));
        if($partial) {
          $partial_name = $partial["partial"];
          if(isset($partial["val"])) {
            $resp = array($partial["val"]=>$resp);
          }
          return SCPartial::renderToString($partial_name, $resp);
        }
        throw new APIException("There is no html associated with this.", 400);
        break;
      case "json":
        return SC::jsonify(SC::toArrayAll($resp));
        break;
    }
  }
  
  private function getResponseContentType() {
    return SC::getResponseContentType("json");
    /*
    $return_types = SC::$return_types;
    $accepts = explode(", ", $_SERVER["HTTP_ACCEPT"]);
    foreach($return_types as $accept_type=>$response_type) {
      if(in_array($accept_type, $accepts)) {
        return $response_type;
      }
    }
    return "json";
    */
  }
  
  private function requireRequestType($req_type) {
    if(strcmp(strtolower($req_type), strtolower($_SERVER['REQUEST_METHOD'])) === 0) {
      return true;
    }
    if(strtolower($_SERVER['REQUEST_METHOD'])==="post") {
      $posted_method = $_POST["method"];
      if($posted_method && strcmp(strtolower($req_type), strtolower($posted_method)) === 0) {
        return true;
      }
    }
    throw new APIException("This method only accepts " . strtoupper($req_type) . " requests.", 405);
    
  }
  
  private function getRequestType() {
    return strtolower($_SERVER['REQUEST_METHOD']);
  }
  
  private function getPutDeleteParams() {
    parse_str(file_get_contents("php://input"),$put_delete_array);
    return $put_delete_array;
  }
  
  private function requireLogin($message) {
    global $current_user;
    if(!$current_user) {
      throw new APIException($message, 401);
    }
    
    return $current_user;
  }
  
  // actual api methods below
  
  
  // USER SESSION
  public function usersessions_create($params = null) { // aka login
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("POST");
      $params = $_POST;
    }
    
    $usersession = new SCUserSession();
    $usersession->destroy();
    if($usersession->create($params["login"], $params["password"], $params["remember"])) {
      $user = $usersession->getSessionUser();
      if($user) {
        return $user;
      }
      else {
        throw new APIException("Username or Password are Incorrect", 401);
      }
    }
  }
  
  public function usersessions_delete($params = null) { // aka logout
    if($params && is_array($params) && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("DELETE");
    }
    
    $usersession = new SCUserSession();
    if($usersession->destroy()) {
      return array("loggedout"=>true);
    }
    else {
      throw new APIException("Username or Password are Incorrect", 401);
    }
  }
  
  //USER
  public function users_show($params = null) {
    if($params && isset($params["__partial"])) return null;

    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view a user");
    
    $user = new SCUser($params["userid"]);
    return $user;
    
  }
  
  public function users_create($params = null) { // aka register
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("PUT");
      $params = $this->getPutDeleteParams();
    }
    
    $password = $params["password"];
    $confirmpassword = $params["confirmpassword"];
    
    $user = new SCUser($params);
    $user->create($password, $confirmpassword);
    
    global $user_session;
    $user_session->create($user->username, $password);
    return $user;
  }
  
  public function users_update($params = null) { // aka update user
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("POST");
      $current_user = $this->requireLogin("You must be logged in to update user attributes");
  
      //$userid = SC::getParam("userid");
      if(!$userid) {
        throw new APIException("No User id", 401);
      }
      if(intval($userid) != intval($current_user->userid)) {
        throw new APIException("You may only update your own user attributes", 403);
      }
      $params = $_POST;
    }
    else {
      $current_user = $this->requireLogin("You must be logged in to update user attributes");
    }
    
    
    $current_user->updateAttributes($params);
    $current_user->save();
    SC::updateSessionUser();
    return $current_user;
    
  }
  
  public function users_memberships_index($params = null) { // aka register
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view your memberships");
    $userid = $params["userid"];
    if(!$userid) {
      throw new APIException("No User id specified", 401);
    }
    
    if(intval($userid) != intval($current_user->userid)) {
      throw new APIException("You may only view your own memebrships", 403);
    }
    
    $user = new SCUser($userid);
    return $user->loadMemberships()->memberships();
  }
  
  public function users_memberships_show($params = null) { // aka register
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    if(!$params["userid"] || !$params["boardid"]) {
      throw new APIException("User id or board id missing", 400);
    }
    $membership = new SCMembership($params["userid"], $params["boardid"]);
    return $membership;
    
  }
  
  // BOARD
  public function boards_show($params = null) {
    if($params && isset($params["__partial"])) return null;

    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view a board");
    
    $board = new SCBoard($params["boardid"]);
    if(!$current_user->isMemberOf($board->boardid)) {
      throw new APIException("You can only view threads from boards you belong to", 403);
    }
    if($params["since"]) {
      $board->loadThreads($params["since"]);
    }
    if($params["hidemessages"]) {
      foreach ($board->threads() as $i=>$thread) {
        $thread->text = "...";
      }
    }
    
    $view_counts = $current_user->getThreadViews($board->getThreadIds());
    return array("board"=>$board,"view_counts"=>$view_counts);
  }
  
  public function boards_preview($params = null) {
    if($params && isset($params["__partial"])) return null;

    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view a board");
    
    $board = new SCBoard($params["boardid"]);
    if($board->privacy) {
      throw new APIException("You can only preview public boards", 403);
    }
    /*if(!$params["showthreads"]) {
      $board->hidethreads = true;
    }
    */
    return $board;
  }
  
  public function boards_index($params = null) { // aka load boards
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    
    $current_user = $this->requireLogin("You must be logged in to view boards");
    
    $board_set = new SCBoardSet();
    $board_set->doLoadBoards($params["privacy"], $params["count"], $params["start"], $params["joined"]);
    
    return $board_set->boards;
    
  }
  
  public function boards_unjoined($params = null) { // aka load boards
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    
    $current_user = $this->requireLogin("You must be logged in to view boards");
    
    $board_set = new SCBoardSet($current_user->userid);
    $board_set->loadUnjoinedBoards(0);
    
    return $board_set->boards;
    
  }
  
  public function boards_create($params = null) { // aka create board
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("PUT");
      $params = $this->getPutDeleteParams();
      $current_user = $this->requireLogin("You must be logged in to create a board");
      $params["creatinguser"] = $current_user;
    }
    
    $board = new SCBoard($params);
    $board->create();
    SC::updateSessionUser();
    return $board;
    
  }
  
  public function boards_threads_create($params = null) { // aka create thread
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("PUT");
      $params = $_POST;
      $put_params = $this->getPutDeleteParams();
      $params = array_merge($params, $put_params);
      $params["boardid"] = $_GET["boardid"];
    }
    $current_user = $this->requireLogin("You must be logged in to join a board");

    $params["authoringuser"] = $current_user;
    
    $board = new SCBoard($params["boardid"]);
    $thread = $board->addThread($params);
    return $thread;
    
  }
  
  public function boards_threads_index($params = null) { // aka create thread
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      /*
      $params = array(
        "boardid"=>SC::getParam("boardid"),
        "start"=>SC::getParam("start"),
        "num"=>SC::getParam("num"),
        "showthreads"=>SC::getParam("showthreads")
      );
      */
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view the threads on a board");
    
    $board = new SCBoard($params["boardid"]);
    if(!$current_user->isMemberOf($board->boardid)) {
      throw new APIException("You can only view threads from boards you belong to", 403);
    }
    
    $hidethreads = !$params["showthreads"];
    $board->loadThreads($params["start"], $params["num"], $hidethreads);
    
    return $board->threads();
    
  }
  
  public function boards_memberships_index($params = null) { // aka load boards
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    
    $current_user = $this->requireLogin("You must be logged in to view board memberships", 401);
    if(!$params["boardid"]) {
      throw new APIException("Board Id Missing", 400);
    }
    
    if(!$current_user->isMemberOf($params["boardid"])) {
      throw new APIException("You can only view members from boards you belong to", 403);
    }
    //TODO: make part of board
    $membershipset = new SCMembershipSet(false, $params["boardid"]);
    $membershipset->loadMembers();
    
    return $membershipset->memberships;
    
  }
  
  public function boards_invitations_create($params = null) { // aka load boards
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("POST");
      $params = $_POST;
      $params["boardid"] = $_GET["boardid"];
    }
    
    $current_user = $this->requireLogin("You must be logged in to create invitations to a board");
    if(!$params["boardid"]) {
      throw new APIException("Board Id Missing", 400);
    }
    
    if(!$params["invite_list"]) {
      throw new APIException("Invite List Missing", 400);
    }
    
    if(!$current_user->isMemberOf($params["boardid"])) {
      throw new APIException("You can only invite people to boards that you belong to", 403);
    }
    //TODO: make part of board
    $board = new SCBoard($params["boardid"]);
    $sent_list = $board->sendInvites($params["invite_list"]);
    
    return $sent_list;
    
  }
  
  // MEMBERSHIP
  public function memberships_show($params = null) { // aka join board
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    if($params["membershipid"]) {
      $membership = new SCMembership($params["membershipid"]);
    }
    else if($params["userid"] && $params["boardid"]) {
      $membership = new SCMembership($params["userid"], $params["boardid"]);
    }
    else {
      throw new APIException("Membership id or userid/boardid pair missing", 400);
    }
    /*
    if(!$params["membershipid"]) {
      throw new APIException("Membership id missing", 400);
    }
    $membership = new SCMembership($params["membershipid"]);
    */
    return $membership;
  }
  
  public function memberships_create($params = null) { // aka join board
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("PUT");
      $current_user = $this->requireLogin("You must be logged in to join a board");
      $params = $_GET;
      $put_params = $this->getPutDeleteParams();
      $params = array_merge($params, $put_params);
      //$params["boardid"] = SC::getParam("boardid");
      //$params["userid"] = SC::getParam("userid");
      //$params["receives_emails"] = SC::getParam("receives_emails");

      if(!$params["userid"]) {
        throw new APIException("No user is specified.", 401);
      }
      if(intval($params["userid"]) != intval($current_user->userid)) {
        throw new APIException("You may only attempt to join a board yourself", 403);
      }
    }
    
    
    $board = new SCBoard($params["boardid"]);
    $board->addUser($current_user->userid, 0, $params["receives_emails"], $params["invitecode"]);
    SC::updateSessionUser();
    return $board;
    
  }
  
  public function memberships_update($params = null) { // aka join board
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("POST");
      $current_user = $this->requireLogin("You must be logged in to join a board");
      $params = $_POST;
      $userid = $_GET["userid"];
      $boardid = $_GET["boardid"];
      $params["receives_emails"] = intval(isset($params["receives_emails"]));
      //$params["boardid"] = SC::getParam("boardid");
      //$params["userid"] = SC::getParam("userid");
      //$params["receives_emails"] = SC::getParam("receives_emails");
      
    }
    if($params["membershipid"]) {
      $membership = new SCMembership($params["membershipid"]);
    }
    else if($userid && $boardid) {
      $membership = new SCMembership($userid, $boardid);
    }
    else {
      throw new APIException("Membership id or userid/boardid pair missing", 400);
    }
    
    if(intval($membership->userid) != intval($current_user->userid)) {
      throw new APIException("You may only attempt to delete your own memberships", 403);
    }
    
    $membership->updateAttributes($params)->save();
    SC::updateSessionUser();
    return $membership;
    
  }
  
    public function memberships_delete($params = null) { // aka join board
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("DELETE");
      $current_user = $this->requireLogin("You must be logged in to delete a membership");
      $params = $_GET;
    }
    if($params["membershipid"]) {
      $membership = new SCMembership($params["membershipid"]);
    }
    else if($params["userid"] && $params["boardid"]) {
      $membership = new SCMembership($params["userid"], $params["boardid"]);
    }
    else {
      throw new APIException("Membership id or userid/boardid pair missing", 400);
    }
    
    if(intval($membership->userid) != intval($current_user->userid)) {
      throw new APIException("You may only attempt to delete your own memberships", 403);
    }
    
    $membership->delete();
    SC::updateSessionUser();
    return true;
    
  }
  
  // THREAD
  
  public function threads_show($params = null) {
    if($params && isset($params["__partial"])) return null;

    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view a thread", 401);
    
    $thread = new SCThread($params["threadid"]);
    /*
    if(!$params["showmessages"]) {
      $thread->hidemessages = true;
    }
    */
    if(!$current_user->isMemberOf($thread->boardid)) {
      throw new APIException("You can only view threads from boards you belong to", 403);
    }
    
    $thread->getMessages(false, $params["since"]);
    if(!$params["noupdate"]) {
      $thread->getMessageCount();
      $current_user->setThreadViews($thread->messageid, $thread->message_count);
    }
    
    return $thread;
    
  }
  
  public function threads_messages_create($params = null) { // aka create thread
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("PUT");
      $params = $_POST;
      $put_params = $this->getPutDeleteParams();
      $params = array_merge($params, $put_params);
      $params["threadid"] = $_GET["threadid"];
    }
    $current_user = $this->requireLogin("You must be logged in to post a message to a thread");

    $params["authoringuser"] = $current_user;
    
    $thread = new SCThread($params["threadid"]);
    $message = $thread->addMessage($params);
    
    return $message;
    
  }
  
  public function threads_messages_index($params = null) { // aka get messages
    if($params && isset($params["__partial"])) return array("partial"=>"thread/thread_messages","val"=>"messages");
    
    if($params === null) {
      $this->requireRequestType("GET");
      /*
      $params = array(
        "threadid"=>SC::getParam("threadid"),
        "since"=>SC::getParam("since")
      );*/
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view the messages on a thread");
    
    $thread = new SCThread($params["threadid"]);
    if(!$current_user->isMemberOf($thread->boardid)) {
      throw new APIException("You can only view messages from threads in boards you belong to", 403);
    }
    
    $thread->getMessages(false, $params["since"]);
    if(!$params["noupdate"]) {
      $thread->getMessageCount();
      $current_user->setThreadViews($thread->messageid, $thread->message_count);
    }
    return $thread->messages();
    
  }
  
  // MESSAGE
  public function messages_show($params = null) {
    if($params && isset($params["__partial"])) return null;
    
    if($params === null) {
      $this->requireRequestType("GET");
      $params = $_GET;
    }
    $current_user = $this->requireLogin("You must be logged in to view a message");
    
    if(!$params["messageid"]) {
      throw new APIException("No messageid provided", 400);
    }
    $message = new SCMessage($params["messageid"]);
    
    if(!$current_user->isMemberOf($message->boardid)) {
      throw new APIException("You can only view messages from boards you belong to", 403);
    }
    
    return $message;
    
  }
  
}

class APIException extends Exception {}


?>

<?php

class SCBoardsController {
  public function show() {
    SC::loginRequired();
    $api = new SCApi();
    $vars = $api->boards_show(); // $board & $view_counts
    
    switch ($_GET["__content_type"]) {
      case "json":
          if($_GET["since"]) {
            $output = array(
              "content"=>SCPartial::renderToString("board/board_threads", $vars)
            );
          }
          else {
            $output = array("threads"=>$vars["board"]->threads());
          }
          echo SC::jsonify($output);
        break;
      case "html":
      default:
    
        $cs = array(
          "title"=>$vars["board"]->boardname,
          "util_links"=>SCPartial::renderToString("board/util_links", $vars),
          "content"=>SCPartial::renderToString("board/board", $vars)
        );
        
        SCLayout::render("main", $vars, $cs);
    }
  }
  public function preview() {
    SC::loginRequired();
    $api = new SCApi();
    $board = $api->boards_preview(); // $board & $view_counts
    
    $vars = array(
      "board"=>$board
    );
    
    $cs = array(
      "title"=>$vars["board"]->boardname,
      "content"=>SCPartial::renderToString("board/preview", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
  }
  public function unjoined() {
    SC::loginRequired();
    $api = new SCApi();
    $boards = $api->boards_unjoined();
    
    $vars = array(
      "boards"=>$boards
    );
    
    $cs = array(
      "title"=>"Find a board to join",
      "content"=>SCPartial::renderToString("board/unjoined", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
  }
  public function _new() {
    SC::loginRequired();
    global $current_user;
    
    $vars = array(
      "boardname"=>SC::getParam("name", true),
      "description"=>SC::getParam("description", true)
    );
    
    $cs = array(
      "title"=>"Create Board",
      "head"=>SCPartial::renderToString("shared/head"),
      "content"=>SCPartial::renderToString("board/new", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
  }
  public function create() {
    SC::loginRequired();
    global $current_user;
    
    try {
      $api = new SCApi();
      $board = $api->boards_create();
      SC::transfer(SCRoutes::set("boards", "show", array("boardid"=>$board->boardid)));
    }
    catch(Exception $ex) {
      SC::setFlashMessage($ex->getMessage(), "error");
      $this->_new();
    }
  }
  public function invitations_new() {
    SC::loginRequired();
    global $current_user;
    
    $vars = array(
      "invite_list"=>SC::getParam("invite_list", true),
      "boardid"=>$_GET["boardid"]
    );
    
    $cs = array(
      "title"=>"Invite others to this board",
      "head"=>SCPartial::renderToString("shared/head"),
      "util_links"=>SCPartial::renderToString("board/newthread_util_links", $vars),
      "content"=>SCPartial::renderToString("board/invitations_new", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
  }
  public function invitations_create() {
    SC::loginRequired();
    global $current_user;
    
    try {
      $api = new SCApi();
      $sent_list = $api->boards_invitations_create();
      SC::transfer(SCRoutes::set("boards", "show", array("boardid"=>$_GET["boardid"])));
    }
    catch(Exception $ex) {
      SC::setFlashMessage($ex->getMessage(), "error");
      $this->invitations_new();
    }
  }
  public function invitations_redeem() {
    SC::loginRequired(false, SCRoutes::set("boards","invitations_redeem", array("invitecode"=>$_GET["invitecode"])));
    global $current_user;
    
    if(!$_GET["invitecode"]) {
      //throw new Exception("You must supply an invitation code that you wish to redeem", 400);
    }
    $invite = new SCInvite($_GET["invitecode"]);
    
    $vars = array(
      "invite"=>$invite,
    );
    
    $cs = array(
      
      "head"=>SCPartial::renderToString("shared/head")
    );
    
    if($invite->isValid()) {
      $cs["title"] = "Would you like to join " . $invite->board()->boardname . "?";
      $cs["content"] = SCPartial::renderToString("board/invitations_redeem", $vars);
    }
    else {
      $cs["title"] = "This is not a valid invite code.";
    }
    
    SCLayout::render("main", $vars, $cs);
  }
}

$controller = new SCBoardsController();

?>

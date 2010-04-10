<?php

class SCMembershipsController {
  public function create() {
    SC::loginRequired();
    global $current_user;
    
    $api = new SCApi();
    $board = $api->memberships_create();
    SC::transfer(SCRoutes::set("users", "memberships_index", array("userid"=>$current_user->userid)));
  }
  public function show($use_params=null) {
    SC::loginRequired();
    global $current_user;
    
    $api = new SCApi();
    if($use_params) {
      $use_params = $_GET;
    }
    $membership = $api->memberships_show($use_params);
    
    $vars = array(
      "membership"=>$membership
    );
    
    $cs = array(
      "title"=>$current_user->displayname . "' s membership in " . $membership->board->boardname,
      "util_links"=>SCPartial::renderToString("board/util_links", $vars),
      "content"=>SCPartial::renderToString("membership/show", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
    
  }
  
  public function update() {
    SC::loginRequired();
    global $current_user;
    try{
      $api = new SCApi();
      $membership = $api->memberships_update();
      SC::transfer(SCRoutes::set("users", "memberships_index", array("userid"=>$current_user->userid)));
    }
    catch(Exception $ex) {
      SC::setFlashMessage($ex->getMessage(), "error");
      $this->show(true);
    }
  }
  
  public function delete() {
    SC::loginRequired();
    global $current_user;
    try{
      $api = new SCApi();
      if($api->memberships_delete()) {
        SC::transfer(SCRoutes::set("users", "memberships_index", array("userid"=>$current_user->userid)));
      }
      else {
        throw new Exception("something went wrong");
      }
    }
    catch(Exception $ex) {
      SC::setFlashMessage($ex->getMessage(), "error");
      $this->show(true);
    }
  }
}

$controller = new SCMembershipsController();

?>

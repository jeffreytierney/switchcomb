<?php
	
class SCUsersController {
  public function memberships_index() {
    
    SC::loginRequired();
    
    global $current_user;
	
    $api = new SCApi();
    $memberships = $api->users_memberships_index();
  
    $vars = array("memberships"=>$memberships); // $board & $view_counts
    
    $cs = array(
      "title"=>$current_user->displayname."'s Boards",
      "content"=>SCPartial::renderToString("user/memberships", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
  }
  public function _new() {
    $vars = array(
      "username"=>SC::getParam("username", true),
      "email"=>SC::getParam("email", true),
      "displayname"=>SC::getParam("displayname", true)
    );
    
    $cs = array(
      "title"=>"Register",
      "content"=>SCPartial::renderToString("user/new", $vars)
    );
    
    SCLayout::render("main", $vars, $cs);
  }
  public function create() {
    
    try {
      $api = new SCApi();
      $user = $api->users_create();
      SC::checkRedir(SCRoutes::set("users", "memberships_index", array("userid"=>$user->userid)));
    }
    catch(Exception $ex) {
      SC::setFlashMessage($ex->getMessage(), "error");
      $this->_new();
    }
  }
  
}

$controller = new SCUsersController();

?>

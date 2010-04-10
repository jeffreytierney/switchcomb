<?php
	
class SCIndexController {
  public function index() {
  
    global $current_user;
    
    if($current_user) {
      $user = $current_user;
      $membershipset = new SCMembershipSet($user->userid);
      $membershipset->loadTopMembershipsByDate(3);
      $membershipset->loadTopMessagesForBoards(3);
      $memberships = $membershipset->memberships;
      
      $vars = array(
        "memberships"=>$memberships,
        "current_user"=>$current_user
      );
    }
    
    SCLayout::render("index", $vars);
  }
}

$controller = new SCIndexController();

?>


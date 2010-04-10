<?php
	
class SCUserSessionsController {
  public function create() {
    try{
      $api = new SCApi();
      $user = $api->usersessions_create();
      if($user) {
        // see if there is a redir sessions var set and if so go there
        // if not go home
        SC::checkRedir();
      }
    }
    catch(Exception $ex) {
      SC::setFlashMessage($ex->getMessage(), "error");
      SC::transfer();
    }
    
  }
  
  public function delete() {
  
    $api = new SCApi();
    $arr = $api->usersessions_delete(true);
    if($arr["loggedout"]) {
      SC::transfer();
    }
    
  }
}

$controller = new SCUserSessionsController();

?>

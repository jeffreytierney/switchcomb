<?php
/*
if(SC::isTest() && !isset($from_email)) {
	$auth_ok = false;
	if($_SERVER['PHP_AUTH_USER'] == "sc" && $_SERVER['PHP_AUTH_PW'] == "scpw") {
		$auth_ok = true;
	}
	if (!$auth_ok) {
	    header('WWW-Authenticate: Basic realm="Switchcomb"');
	    header('HTTP/1.0 401 Unauthorized');
	    echo 'Need To Authenticate';
	    exit;
	}
}
*/

//require_once "define_environment.php";
require_once "config/environment.php";

require_once "config/config.php";

require_once "lib/SC.php";
require_once "lib/SCBase.php";
require_once "lib/SCDB.php";
require_once "lib/SCUserSession.php";
require_once "lib/SCUser.php";
require_once "lib/SCMessage.php";
require_once "lib/SCThread.php";
require_once "lib/SCBoard.php";
require_once "lib/SCInvite.php";
require_once "lib/SCBoardSet.php";
require_once "lib/SCMembershipSet.php";
require_once "lib/SCMembership.php";
require_once "lib/SCEmail.php";
require_once "lib/SCViewed.php";
require_once "lib/SCPartial.php";
require_once "lib/SCLayout.php";

require_once "lib/SCRoutes.php";


require_once "api/SCApi.php";

$current_user = null;
if (!isset($from_email)) {
  $user_session = new SCUserSession();
  $current_user = $user_session->getSessionUser();

  if(!$current_user->existing) {
    $current_user = null;
  }
}


//$current_user = SCUserSession::loggedInUser();

?>

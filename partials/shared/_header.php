
<div id="header" class="clearfix">
	<a href="<?php echo SC::root() ?>" id="header_home"><img src="<?php echo SC::imagePath("logo.png"); ?>" alt="" border="0"></a>
  <?php if($current_user): ?>
    <div id="links">
      <a href="<?php echo SCRoutes::set("users", "memberships_index", array("userid"=>$current_user->userid)); ?>" class="header_link logged_in" id="header_boards">My Boards</a>
      <a href="<?php echo SCRoutes::set("boards", "_new"); ?>" class="header_link logged_in" id="header_create">Create a Board</a>
      <a href="<?php echo SCRoutes::set("boards", "unjoined") ?>" class="header_link logged_in" id="header_find">Find Boards</a>
    </div>
    <div id="loggedin">
      <span id="loggedinuser"><?php echo $current_user->displayname ?></span>
      <a href="<?php echo SCRoutes::set("usersessions", "delete"); ?>">Logout</a>
    </div>
 <?php endif; ?>
</div>


<div id="header">
	<a href="<?php echo SC::root() ?>" id="header_home">Switchcomb</a>
  <?php if($current_user): ?>
    <div id="loggedin" class="clearfix">
        <div id="auth_container">
          <span id="loggedinuser"><?php echo $current_user->displayname ?></span>
          <a href="<?php echo SCRoutes::set("usersessions", "delete"); ?>">Logout</a>
        </div>
      <ul id="links" class="clearfix">
        <li><a href="<?php echo SCRoutes::set("users", "memberships_index", array("userid"=>$current_user->userid)); ?>" class="header_link logged_in" id="header_boards">My Boards</a></li>
        <li><a href="<?php echo SCRoutes::set("boards", "_new"); ?>" class="header_link logged_in" id="header_create">Create a Board</a></li>
        <li><a href="<?php echo SCRoutes::set("boards", "unjoined") ?>" class="header_link logged_in" id="header_find">Find Boards</a></li>
      </ul>
    </div>
 <?php endif; ?>
</div>

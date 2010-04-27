<ul class="util_links" class="clearfix">
  <li><a href="<?php echo SCRoutes::set("users", "memberships_index", array("userid"=>$current_user->userid)); ?>">Back to MyBoards</a></li>
  <li><a id="create_thread_link" href="<?php echo SCRoutes::set("threads", "_new", array("boardid"=>$board->boardid)); ?>">Create Thread</a></li> 
  <li><a href="<?php echo SCRoutes::set("boards", "invitations_new", array("boardid"=>$board->boardid)); ?>">Invite Others</a></li>
</ul>

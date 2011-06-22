<div id="membership">
  <h4 id="boardname"><?php echo $membership->board->boardname; ?></h4>
  <div id="boardcreator">Created By: <?php echo $membership->board->creator()->displayname; ?></div>
  <div id="boarddescription"><?php echo $membership->board->boarddescription; ?></div>
  <div id="boardcreatedate"><?php echo $membership->board->timeAgo(); ?></div>
  <form id="update_membership" method="POST" action="<?php echo SCRoutes::set("memberships","update", array("userid"=>$current_user->userid,"boardid"=>$membership->board->boardid)); ?>">
    <input type="checkbox" id="cb_receives_emails" name="receives_emails" <?php echo $membership->receives_emails ? " checked" : ""; ?>/>
    <label for="cb_receives_emails" id="boardemails">Receive Emails</label>
    <input type="submit" id="btn_submit" value="save" />
  </form>
  <form id="delete_membership" method="POST" action="<?php echo SCRoutes::set("memberships","delete", array("userid"=>$current_user->userid,"boardid"=>$membership->board->boardid)); ?>">
    <input type="hidden" name="method" value="DELETE" />
    <input type="submit" id="btn_submit" value="leave board" />
  </form>
</div>

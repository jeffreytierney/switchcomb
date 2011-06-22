<?php if(sizeof($memberships)): ?>
  <div id="boardset">
    <?php foreach($memberships as $id=>$membership): ?>
    
      <div id="boardlink_<?php echo $membership->board->boardid; ?>" class="boardset_board">
        <div class="clearfix">
          <div class="boardset_boardname"><a href="<?php echo SCRoutes::set("boards", "show", array("boardid"=>$membership->board->boardid)); ?>"><?php echo $membership->board->boardname; ?></a></div>
          <div class="boardset_boardcreator">Created By: <?php echo $membership->board->creator()->displayname; ?></div>
        </div>
        <div class="clearfix">
          <div class="boardset_boardname"><?php echo $membership->board->boarddescription; ?></div>
          <div class="boardset_boardcreator"><?php echo $membership->board->timeAgo(); ?></div>
        </div>
        <div class="clearfix">
          <div class="boardset_boardname">Receive Emails: <?php echo $membership->receives_emails ? "yes" : "no"; ?></div>
          <div class="boardset_boardcreator"><a href="<?php echo SCRoutes::set("memberships", "show", array("userid"=>$current_user->userid,"boardid"=>$membership->board->boardid)); ?>">Edit</a></div>
        </div>
        <!--<?php echo SC::privacy($board->privacy); ?>-->
      </div>
    
    <?php endforeach; ?>
  </div>
<?php else: ?>
  Oh Noes <?php echo $current_user->displayname ?>!  You don't belong to any boards! <a href="<?php echo SCRoutes::set("boards", "unjoined") ?>">Find Public Boards to Join</a> or <a href="<?php echo SCRoutes::set("boards", "_new"); ?>">create your own</a> to get started.
<?php endif; ?>

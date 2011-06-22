<div id="board_preview">
  <?php if($board->description): ?>
    <p><?php echo htmlspecialchars($board->description); ?></p>
  <?php endif; ?>
  <ul id="board_members">
    <?php foreach($board->memberships()->memberships as $key=>$member): ?>
      <li><?php $member_user = new SCUSer($member->userid); echo htmlspecialchars($member_user->displayname); ?></li>
    <?php endforeach; ?>
  </ul>
  <form id="create_membership" method="POST" action="<?php echo SCRoutes::set("memberships","create", array("userid"=>$current_user->userid,"boardid"=>$board->boardid)); ?>">
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_submit" value="join board" />
  </form>
</div>

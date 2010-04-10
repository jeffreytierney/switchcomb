<div id="board_preview">
  <?php if($board->description): ?>
    <p><?php echo $member->description; ?></p>
  <?php endif; ?>
  <ul id="board_members">
    <?php foreach($board->memberships()->memberships as $key=>$member): ?>
      <li><?php $member_user = new SCUSer($member->userid); echo $member_user->displayname; ?></li>
    <?php endforeach; ?>
  </ul>
</div>

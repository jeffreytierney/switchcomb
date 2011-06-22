<?php foreach($boards as $id=>$board): ?>
  <div id="joinlink_board_<?php echo $board->boardid; ?>">
    <?php echo $board->boardname; ?>
    <?php echo $board->creator()->displayname; ?>
    <?php echo $board->timeAgo(); ?>
    <?php if(SC::isLoggedIn()):?>
      <a href="<?php echo SCRoutes::set("boards", "preview", array("boardid"=>$board->boardid)); ?>">join</a>
    <?php endif; ?>
  </div>

<?php endforeach; ?>

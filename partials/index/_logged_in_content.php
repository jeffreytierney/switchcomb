<div id="boardset">
  <?php foreach($memberships as $id=>$membership): ?>
    <div id="board_<?php echo $membership->board->boardid; ?>" class="boardset_board">
      <div class="boardset_boardheader clearfix">
        <div class="boardset_boardname">
          <a href="<?php echo SCRoutes::set("boards", "show", array("boardid"=>$membership->board->boardid)); ?>"><?php echo htmlspecialchars($membership->board->boardname); ?></a>
        </div>
        <div class="boardset_boardactivity">
          <?php echo ($membership->board->lastpost ? "Last Post: " . $membership->board->lastpost : "No threads yet"); ?>
        </div>
      </div>
      <?php foreach($membership->board->threads() as $thread_id=>$thread): ?>
        <div class="boardset_boardthread clearfix">
          <div class="boardset_boardthread_subject"> - <a href="<?php echo SCRoutes::set("threads", "show", array("boardid"=>$thread->boardid, "threadid"=>$thread->messageid)); ?>"><?php echo $thread->subject; ?></a></div>
          <div class="boardset_boardthread_replies"><?php echo $thread->message_count ?> post<?php if(($thread->message_count>=0)&&$thread->message_count!=1)echo "s"?></div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endforeach; ?>
</div>

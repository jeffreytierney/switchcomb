<?php $count=0; foreach($board->threads() as $id=>$thread): ?>
  <a href="<?php echo SCRoutes::set("threads", "show", array("boardid"=>$thread->boardid,"threadid"=>$thread->messageid)); ?>" id="thread_<?php echo $thread->messageid; ?>" class="boarditem <?php echo ((($count++)===(sizeof($board->threads)-1)) ? "boarditemlast" : ""); ?>  <?php echo (!isset($view_counts->view_counts[$thread->messageid]) || intval($view_counts->view_counts[$thread->messageid]) < intval($thread->message_count)) ? "new":""; ?> clearfix">
    <span class="board_threadname">
      <?php echo $thread->subject; ?>
    </span>
    <span class="board_threadcreatedby">
      <?php echo $thread->author()->displayname ?>
    </span>
    <span class="board_threadcreatedate">
      <?php echo $thread->created ?>
    </span>
    <span class="board_threadreplies">
      <?php echo $thread->message_count ?>
    </span>
  </a>
<?php endforeach; ?>

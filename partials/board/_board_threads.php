<?php $count=0; foreach($board->threads() as $id=>$thread): ?>
  <div id="thread_<?php echo $thread->messageid; ?>" class="boarditem <?php echo ((($count++)===(sizeof($board->threads)-1)) ? "boarditemlast" : ""); ?> clearfix">
    <div class="board_threadname">
      <a href="<?php echo SCRoutes::set("threads", "show", array("boardid"=>$thread->boardid,"threadid"=>$thread->messageid)); ?>"><?php echo $thread->subject; ?></a>
      <?php if(!isset($view_counts->view_counts[$thread->messageid]) || intval($view_counts->view_counts[$thread->messageid]) < intval($thread->message_count)): ?>
       - NEW
      <?php endif; ?>
    </div>
    <div class="board_threadcreatedby">
      <?php echo $thread->author()->displayname ?>
    </div>
    <div class="board_threadcreatedate">
      <?php echo $thread->created ?>
    </div>
    <div class="board_threadreplies">
      <?php echo $thread->message_count ?>
    </div>
  </div>
<?php endforeach; ?>

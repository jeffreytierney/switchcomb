<?php if(sizeof($board->threads())): ?>
  <div id="board_<?php echo $board->boardid; ?>" class="board">

    <div id="board_header" class="clearfix">
      <div class="board_threadname header">
        Thread Name
      </div>
      <div class="board_threadcreatedby header">
        Created By
      </div>
      <div class="board_threadcreatedate header">
        Create Date
      </div>
      <div class="board_threadreplies header">
        Replies
      </div>
    </div>
    <div id="board_threads" class="clearfix">
      <?php SCPartial::render("board/board_threads", array("board"=>$board,"view_counts"=>$view_counts)); ?>
    </div>
  </div>
<?php endif; ?>

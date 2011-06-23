<div id="thread_<?php echo $thread->boardid; ?>_<?php echo $thread->messageid; ?>" class="thread">
  <div id="messages">
    <?php SCPartial::render("thread/thread_messages", array("thread"=>$thread)); ?>
  </div>
  <div id="loadmore">
    <a href="<?php echo SCRoutes::set("threads", "show", array("boardid"=>$thread->boardid, "threadid"=>$thread->messageid)); ?>">
      <span id="loadmore_count">0</span>
      New Messages
    </a>
  </div>
</div>

<?php $now = time(); SCBlock::set("javascript", <<<JS
<script type="text/javascript">
$(function() {
    new SC.TimeUpdater($now, {is_seconds:true});
});
</script>
JS
); ?>

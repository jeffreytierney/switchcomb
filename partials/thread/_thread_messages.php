<?php $msgs = $messages or $msgs = $thread->messages(); foreach($msgs as $id=>$message): ?>

  <div id="message_<?php echo $message->messageid; ?>" class="message">

    <div class="message_options">
      <ul class="message_options_content">
        <li>
          <a class="reply_link" href="<?php $threadid = $message->threadid or $threadid = $message->messageid; echo SCRoutes::set("messages", "_new", array("boardid"=>$message->boardid, "threadid"=>$threadid)); ?>">Reply</a>
        </li>
      </ul>
    </div>
    <div class="message_content">
      <div class="message_info clearfix">
        <div class="message_date">
          <?php echo $message->created; ?>
        </div>
        <div class="message_source">
          <?php echo $message->source; ?>
        </div>
        <div class="message_author">
          <?php echo $message->author()->displayname; ?>
        </div>
      </div>
      <?php if($message->subject != "" && $message->threadid !=0): ?>
        <div class="changed_subject"><?php echo $message->subject; ?></div>
      <?php endif; ?>
      <div class="message_text">
        <?php SCPartial::render("message/".$message->type."_body", array("message"=>$message)); ?>
      </div>
    </div>
  </div>

<?php endforeach; ?>

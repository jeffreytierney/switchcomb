<?php echo $message->media; ?>
<?php if ($message->caption): ?>
  <br/>
  <?php echo $message->caption; ?>
<?php endif; ?>
<?php if ($message->text): ?>
  <br/></br/>
  <?php echo $message->text; ?>
<?php endif; ?>

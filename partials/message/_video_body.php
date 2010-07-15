<?php echo $message->media; ?>
<?php if ($message->caption): ?>
  <?php echo $linebreak; ?>
  <?php echo $message->caption; ?>
<?php endif; ?>
<?php if ($message->text): ?>
  <?php echo $linebreak.$linebreak; ?>
  <?php echo str_replace("\n", $linebreak, $message->text); ?>
<?php endif; ?>

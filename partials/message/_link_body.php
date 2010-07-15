<a href="<?php echo $message->media; ?>">
<?php if ($message->caption): ?>
  <?php echo $message->caption; ?>
<?php else: ?>
  <?php echo $message->media; ?>
<?php endif; ?>
</a>
<?php if ($message->text): ?>
  <?php echo $linebreak.$linebreak; ?>
  <?php echo str_replace("\n", $linebreak, $message->text); ?>
<?php endif; ?>

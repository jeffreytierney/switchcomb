<a href="<?php echo htmlspecialchars($message->media); ?>">
<?php if ($message->caption): ?>
  <?php echo htmlspecialchars($message->caption); ?>
<?php else: ?>
  <?php echo htmlspecialchars($message->media); ?>
<?php endif; ?>
</a>
<?php if ($message->text): ?>
  <?php echo $linebreak.$linebreak; ?>
  <?php echo str_replace("\n", $linebreak, htmlspecialchars($message->text)); ?>
<?php endif; ?>

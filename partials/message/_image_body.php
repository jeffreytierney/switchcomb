<img src="<?php echo SCAsset::assetUrl($message->media); ?>" />
<?php if ($message->caption): ?>
  <?php echo $linebreak; ?>
  <?php echo htmlspecialchars($message->caption); ?>
<?php endif; ?>
<?php if ($message->text): ?>
  <?php echo $linebreak.$linebreak; ?>
  <?php echo str_replace("\n", $linebreak, htmlspecialchars($message->text)); ?>
<?php endif; ?>

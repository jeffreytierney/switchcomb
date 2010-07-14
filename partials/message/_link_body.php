<a href="<?php echo $message->media; ?>">
<?php if ($message->caption): ?>
  <?php echo $message->caption; ?>
<?php else: ?>
  <?php echo $message->media; ?>
<?php endif; ?>
</a>
<?php if ($message->text): ?>
  <br/></br/>
  <?php echo $message->text; ?>
<?php endif; ?>

<form id="createmessage" method="POST" action="<?php echo SCRoutes::set("messages", "create", array("boardid"=>$boardid,"threadid"=>$threadid)); ?>">
  <p>
    <label for="message_create_subject">Subject:</label>
    <input type="text" id="message_create_subject" name="subject" value="<?php echo $subject; ?>" />
  </p>
  <p>
    <label for="message_create_text">Message:</label>
    <textarea id="message_create_text" name="text" rows="5" cols="50"><?php echo $text; ?></textarea>
  </p>
  <p>
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_create" value="Create" />
  </p>
</form>

<form id="createthread" method="POST" action="<?php echo SCRoutes::set("threads", "create", array("boardid"=>$boardid)); ?>">
  <p>
    <label for="thread_create_subject">Subject:</label>
    <input type="text" id="thread_create_subject" name="subject" value="<?php echo $subject; ?>" />
  </p>
  <p>
    <label for="thread_create_text">Message:</label>
    <textarea id="thread_create_text" name="text" rows="5" cols="50"><?php echo $text; ?></textarea>
  </p>
  <p>
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_create" value="Create" />
  </p>
</form>

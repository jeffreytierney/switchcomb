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
    <label for="thread_create_type">Type:</label>
    <select id="thread_create_type" name="type">
      <option value="text">Text</option>
      <option value="image">Image</option>
      <option value="video">Video</option>
      <option value="link">Link</option>
    </select>
  </p>
  <p>
    <label for="thread_create_url">Url:</label>
    <input type="text" id="thread_create_url" name="url" />
  </p>
  <p>
    <label for="thread_create_caption">Caption:</label>
    <input type="text" id="thread_create_caption" name="caption" />
  </p>
  <p>
    <label for="thread_create_upload_image">Upload Image:</label>
    <input id="thread_create_upload_image" type="file" name="uploadmedia" />
    <a href="mailto:<?php echo $board->emailAddress(); ?>"><?php echo $board->emailAddress(); ?></a>
  </p>
  <p>
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_create" value="Create" />
  </p>
</form>

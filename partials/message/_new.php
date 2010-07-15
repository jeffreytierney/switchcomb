<form id="createmessage" method="POST" action="<?php echo SCRoutes::set("messages", "create", array("boardid"=>$boardid,"threadid"=>$threadid)); ?>" enctype="multipart/form-data">
  <p>
    <label for="message_create_subject">Subject:</label>
    <input type="text" id="message_create_subject" name="subject" value="<?php echo $subject; ?>" />
  </p>
  <p>
    <label for="message_create_text">Message:</label>
    <textarea id="message_create_text" name="text" rows="5" cols="50"><?php echo $text; ?></textarea>
  </p>
  <p>
    <label for="message_create_type">Type:</label>
    <select id="message_create_type" name="type">
      <option value="text">Text</option>
      <option value="image">Image</option>
      <option value="video">Video</option>
      <option value="link">Link</option>
    </select>
  </p>
  <p>
    <label for="message_create_url">Url:</label>
    <input type="text" id="message_create_url" name="url" />
  </p>
  <p>
    <label for="message_create_caption">Caption:</label>
    <input type="text" id="message_create_caption" name="caption" />
  </p>
  <p>
    <label for="message_create_upload_image">Upload Image:</label>
    <input id="message_create_upload_image" type="file" name="uploadmedia" />
  </p>
  <p>
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_create" value="Create" />
  </p>
</form>

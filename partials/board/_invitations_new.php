<form id="sendinvites" method="POST" action="<?php echo SCRoutes::set("boards", "invitations_create", array("boardid"=>$boardid)); ?>">
  <p>
    <label for="invite_email_text">Email Addresses: (comma separated)</label><br/>
    <textarea id="invite_email_text" name="invite_list" rows="10" cols="100"><?php echo $invite_list; ?></textarea>
  </p>
  <p>
    <input type="submit" id="btn_send" value="Send" />
  </p>
</form>

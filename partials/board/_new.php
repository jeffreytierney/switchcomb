<form id="createboard" method="POST" action="<?php echo SCRoutes::set("boards", "create"); ?>">
  <p>
    <label for="create_boardname">Board Name:</label>
    <input type="text" id="create_boardname" name="boardname" value="<?php echo $boardname; ?>" />
  </p>
  <p>
    <label for="create_description">Board Description:</label>
    <textarea id="create_description" name="description" rows="5" cols="50"><?php echo $description; ?></textarea>
  </p>
  <p>
    Privacy:<br/>
    <label for="create_public">Public:</label>
    <input type="radio" id="create_public" name="privacy" value="0" <?php if(!$privacy) {echo "checked";} ?> />
    <label for="create_private">Private:</label>
    <input type="radio" id="create_private" name="privacy" value="1" <?php if($privacy) {echo "checked";} ?>/>
  </p>
  <p>
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_create" value="Create" />
  </p>
</form>

<form id="createmessage" class="<?= $type ?>" method="POST" action="<?= $action; ?>" enctype="multipart/form-data">
  <ul id="create_link_types" class="clearfix">
    <?php foreach(array("text"=>"Text", "image"=>"Image", "video"=>"Video", "link"=>"Link") as $new_type=>$new_type_display): ?>
      <li>
        <a class="type_link bgs rnd <?= $new_type . ($new_type==$type ? " active" : ""); ?>" id="create_<?= $new_type; ?>" href="<?= SCRoutes::set($controller, "_new", array_merge($route_params, array("type"=>$new_type))); ?>">
          <?= $new_type_display; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <fieldset class="all">
    <label for="message_create_subject">Subject:</label>
    <input type="text" class="text" id="message_create_subject" name="subject" value="<?= $subject; ?>" />
  </fieldset>
  <fieldset class="all">
    <label for="message_create_text">Message:</label>
    <textarea id="message_create_text" name="text" rows="5" cols="50"><?= $text; ?></textarea>
  </fieldset>
  <fieldset class="i">
    <label for="message_create_upload_image">Upload Image:</label>
    <input id="message_create_upload_image" type="file" class="file" name="uploadmedia" />
    <a class="submit_email" href="mailto:<?= $parent->emailAddress(); ?>"><?= $parent->emailAddress(); ?></a>
    <strong>- or -</strong>
  </fieldset>
  <fieldset class="i l">
    <label for="message_create_url">Url:</label>
    <input type="text" class="text" id="message_create_url" name="url" />
  </fieldset>
  <fieldset class="v">
    <label for="message_create_embed_code">Video Embed Code:</label>
    <textarea id="message_create_embed_code" name="embed_code" rows="5" cols="50"><?= $embed_code; ?></textarea>
  </fieldset>
  <fieldset class="i v l">
    <label for="message_create_caption">Caption:</label>
    <input type="text" class="text" id="message_create_caption" name="caption" />
  </fieldset>
  <fieldset class="clearfix buttons">
    <input type="hidden" name="type" id="message_create_type" value="<?= $type; ?>" />
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" class="button" id="btn_create" value="Create" />
  </fieldset>
</form>

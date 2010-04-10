<form id="acceptinvite" method="POST" action="<?php echo SCRoutes::set("memberships", "create", array("userid"=>$current_user->userid,"boardid"=>$invite->board()->boardid)); ?>">
    <input type="hidden" name="method" value="PUT" />
    <input type="hidden" name="invitecode" value="<?php echo $invite->hash; ?>" />
    <input type="submit" id="btn_join" value="Join" />
  </p>
</form>

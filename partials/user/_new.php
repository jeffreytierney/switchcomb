<form id="register" method="POST" action="<?php echo SCRoutes::set("users", "create"); ?>">
  <h1>Register</h1>
  <p class="clearfix">
    <label for="reg_username">Username</label>
    <input type="text" id="reg_username" name="username" value="<?php echo $username; ?>" />
  </p>
  <p class="clearfix">
    <label for="reg_email">Email</label>
    <input type="text" id="reg_email" name="email"  value="<?php echo $email; ?>"/>
  </p>
  <p class="clearfix">
    <label for="reg_password">Password</label>
    <input type="password" id="reg_password" name="password" />
  </p>
  <p class="clearfix">
    <label for="reg_confirmpassword">Confirm Password</label>
    <input type="password" id="reg_confirmpassword" name="confirmpassword" />
  </p>
  <p class="clearfix">
    <label for="reg_userdispname">Display Name</label>
    <input type="text" id="reg_userdispname" name="displayname"  value="<?php echo $displayname; ?>"/>
  </p>
  <!--
  <p class="clearfix">
    <label for="reg_userfname">First Name</label>
    <input type="text" id="reg_userfname" name="userfname" />
  </p>
  <p class="clearfix">
    <label for="reg_userlname">Last Name</label>
    <input type="text" id="reg_userlname" name="userlname" />
  </p>
  -->
  <p>
    <input type="hidden" name="method" value="PUT" />
    <input type="submit" id="btn_register" value="Register" />
  </p>
</form>

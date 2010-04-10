<form id="login" class="loginreg<?php echo ($current_user ? " hidden" : "") ?>" action="<?php echo SCRoutes::set("usersessions", "create"); ?>" method="POST">
  <h1>Login</h1>
  <p class="clearfix">
    <label for="log_login">Username </label>
    <input type="text" id="log_login" name="login" />
  </p>
  <p class="clearfix">
    <label for="log_password">Password</label>
    <input type="password" id="log_password" name="password" /><br/>
  </p>
  <input type="hidden" name="f" value="login" />
  <p> 	
    <label for="log_remember">Remember Me</label><input type="checkbox" id="log_remember" name="remember" value="true">
  </p>
  <p>
    <input type="submit" id="btn_login" value="Login" />
  </p>
</form>

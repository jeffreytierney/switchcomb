<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
    <meta content='IE=8' http-equiv='X-UA-Compatible' />
    <meta content='text/html; charset=utf-8' http-equiv='content-type' />
		<title><?php echo SC::pageTitle(); ?></title>

		<?php SCPartial::render("shared/head"); ?>

	</head>
	<body>
		<?php SCPartial::render("shared/header"); ?>
    <?php echo $flash_message ?>
		<div id="pagecontainer" class="clearfix">
			<?php if(!$current_user): ?>
					<?php SCPartial::render("usersession/create"); ?>
					<?php SCPartial::render("user/new"); ?>
				</div>
			<?php else: ?>
        <?php if(sizeof($memberships)): ?>
          <?php SCPartial::render("index/logged_in_content", array("memberships"=>$memberships)); ?>
        <?php else: ?>
          <?php SCPartial::render("index/logged_in_nocontent"); ?>
        <?php endif; ?>
			<?php endif; ?>
      <?php SCPartial::render("shared/notifier"); ?>
		</div>
	</body>
</html>

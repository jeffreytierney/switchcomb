<html>
	<head>
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
		</div>
	</body>
</html>

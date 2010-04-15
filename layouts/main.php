<html>
	<head>
		<title><?php SCLayout::yield("title"); ?></title>
		
		<?php SCPartial::render("shared/head"); ?>
    <?php SCLayout::yield("page_head"); ?>
		
	</head>
	<body id="<?php echo $controller; ?>" class="<?php echo $action; ?>">
		<?php SCPartial::render("shared/header"); ?>
    <?php echo $flash_message ?>
		<div id="pagecontainer">
			<h1><?php SCLayout::yield("title"); ?></h1>
			
			<?php SCLayout::yield("util_links"); ?>
      <?php SCLayout::yield("content"); ?>
      <?php SCPartial::render("shared/notifier"); ?>
		</div>
	</body>
</html>

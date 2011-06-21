<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
    <meta content='IE=8' http-equiv='X-UA-Compatible' />
    <meta content='text/html; charset=utf-8' http-equiv='content-type' />
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
	<?php SCPartial::render("shared/js_includes"); ?>
	<?php SCBlock::render("javascript"); ?>
	</body>
</html>

<?
	$bigtree["form"] = $form = BigTreeAutoModule::getEmbedFormByHash($_GET["hash"]);
	$bigtree["form_root"] = ADMIN_ROOT."ajax/auto-modules/embeddable-form/";
?><!doctype html> 
<!--[if lt IE 7 ]> <html lang="en" class="ie ie6"> <![endif]-->
<!--[if IE 7 ]>	<html lang="en" class="ie ie7"> <![endif]-->
<!--[if IE 8 ]>	<html lang="en" class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>	<html lang="en" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
	<head>
		<meta charset="utf-8" />
		<link rel="stylesheet" href="<?=ADMIN_ROOT?>css/main.css" type="text/css" media="screen" />
		<?
			// Configuration based CSS
			if (isset($bigtree["config"]["admin_css"]) && is_array($bigtree["config"]["admin_css"])) {
				foreach ($bigtree["config"]["admin_css"] as $style) {
		?>
		<link rel="stylesheet" href="<?=ADMIN_ROOT?>css/<?=$style?>" type="text/css" media="screen" />
		<?
				}
			}
			// Runtime based CSS
			if (isset($bigtree["css"]) && is_array($bigtree["css"])) {
				foreach ($bigtree["css"] as $style) {
		?>
		<link rel="stylesheet" href="<?=ADMIN_ROOT?>css/<?=$style?>" type="text/css" media="screen" />
		<?
				}
			}
			if ($form["css"]) {
		?>
		<link rel="stylesheet" href="<?=$form["css"]?>" type="text/css" media="screen" />
		<?
			}
		?>
		<script src="<?=ADMIN_ROOT?>js/lib.js"></script>
		<script src="<?=ADMIN_ROOT?>js/main.js"></script>
		<script src="<?=ADMIN_ROOT?>js/jcrop.min.js"></script>
		<script src="<?=ADMIN_ROOT?>js/<?=isset($bigtree["config"]["html_editor"]) ? $bigtree["config"]["html_editor"]["src"] : "tinymce3/tiny_mce.js"?>"></script>
		<?
			// Configuration based JS
			if (isset($bigtree["config"]["admin_js"]) && is_array($bigtree["config"]["admin_js"])) {
				foreach ($bigtree["config"]["admin_js"] as $script) {
		?>
		<script src="<?=ADMIN_ROOT?>js/<?=$script?>"></script>
		<?
				}
			}

			// Runtime based JS
			if (isset($bigtree["js"]) && is_array($bigtree["js"])) {
				foreach ($bigtree["js"] as $script) {
		?>
		<script src="<?=ADMIN_ROOT?>js/<?=$script?>"></script>
		<?
				}
			}
		?>
		<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body class="bigtree embedded">

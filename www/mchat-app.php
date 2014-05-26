<?php
require 'inc/config.php';
require 'inc/mchat-booting.php'
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>fancyflirt</title>
	<!--	<link rel="stylesheet" href="javascripts/libs/jscrollpane/jquery.jscrollpane.css" media="all"/>-->
	<link rel="stylesheet" href="assets/stylesheets/main.css" type="text/css" media="screen"/>
	<script src="<?= SOCKET_URL ?>/socket.io/socket.io.js"></script>
	<script type="text/javascript">
		window.roomid = <?=$roomid ?>
	</script>
	<script src="bower_components/requirejs/require.js" data-main="javascripts/mchat"></script>
</head>
<body>
<div id="chatapp"></div>
</body>
</html>

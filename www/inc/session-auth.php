<?php
	if(isset($_POST['sid'])){
		session_id($_POST['sid']);
		session_start();
		echo json_encode($_SESSION);
	}

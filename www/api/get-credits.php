<?php
	if(isset($_POST['uid'])){
		$r = array('credits'=>120);
		echo json_encode($r);
	}

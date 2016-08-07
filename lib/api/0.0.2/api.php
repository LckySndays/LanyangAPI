<?php

header('Access-Control-Allow-Origin: *');

$action = "none";

if(isset($_POST['action'])){ 
	$action = htmlspecialchars($_POST['action']);
}

if ($action == "validate"){

	require_once('stu-account.php');

	if(!isset($_POST['uid']) || !isset($_POST['pass'])){
		echo "missing parameter";
	}else{
		$response = new StuAccount();
		$response->login($_POST['uid'], $_POST['pass']);
		$response->output();
	}
}

if ($action == "stu-info"){

	require_once('stu-info.php');

	if(!isset($_POST['uid']) || !isset($_POST['pass'])){
		echo "missing parameter";
	}else{
		$response = new StuInfo();
		$response->parse_stu_info($_POST['uid'], $_POST['pass']);
		$response->output();
	}
}

if ($action == "stu-score"){

	require_once('stu-score.php');

	if(!isset($_POST['uid']) || !isset($_POST['pass'])){
		echo "missing parameter";
	}else{
		$response = new StuScore();
		$response->parse_stu_Score($_POST['uid'], $_POST['pass']);
		$response->output();
	}
}

?>

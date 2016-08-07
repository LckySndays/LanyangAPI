<?php

header('Access-Control-Allow-Origin: *');

$action = "error";
$status = "error";
$response = "error";

if(isset($_POST['action'])){ 
	$action = htmlspecialchars($_POST['action']);
}

if ($action == "validate"){
	include("stu-account.php");

	if(!isset($_POST['uid']) || !isset($_POST['pass'])){
		$status = "Fail";
	}else{
		$status = "OK";
		$response =  login($_POST['uid'], $_POST['pass']);
	}
}

if ($action == "stu-info"){
	include("stu-info.php");

	if(!isset($_POST['uid']) || !isset($_POST['pass'])){
		$status = "Fail";
	}else{
		$status = "OK";
		$response =  parse_stu_info($_POST['uid'], $_POST['pass']);
	}
}

$json = array(
	"action"		=> $action,
	"timestamp"		=> date("Y-m-d H:i:s"),
	"status"		=> $status,
	"response"		=> $response
);

$final = json_encode_unicode($json);

echo "<pre>";
echo $final;
echo "</pre>";

function json_encode_unicode($data) {
	if (defined('JSON_UNESCAPED_UNICODE')) {
		return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	}
	return preg_replace_callback('/(?<!\\\\)\\\\u([0-9a-f]{4})/i',
		function ($m) {
			$d = pack("H*", $m[1]);
			$r = mb_convert_encoding($d, "UTF8", "UTF-16BE");
			return $r!=="?" && $r!=="" ? $r : $m[0];
		}, json_encode($data)
	);
}

?>

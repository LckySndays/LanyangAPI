<?php
	require_once('class.php');
	$filename 		= "stu-account.php";
	$version		= "0.0.2";
	$dependencies	= "none";
	$description	= "validate student account credential";

	class StuAccount extends LanyangAPI{

		function __construct(){
			parent::setFilename($GLOBALS['filename']);
			parent::setVersion($GLOBALS['version']);
			parent::setDependencies($GLOBALS['dependencies']);
			parent::setDescription($GLOBALS['description']);
		}

		function login($username, $password){

			$status = "";
		
			$curl           = curl_init();
			$url            = "portal.tku.edu.tw/NEAI/login2.do?action=EAI&myurl=http://portal.tku.edu.tw/aissinfo/emis/tmw0012.aspx&ln=en_US&username=$username&password=$password&loginbtn=Login";
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_COOKIEJAR, 1);
			
			$curlResult = curl_exec($curl);
			curl_close($curl);
			
			if (strpos($curlResult, 'Tamkang University Single Sign On(SSO)') !== false) {
				$status = "FAILED";
				parent::setIsLogin("FALSE");
			}else{
				$status = "OK";
				parent::setIsLogin("TRUE");
			}

			$response = array(
				"username" 				=> $username, 
				"status"				=> $status
			);
			
			return parent::setResponse($response);
		}

	}
	
		
?>

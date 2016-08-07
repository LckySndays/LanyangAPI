<?php
	$filename 		= "stu-account.php";
	$version		= "0.0.1";
	$dependencies	= "none";
	$description	= "validate student account credential";

	function login($username, $password){
		
		$curl           = curl_init();
		$url            = "portal.tku.edu.tw/NEAI/login2.do?action=EAI&myurl=http://portal.tku.edu.tw/aissinfo/emis/tmw0012.aspx&ln=en_US&username=$username&password=$password&loginbtn=Login";
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_COOKIEJAR, 1);
		
		$curlResult = curl_exec($curl);
		curl_close($curl);
		
		$status = "OK";

		if (strpos($curlResult, 'Tamkang University Single Sign On(SSO)') !== false) {
			$status = "FAILED";
		}

		$array = array(
			"information"	=> array(	
				"filename" 				=> $GLOBALS['filename'], 
				"version" 				=> $GLOBALS['version'],
				"dependencies" 			=> $GLOBALS['dependencies'],
				"description" 			=> $GLOBALS['description']
				),
			"result"		=> array(
				"username" 				=> $username, 
				"status"				=> $status
			)
		);
		
		return $array;
	}
		
?>

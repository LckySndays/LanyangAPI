<?php 
	require_once('stu-account.php');
	$filename 		= "stu-info.php";
	$version		= "0.0.1";
	$dependencies	= "stu-account.php";
	$description	= "print out student basic information data";

	function parse_stu_info($username, $password){
		
		$validate = login($username, $password);

		if($validate['result']['status'] == "OK"){

			$curl           = curl_init();
			$url            = "http://portal.tku.edu.tw/aissinfo/emis/TMWS020.aspx";
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_COOKIEFILE, 1);
			$curlResult2 = curl_exec($curl);
			curl_close($curl);
			
			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML('<?xml encoding="UTF-8">' . $curlResult2);
			
			$counter = 1;
			
			foreach($dom->getElementsByTagName('td') as $link) {
					
				switch ($counter) {
					case 2:
						$stu_dept = $link->textContent;
						break;
					case 4:
						$stu_ID = $link->textContent;
						break;
					case 6:
						$stu_nameCH = $link->textContent;
						break;
					case 8:
						$stu_nameEN = $link->textContent;
						break;
					case 10:
						$stu_birthday = $link->textContent;
						break;
					case 12:
						$stu_gender = $link->textContent;
						break;
					case 14:
						$stu_ID_card = $link->textContent;
						break;
					case 16:
						$stu_ARC = $link->textContent;
						break;
					case 18:
						$stu_status_1 = $link->textContent;
						break;
					case 20:
						$stu_status_2 = $link->textContent;
						break;
					case 22:
						$stu_telp = $link->textContent;
						break;
					case 23:
						$stu_telp = $stu_telp . $link->textContent;
						break;
					case 25:
						$stu_phone = $link->textContent;
						break;
					case 27:
						$stu_addr_1 = $link->textContent;
						break;
					case 29:
						$stu_addr_2 = $link->textContent;
						break;
					case 31:
						$stu_addr_3 = $link->textContent;
						break;
					case 33:
						$stu_send_opt = $link->textContent;
						break;
					case 35:
						$stu_school_email = $link->textContent;
						break;
					case 37:
						$stu_guardian_name = $link->textContent;
						break;
					case 39:
						$stu_guardian_telp = $link->textContent;
						break;
					case 40:
						$stu_guardian_telp = $stu_guardian_telp . $link->textContent;
						break;
					case 42:
						$stu_guardian_phone = $link->textContent;
						break;
					case 43:
						$stu_guardian_phone = $stu_guardian_phone . $link->textContent;
						break;
					default:
						break;
				}
				
				$counter += 1;  
			}
			
			$array = array(
				"information"	=> array(	
					"filename" 				=> $GLOBALS['filename'], 
					"version" 				=> $GLOBALS['version'],
					"dependencies" 			=> $GLOBALS['dependencies'],
					"description" 			=> $GLOBALS['description']
					),
				"result"		=> array(
					"stu_dept"				=> $stu_dept,
					"stu_ID" 				=> $stu_ID,
					"stu_nameCH"			=> $stu_nameCH,
					"stu_nameEN"			=> $stu_nameEN,
					"stu_birthday"			=> $stu_birthday,
					"stu_gender"			=> $stu_gender,
					"stu_ID_card"			=> $stu_ID_card,
					"stu_ARC"				=> $stu_ARC,
					"stu_status_1"			=> $stu_status_1,
					"stu_status_2"			=> $stu_status_2,
					"stu_telp"				=> $stu_telp,
					"stu_phone"				=> $stu_phone,
					"stu_addr_1"			=> $stu_addr_1,
					"stu_addr_2"			=> $stu_addr_2,
					"stu_addr_3"			=> $stu_addr_3,
					"stu_send_opt"			=> $stu_send_opt,
					"stu_school_email"		=> $stu_school_email,
					"stu_guardian_name"		=> $stu_guardian_name,
					"stu_guardian_telp"		=> $stu_guardian_telp,
					"stu_guardian_phone"	=> $stu_guardian_phone
				)
			);
		
		}else{
			$array = array(
				"information"	=> array(	
					"filename" 				=> $GLOBALS['filename'], 
					"version" 				=> $GLOBALS['version'],
					"dependencies" 			=> $GLOBALS['dependencies'],
					"description" 			=> $GLOBALS['description']
					),
				"result"		=> array(
					"error"					=> "Not Validated"
				)
			);      
		}

		return $array;
	}

?>

<?php 
	
	//Configuration
	$username = "student_id";
	$password = "student_password";
	
	$curl           = curl_init();
	$url            = "portal.tku.edu.tw/NEAI/login2.do?action=EAI&myurl=http://portal.tku.edu.tw/aissinfo/emis/tmw0012.aspx&ln=en_US&username=$username&password=$password&loginbtn=Login";
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_COOKIEJAR, 1);
	
	$curlResult = curl_exec($curl);
	curl_close($curl);
	
	if (strpos($curlResult, 'Tamkang University Single Sign On(SSO)') !== false) {
		exit("Error: Login Failed");
	}
	
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
	
	/*
	目前就讀系級	= stu_dept	學生連絡電話	= stu_telp
	學號		= stu_ID	學生行動電話	= stu_phone
	中文姓名		= stu_nameCH	戶籍住址		= stu_addr_1
	英文姓名		= stu_nameEN	通訊住址		= stu_addr_2
	出生年月日	= stu_birthday	國外住址		= stu_addr_3
	性別		= stu_gender	寄件選擇		= stu_send_opt
	身分證字號	= stu_ID_card	校級電子信箱	= stu_school_email
	居留證統一證號	= stu_ARC	監護人		= stu_guardian_name
	入學身分		= stu_status_1	監護人電話(宅)	= stu_guardian_telp
	入學方式		= stu_status_2	監護人電話(公)	= stu_guardian_phone
	*/
	
	$array = array(
		"stu_dept"		=> $stu_dept,
		"stu_ID" 		=> $stu_ID,
		"stu_nameCH"		=> $stu_nameCH,
		"stu_nameEN"		=> $stu_nameEN,
		"stu_birthday"		=> $stu_birthday,
		"stu_gender"		=> $stu_gender,
		"stu_ID_card"		=> $stu_ID_card,
		"stu_ARC"		=> $stu_ARC,
		"stu_status_1"		=> $stu_status_1,
		"stu_status_2"		=> $stu_status_2,
		"stu_telp"		=> $stu_telp,
		"stu_phone"		=> $stu_phone,
		"stu_addr_1"		=> $stu_addr_1,
		"stu_addr_2"		=> $stu_addr_2,
		"stu_addr_3"		=> $stu_addr_3,
		"stu_send_opt"		=> $stu_send_opt,
		"stu_school_email"	=> $stu_school_email,
		"stu_guardian_name"	=> $stu_guardian_name,
		"stu_guardian_telp"	=> $stu_guardian_telp,
		"stu_guardian_phone"	=> $stu_guardian_phone
	);      
	
	echo "stu_dept = "			. $array['stu_dept']		. "<br>";
	echo "School ID = "			. $array['stu_ID']		. "<br>";
	echo "Chinese Name = "			. $array['stu_nameCH']		. "<br>";
	echo "English Name = "			. $array['stu_nameEN']		. "<br>";
	echo "stu_birthday = "			. $array['stu_birthday'] 	. "<br>";
	echo "stu_gender = "			. $array['stu_gender']		. "<br>";
	echo "Identity card = "			. $array['stu_ID_card']		. "<br>";
	echo "stu_ARC = "			. $array['stu_ARC']		. "<br>";
	echo "Status 1 = "			. $array['stu_status_1'] 	. "<br>";
	echo "Status 2 = "			. $array['stu_status_2'] 	. "<br>";
	echo "Student Telp = "			. $array['stu_telp']		. "<br>";
	echo "Student Phone = "			. $array['stu_phone']		. "<br>";
	echo "Address 1 = "			. $array['stu_addr_1']		. "<br>";
	echo "Address 2 = "			. $array['stu_addr_2']		. "<br>";
	echo "Address 3 = "			. $array['stu_addr_3']		. "<br>";
	echo "Send Opt = "			. $array['stu_send_opt'] 	. "<br>";
	echo "School Email = "			. $array['stu_school_email']	. "<br>";
	echo "stu_guardian_name = "		. $array['stu_guardian_name']	. "<br>";
	echo "stu_guardian_name Telp = " 	. $array['stu_guardian_telp']	. "<br>";
	echo "stu_guardian_name Phone = "	. $array['stu_guardian_phone']	. "<br>";
	
?>

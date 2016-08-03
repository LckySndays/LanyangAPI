<?php 

	//Configuration
	$username = "student_id";
	$password = "student_pass";
	
	$header1_text  		= "學年";			$header2_text  		= "系級";
	$header1_value 		= 10;				$header2_value 		= 13;
	$footer1_text  		= "學業總平均";		$footer2_text  		= "必修學分";
	$footer1_value 		= 1;				$footer2_value 		= 1;
	$totalColumn1_value	= 10;				$totalColumn2_value	= 9;
	
	
	//Login to the TKU Scoring System to Retrieve the Cookies/Session ID
	$curl 		= curl_init();
	$url		= "portal.tku.edu.tw/NEAI/login2.do?action=EAI&myurl=http://portal.tku.edu.tw/aissinfo/emis/tmw0012.aspx&ln=en_US&username=$username&password=$password&loginbtn=Login";
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_COOKIEJAR, 1);
	// curl_setopt($curl, CURLOPT_COOKIEJAR, "tmp/test.txt");
	
	$curlResult = curl_exec($curl);
	curl_close($curl);
	
	if (strpos($curlResult, 'Tamkang University Single Sign On(SSO)') !== false) {
		exit("Error: Login Failed");
	}
	
	
	//Retrieve the Student Basic Information
	$curl 		= curl_init();
	$url		= "http://portal.tku.edu.tw/aissinfo/emis/TMWS030.aspx";
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_COOKIEFILE, 1);
	
	$curlResult = curl_exec($curl);
	curl_close($curl);
	
	/* Bug Fix 			: Remove the default page encoding (big5) and let the DOM Parser use UTF-8 instead */
	/* Bug Description 	: The DOM Parser fail to parse the data after encounter Chinese character, leaving the rest empty */
	$curlResult = preg_replace ('/<meta http-equiv="Content-Type" content="text\/html; charset=big5" \/>/i', '', $curlResult);
	
	/* Let the DOM Parser handle the page result from Curl */
	/* Force DOM Parser to add UTF-8 encoding to the page for the Chinese character to load properly */
	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML('<?xml encoding="UTF-8">' . $curlResult);

	/* Try to get total row of data by parsing the number of (共 ? 筆資料) */
	/* Only Parse the first encounter (共) word and hoping it is the right data to parse */
	/* Not reliable and may fail any time. Example : if 全發院[共]同科 appeared before [共] ? 筆資料 */
	/* Alternative : Count the (明細) word inside the table [for this page only] */
	$totalRow = 0;
	foreach($dom->getElementsByTagName('td') as $link) {	 
		if(strpos($link->textContent, '共') !== false){
			$totalRow = intval(preg_replace('/[^0-9]+/', '', $link->textContent), 10);
			break;
		}
	}
	
	/* headerData and footerData value depend on what data actually been shown there on the original page [Not-Reliable] */
	
	$headerData 	= 10;	// The total of unnecessary data on header before starting to parse the first table(ex: table title)
	$footerData 	= 2;	// The total of unnecessary data on footer after parsing first table before parsing second table
	$totalColumn 	= 10;	// Total column of the table one
	
	$currentData 	= 0;	// The current position of data parsed 
	$dataCounting 	= 0;	// Counting the total of data has been parsed inside the table
	$currentColumn 	= 0;	// Current parsing column
	$index 			= 0;	// Index of data array
	
	/* currentData is counting the total of all data parsed while the dataCounting only counting data parsed inside the table only */
	
	//Overwrite the default value
	$totalColumn	= $totalColumn1_value;
	
	foreach($dom->getElementsByTagName('td') as $link) {
		
		// Re-sync the parsing data position to keep the data on-track
		if ($link->textContent == $header1_text){$headerData = $header1_value;}
			
		if ($currentData <= $headerData){
			// We do not parse anything that are unnecessary on header
			
		}else if ($dataCounting < $totalRow*$totalColumn){
			// We start to parse the data inside the first table if current data counting still on the range of total data inside (row x column)
			
			// If we finish one row, process to the next row
			if($currentColumn >= $totalColumn){
				$currentColumn = 0;
				$index += 1;
			}
			
			// Parse the data by the corresponding column
			switch ($currentColumn) {
				case 0:
					$academicYear[$index] = $link->textContent;
					break;
				case 1:
					$semester[$index] = $link->textContent;
					break;
				case 2:
					$department[$index] = $link->textContent;
					//empty function and value == "" are not working here
					//due to there is invincible character (not white space)
					if(strlen($department[$index]) == 2) {
						$department[$index] = $department[$index-1];
					}
					break;
				case 3:
					$scoreBehavior[$index] = $link->textContent;
					break;
				case 4:
					$creditTotalSemester[$index] = $link->textContent;
					break;
				case 5:
					$creditAcquiredSemester[$index] = $link->textContent;
					break;
				case 6:
					$creditAcquiredTotal[$index] = $link->textContent;
					break;
				case 7:
					$scoreAvgSemester[$index] = $link->textContent;
					break;
				case 8:
					$learningStatus[$index] = $link->textContent;
					break;
				default:
					break;
			}
			
			// Process to the next column and also increment the data counting
			$currentColumn 	+= 1;
			$dataCounting 	+= 1;
			
		}else{
		
			// Re-sync the parsing data position to keep the data on-track
			// for minimizing the chance of error occurred
			if ($link->textContent == $footer1_text){$footerData = $footer1_value;}
			
			// We only parse after the we skip several unnecessary footerData
			if($footerData > 4){
				switch ($footerData) {
					case 5:
						$scoreAvgOverall = $link->textContent;
						break;
					case 6:
						$classRankOverall = $link->textContent;
						break;
					case 7:
						$deptRankOverall = $link->textContent;
						break;
					case 8:
						$gpa = $link->textContent;
						break;
					default:
						break;
				}
			}
			$footerData += 1;
		}
		
		// Incrementing the current data parsed
		$currentData += 1;
	}
	
	// Parse link of the detailed information
	$currentData = 0;
	$index = 0;
	foreach($dom->getElementsByTagName('a') as $link) {
		if ($currentData < $totalRow){
			$urlLink[$index] = $link->getAttribute('href');
			$currentData += 1;
			$index += 1;
		}
	}
	
	$array = array(
		"scoreAvgOverall" 			=> $scoreAvgOverall,
		"classRankOverall"			=> $classRankOverall,
		"deptRankOverall" 			=> $deptRankOverall,
		"gpa" 						=> $gpa,
		"academicYear" 				=> $academicYear,
		"semester"					=> $semester,
		"department"				=> $department,
		"scoreBehavior"				=> $scoreBehavior,
		"creditTotalSemester"		=> $creditTotalSemester,
		"creditAcquiredSemester"	=> $creditAcquiredSemester,
		"creditAcquiredTotal"		=> $creditAcquiredTotal,
		"scoreAvgSemester"			=> $scoreAvgSemester,
		"learningStatus"			=> $learningStatus,
		"urlLink"					=> $urlLink,
		"totalData"					=> $totalRow
	);	
	
	//Start to parsing each of the detailed data
	$totalData = $totalRow;
	
	for($c2 = 0; $c2<$totalData; $c2++){
		
		$curl 		= curl_init();
		$url		= "http://portal.tku.edu.tw/aissinfo/emis/" . $urlLink[$c2];
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_COOKIEFILE, 1);
		$curlResult = curl_exec($curl);
		curl_close($curl);

		$curlResult = preg_replace ('/<meta http-equiv="Content-Type" content="text\/html; charset=big5" \/>/i', '', $curlResult);

		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML('<?xml encoding="UTF-8">' . $curlResult);

		$totalRow = 0;
		foreach($dom->getElementsByTagName('td') as $link) {	 
			if(strpos($link->textContent, '共') !== false){
				$totalRow = intval(preg_replace('/[^0-9]+/', '', $link->textContent), 10);
				break;
			}
		}
		
		$headerData 	= 13;	// The total of unnecessary data on header before starting to parse the first table(ex: table title)
		$footerData 	= 2;	// The total of unnecessary data on footer after parsing first table before parsing second table
		$totalColumn 	= 9;	// Total column of the table one
		
		$currentData 	= 0;	// The current position of data parsed 
		$dataCounting 	= 0;	// Counting the total of data has been parsed inside the table
		$currentColumn 	= 0;	// Current parsing column
		$index 			= 0;	// Index of data array
		
		$totalColumn 	= $totalColumn2_value;
		
		foreach($dom->getElementsByTagName('td') as $link) {
			
			// Re-sync the parsing data position to keep the data on-track
			if ($link->textContent == $header2_text){$headerData = $header2_value;}
		
			if ($currentData <= $headerData){
				//do nothing
			}else if($dataCounting < $totalRow*$totalColumn){
				if($currentColumn >= $totalColumn){
					$currentColumn = 0;
					$index += 1;
				}
				switch ($currentColumn) {
					case 0:
						$courseDept[$index] = $link->textContent;
						break;
					case 1:
						$courseName[$index] = $link->textContent;
						break;
					case 2:
						$coursePeriod[$index] = $link->textContent;
						break;
					case 3:
						$courseClass[$index] = $link->textContent;
						break;
					case 4:
						$courseGroup[$index] = $link->textContent;
						break;
					case 5:
						$courseElectOpt[$index] = $link->textContent;
						break;
					case 6:
						$courseCredit[$index] = $link->textContent;
						break;
					case 7:
						$courseScore[$index] = $link->textContent;
						break;
					case 8:
						$scoreDescription[$index] = $link->textContent;
						break;
					default:
						break;
				}
				$currentColumn += 1;
				$dataCounting += 1;
			}else{
				if ($link->textContent == $footer2_text){
					$footerData = $footer2_value;
				}
				if($footerData > 6){
					switch ($footerData) {
						case 7:
							$creditTotalElective = $link->textContent;
							break;
						case 8:
							$creditTotalOptional = $link->textContent;
							break;
						case 9:
							$creditTotalSemester = $link->textContent;
							break;
						case 10:
							$creditAcquiredSemester = $link->textContent;
							break;
						case 11:
							$scoreAvgSemester = $link->textContent;
							break;
						case 12:
							$classRank = $link->textContent;
							break;
						default:
							break;
					}
				}
				
				$footerData += 1;
			}
			
			$currentData += 1;
		}
		
		$array_name = "data" . $c2; 
		$$array_name = array(
			"creditTotalElective" 		=> $creditTotalElective,
			"creditTotalOptional"		=> $creditTotalOptional,
			"creditTotalSemester" 		=> $creditTotalSemester,
			"creditAcquiredSemester" 	=> $creditAcquiredSemester,
			"scoreAvgSemester" 			=> $scoreAvgSemester,
			"classRank"					=> $classRank,
			"courseDept"				=> $courseDept,
			"courseName"				=> $courseName,
			"coursePeriod"				=> $coursePeriod,
			"courseClass"				=> $courseClass,
			"courseGroup"				=> $courseGroup,
			"courseElectOpt"			=> $courseElectOpt,
			"courseCredit"				=> $courseCredit,
			"courseScore"				=> $courseScore,
			"scoreDescription"			=> $scoreDescription
		);
		
		//Append this array to the main array
		$array["$array_name"] = ${$array_name};
	}
	
	
	echo "<pre>";
	print_r($array);
	echo "</pre>";
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////							Example to retrieve the data from Array								  /////			
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/*
	學年 	= $academicYear				實得學分 		= $creditAcquiredSemester	學業總平均		= $scoreAvgOverall
	學期		= $semester					累計實得		= $creditAcquiredTotal		班級總排名		= $classRankOverall
	所系年班	= $department				學期學業平均	= $scoreAvgSemester			系組總排名		= $deptRankOverall
	操行成績	= $scoreBehavior			修業狀況		= $learningStatus			GPA			= $gpa
	總修學分	= $creditTotalSemester		各科成績		= $dataLink or $urlLink
	*/
	
	/*
	系級		= $courseDept				必修學分		= $creditTotalElective
	科目名稱	= $courseName				選修學分		= $creditTotalOptional
	學期序	= $coursePeriod				總修學分		= $creditTotalSemester
	班別		= $courseClass				實得學分		= $creditAcquiredSemester
	群別		= $courseGroup				學期學業平均	= $scoreAvgSemester
	選必修	= $courseElectOpt			班排名		= $classRank
	學分		= $courseCredit
	成績		= $courseScore
	成績狀況	= $scoreDescription
	*/
	
	/*
	echo "<h1 style='text-align: center'>Overview</h1>";
	
	echo "Overall Score Average = " 		. $array["scoreAvgOverall"] 	. "<br>"; 
	echo "Overall Class Ranking = " 		. $array["classRankOverall"] 	. "<br>";
	echo "Overall Department Ranking = " 	. $array["deptRankOverall"]		. "<br>";
	echo "GPA = " 							. $array["gpa"] 				. "<br>";
	
	for($c = 0; $c<$array["totalData"]; $c++){
		echo "</br>";
		echo "Year => " 				. $array["academicYear"]["$c"] 				. "<br>";
		echo "Semester => "				. $array["semester"]["$c"] 					. "<br>";
		echo "Department => " 			. $array["department"]["$c"] 				. "<br>";
		echo "Behavior Score => " 		. $array["scoreBehavior"]["$c"] 			. "<br>";
		echo "Credit Total => "			. $array["creditTotalSemester"]["$c"] 		. "<br>";
		echo "Credit Acquired => " 		. $array["creditAcquiredSemester"]["$c"]	. "<br>";
		echo "Credit Accumulated => " 	. $array["creditAcquiredTotal"]["$c"] 		. "<br>";
		echo "Score Average => " 		. $array["scoreAvgSemester"]["$c"] 			. "<br>";
		echo "Learning Status => " 		. $array["learningStatus"]["$c"] 			. "<br>";
		echo "Detail Link => " 			. $array["urlLink"]["$c"] 					. "<br>";
		echo "</br>";
	}
	
	for($c2 = 0; $c2<$array["totalData"]; $c2++){
	
		$array_name = "data" . $c2; 
		
		echo "<h1 style='text-align: center'>Data</h1>";
		
		echo "Elective Credit = " 			. $array["$array_name"]["creditTotalElective"]		. "<br>";
		echo "Optional Credit = " 			. $array["$array_name"]["creditTotalOptional"]		. "<br>";
		echo "Total Credit = " 				. $array["$array_name"]["creditTotalSemester"] 		. "<br>";
		echo "Credit Acquired = " 			. $array["$array_name"]["creditAcquiredSemester"] 	. "<br>";
		echo "Score Average = " 			. $array["$array_name"]["scoreAvgSemester"] 		. "<br>";
		echo "Ranking = "					. $array["$array_name"]["classRank"] 				. "<br>";
		
		for($c = 0; $c<$totalRow; $c++){
			echo "<br>";
			echo "Department = " 		. $array["$array_name"]["courseDept"]["$c"] 		. "<br>";
			echo "Course Name = " 		. $array["$array_name"]["courseName"]["$c"] 		. "<br>";
			echo "Course Period = " 	. $array["$array_name"]["coursePeriod"]["$c"] 		. "<br>";
			echo "Class = " 			. $array["$array_name"]["courseClass"]["$c"] 		. "<br>";
			echo "Course Group = " 		. $array["$array_name"]["courseGroup"]["$c"] 		. "<br>";
			echo "Elective/Optional = " . $array["$array_name"]["courseElectOpt"]["$c"]		. "<br>";
			echo "Credit = " 			. $array["$array_name"]["courseCredit"]["$c"] 		. "<br>";
			echo "Score = " 			. $array["$array_name"]["courseScore"]["$c"] 		. "<br>";
			echo "Description = "		. $array["$array_name"]["scoreDescription"]["$c"] 	. "<br>";
			echo "<br>";
		}
	}
	*/
?>
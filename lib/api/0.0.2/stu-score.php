<?php 
	require_once('class.php');
	require_once('stu-account.php');

	$filename 		= "stu-score.php";
	$version		= "0.0.2";
	$dependencies	= "stu-account.php";
	$description	= "print out student score data";

	class StuScore extends LanyangAPI{

		function __construct(){
			parent::setFilename($GLOBALS['filename']);
			parent::setVersion($GLOBALS['version']);
			parent::setDependencies($GLOBALS['dependencies']);
			parent::setDescription($GLOBALS['description']);
		}

		function parse_stu_score($username, $password){
			
			$header1_text  		= "學年";			$header2_text  		= "系級";
			$header1_value 		= 10;				$header2_value 		= 13;
			$footer1_text  		= "學業總平均";		$footer2_text  		= "必修學分";
			$footer1_value 		= 1;				$footer2_value 		= 1;
			$totalColumn1_value	= 10;				$totalColumn2_value	= 9;

			$validate = new StuAccount();
			$validate->login($username, $password);
			$validate = $validate->getResponse();

			if($validate['status'] == "OK"){

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
							$courseTotal = $totalRow;
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
						"courseTotal"				=> $courseTotal,
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

				

			}else{
				//Fail
			}
			
			return parent::setResponse($array);
			
		}
	}
			
?>
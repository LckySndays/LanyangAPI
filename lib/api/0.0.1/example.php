<?php
	
	// $curl           = curl_init();
	// $url            = "http://localhost/github-local/LanyangAPI/lib/api/0.0.1/lanyang-api.php?action=stu-info&uid=403850398&pass=019894";
	// curl_setopt($curl, CURLOPT_URL, $url);
	// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	// curl_setopt($curl, CURLOPT_COOKIEJAR, 1);
	
	// $curlResult = curl_exec($curl);
	// curl_close($curl);

	// echo "<pre>";
	// echo $curlResult;
	// echo "</pre>";
	
	echo '

	<html>
	<body>

	<form action="https://lanyang-api.herokuapp.com/" method="post">
	
	Action:
	<input type="radio" name="action" value="validate" checked> validate
	<input type="radio" name="action" value="stu-info"> stu-info
	<br>
	Student ID: <input type="text" name="uid"><br>
	Student Password: <input type="password" name="pass"><br>
	<input type="submit">
	</form>

	</body>
	</html>

	';

?>

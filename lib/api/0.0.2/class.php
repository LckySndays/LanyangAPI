<?php

class LanyangAPI{

	private $filename 		= "";
	private $version		= "";
	private $dependencies	= "";
	private $description	= "";
	private $response		= "";

	private $isLogin		= "FALSE";


	public function getFilename(){
		return $this->filename;
	}

	public function getVersion(){
		return $this->version;
	}

	public function getDependencies(){
		return $this->dependencies;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getIsLogin(){
		return $this->isLogin;
	}

	public function getResponse(){
		return $this->response;
	}

	public function setFilename($value){
		$this->filename = $value;
	}

	public function setVersion($value){
		$this->version = $value;
	}

	public function setDependencies($value){
		$this->dependencies = $value;
	}

	public function setDescription($value){
		$this->description = $value;
	}

	public function setIsLogin($value){
		$this->isLogin = $value;
	}

	public function setResponse($value){
		$this->response = $value;
	}

	public function output(){
		$json = array(
			"information"	=> array(	
				"filename" 				=> $this->filename, 
				"version" 				=> $this->version,
				"dependencies" 			=> $this->dependencies,
				"description" 			=> $this->description
				),
			"response"					=> $this->response
		);
		
		echo "<pre>";
		echo $this->json_encode_unicode($json);
		echo "</pre>";
	}

	public function json_encode_unicode($data) {
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

}




?>
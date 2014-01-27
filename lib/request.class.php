<?php
namespace Barebones\Lib;
class Request{

	public static $data = array();
	public static $ahis;
	public static $rt_socket;
	public static $request_uri;
	public static $uri_segments;
	public static $isValid = false;
	public static $version;
	public static $module;
	public static $requestMethod;
	public static $url_variables = array(
		":id"=>"[0-9]{1,}",
		":alphanumeric"=>"[a-zA-Z0-9]{1,}",
		":alpha"=>"[a-zA-Z]{1,}"
	);
	private static $instance;

	private function __construct(){
		self::setRequestURI();
		self::setRequestData();
		self::validate();
	}


	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new Request();
		}
		return self::$instance;
	}

	private static function validate(){
		/*if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'){
			self::sendError("No SSL connection detected. Please use HTTPS.");
		}else if(!isset(self::$ahis)){
			self::sendError("Session Info missing");
		}else{
			self::$isValid = true;
		}*/
		self::$isValid = true;
	}



	private static function setRequestData(){

		self::$requestMethod = $_SERVER['REQUEST_METHOD'];
		
		if(array_search($_SERVER['REQUEST_METHOD'], array(0=>'GET', 1=>'POST', 2=>'PUT', 3=>'DELETE')) < 0)
			return self::sendError("Request Type Info Missing" . php_uname('n'));
		switch($_SERVER['REQUEST_METHOD']){

			case "GET":
				self::$data = $_GET;
				//self::$data['id'] = end(self::$uri_segments);
				// self::$ahis = self::$data['ahis'];
				break;
			case "POST":
				//self::$data = json_decode(file_get_contents("php://input"), true);
				self::$data = $_POST;
				//self::$data['id'] = (int)end(self::$uri_segments);
				// self::$ahis = self::$data['ahis'];

				break;
			case "PUT":
				// var_dump(json_decode(file_get_contents("php://input"), true));
				self::$data = json_decode(file_get_contents("php://input"), true);
				// self::$ahis = self::$data['ahis'];
				break;
			case "DELETE":
				self::$data = $_GET; // Need to see where the data for a DELETE comes from
				// self::$ahis = self::$data['ahis'];
				break;
		}
		/*self::$ahis = self::$data['ahis'];
		if(isset(self::$data['rt_socket']))
			self::$rt_socket = self::$data['rt_socket'];*/
	}

	public static function getUrlData($path){
		$path = str_replace(array('@^','$@'),"",$path);
		$path_segments = explode("/",$path);
		foreach($path_segments as $i => $variable){
			if(substr($variable,0,1) == ":" ){
				self::$data[substr($variable,1)] = self::$uri_segments[$i];
			}
		}
		//die("Got it! <br />".$path."<br />".self::$request_uri."<br />".var_dump(self::$data));
	}

	private static function setRequestURI(){
		//If system directory is nested, remove nesting
		if( strpos($_SERVER['REQUEST_URI'],"/".SYSDIR."/") !== false )
			$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],"/".SYSDIR."/"));
		//Remove System Directory from the request uri
		if( substr($_SERVER['REQUEST_URI'],0,( strlen(SYSDIR) + 2) ) == "/".SYSDIR."/" )
			self::$request_uri = substr($_SERVER['REQUEST_URI'],( strlen(SYSDIR) + 2) );

		self::$request_uri = current(explode("?",self::$request_uri));
		self::$uri_segments = explode("/",self::$request_uri);
		self::$version = self::$uri_segments[0];
		self::$module = self::$uri_segments[1];
		array_shift(self::$uri_segments);
		self::$request_uri = implode("/", self::$uri_segments);
	}

	private static function sendError ($message){
		Response::setBody(array("success" =>"0", "error"=>$message));
		Response::send_403();
	}

}


?>

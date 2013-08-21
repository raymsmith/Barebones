<?php
namespace BarebonesPHP;
class Response{
	protected static $body = array();
    protected static $sendHeaders = true;
    protected static $headers = array();
    public static function displayHeaders($bool){
        Response::$sendHeaders = $bool;
    }
	public static function setBody($args){
        Response::setHeaders('Content-type','Content-type: application/json',true);
		Response::$body = json_encode($args);
	}
	public static function getBody(){
		return Response::$body;
	}
    private static function setHeaders($key,$header,$overwrite=true,$respCode=""){
		Response::$headers[$key] = array($header,$overwrite,$respCode);
    }
	public static function getHeaders(){
		return Response::$headers;
	}
    private static function send(){
        if( Response::$sendHeaders ){
			foreach(Response::$headers as $header)
			{
				if( $header[2] == "" )
					header($header[0],$header[1]);
				else
					header($header[0],$header[1],$header[2]);
			}
			die(Response::$body);
        }
	}
	public static function send_200(){
		Response::setHeaders('Response-Code','HTTP/1.1 200 Success', true, 200);
		Response::send();
	}
	public static function send_400(){
		Response::setHeaders('Response-Code',"HTTP/1.1 400 Bad Request",true, 400);
		Response::send();
	}
	public static function send_403(){
		Response::setHeaders('Response-Code','HTTP/1.1 403 Forbidden', true, 403);
		Response::send();
	}
	public static function send_404(){
		Response::setHeaders('Response-Code','HTTP/1.1 404 Not Found', true, 404);
		Response::send();
	}
	public static function send_405(){
		Response::setHeaders('Response-Code','HTTP/1.1 405 Method Not Allowed', true, 405);
		Response::send();
	}
	public static function send_500(){
		Response::setHeaders('Response-Code','HTTP/1.1 500 Internal Server Error', true, 500);
		Response::send();
	}

}
?>

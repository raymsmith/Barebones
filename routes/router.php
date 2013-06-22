<?php
require_once(LIBPATH."request.class.php");

$request = Request::getInstance();

if( !file_exists(BASEPATH.SYSDIR."/routes/".$request::$version."/".$request::$module.".php") ){
	Response::setBody(array("success"=>"0","error"=>"Invalid Module ".php_uname('n')." ".BASEPATH.'routes/'.$request::$version.'/'.$request::$module.".php"));
	Response::send_400();
}
else
	require_once(BASEPATH.SYSDIR."/routes/".$request::$version."/".$request::$module.".php");


if( !isset($routes[$request::$requestMethod]) ){
	Response::setBody(array("success"=>"0","error"=>"Invalid Request ".php_uname('n')." ".$request::$request_uri));
	Response::send_400();
}
$routes_data = $routes[$request::$requestMethod];
// Order Matters!
$route_variables = array(
	":id"=>"[0-9]{1,}",
	":alphanumeric"=>"[a-zA-Z0-9]{1,}",
	":alpha"=>"[a-zA-Z]{1,}"
);

$valid = false;
$active_controller = "";
foreach($routes_data as $route){
	$potential = $route['pattern'];
	if( preg_match_all('/:[a-zA-Z]{2,}/',$potential,$matches) ){
		foreach($matches[0] as $match){
			$variable = (isset($request::$url_variables[$match]))?$request::$url_variables[$match]:'[a-zA-Z0-9]{1,}';
			$potential = str_replace($match,$variable,$potential);
		}
	}
	if( preg_match($potential,$request::$request_uri) )
	{
		$valid = true;
		$active_controller = $route;
		break;
	}
}
if( $valid )
{
    $path = $active_controller['path'];
    if( preg_match_all('/\{[a-zA-Z0-9\$]{1,}\}/',$path,$matches) )
    {
        foreach($matches[0] as $match)
        {
            $variable = substr($match,1,-1);
            if( substr($variable,0,1) == '$' )
                $path = str_replace($match,substr($variable,1),$path);
            else
                $path = str_replace($match,constant($variable),$path);
        }
    }
    $display = array("\n"=>"<br />","\t"=>"&nbsp;");

    require_once($path);
    $api = new $active_controller['name']();
    $method = $active_controller['method'];
	$request::getUrlData($active_controller['pattern']);
    if( method_exists($api,$method) ){
        Response::setBody($api->$method($request::$data));
        Response::send_200();
	}
    else{
		Response::setBody(array("success"=>"0","error"=>'Invalid Request: '.php_uname('n').' '.$request::$request_uri.'; Method Does not exist: '.$method));
		Response::send_400();
	}
}
else{
    Response::setBody(array("success"=>"0","error"=>'Invalid Request: '.php_uname('n').' Route Does not exist'.$request::$request_uri));
    Response::send_400();
}
?>

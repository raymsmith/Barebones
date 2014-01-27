<?php
namespace Barebones\Lib;
require_once(LIBPATH."request.class.php");
require_once(LIBPATH."Permissions.class.php");
$request = Request::getInstance();

/*
if ($request::$isValid == true) {
	ApplicationSessions::setStrategy();
	ApplicationSessions::load($request::$ahis);
}


//load user permissions
Permissions::load();
*/

if( !file_exists(BASEPATH.SYSDIR."/routes/".$request::$version."/".$request::$module.".php") ){
	Response::setBody(array("success"=>"0","error"=>"Invalid Module ".php_uname('n')." ".BASEPATH.'routes/'.$request::$version.'/'.$request::$module.".php"));
	Response::send_400();
}
else
	require_once(BASEPATH.SYSDIR."/routes/".$request::$version."/".$request::$module.".php");


$router = new Router($routes);
if( $router->findRoute($request::$request_uri) ){
	$active_controller = $router->getRoute();
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
	$request::getUrlData($router->pattern);
    require_once($path);
    $api = new $active_controller['name']();
    $method = $active_controller['method'];
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

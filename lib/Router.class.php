<?
namespace BarebonesPHP;
class Router{
    private $route;
    private $routes;
    public $pattern;
    public function __construct($routes){
        $this->routes = $routes;
        $request = Request::getInstance();
    }
    public function findRoute($uri){
        $valid = false;
        $active_controller = "";
        $request = Request::getInstance();
        $routes_data = $this->routes[$request::$requestMethod];
		if( isset($this->routes['ANY']) ){
			$routes_data = array_merge_recursive($routes_data,$this->routes['ANY']);
		}
		$msg = "";
        foreach($routes_data as $route){
            $potential = $route['pattern'];
            if( preg_match_all('/:[a-zA-Z_-]{2,}/',$potential,$matches) ){
                foreach($matches[0] as $match){
                    $variable = (isset($request::$url_variables[$match]))?$request::$url_variables[$match]:'[a-zA-Z0-9]{1,}';
                    $potential = str_replace($match,$variable,$potential);
                }
            }
            $msg .= $potential."<br />";
            if( preg_match($potential,$uri) )
            {
                // Is sub-resource?
                if( isset($route['sub-resource']) ){
					$root = $route['root'];
					$this->pattern .= str_replace(array("@^","$@","@"),"",$root);
					if( preg_match_all('/:[a-zA-Z_-]{2,}/',$root,$matches) ){
						foreach($matches[0] as $match){
							$variable = (isset($request::$url_variables[$match]))?$request::$url_variables[$match]:'[a-zA-Z0-9]{1,}';
							$root = str_replace($match,$variable,$root);
						}
					}
					//echo $root."\n";
					$uri = preg_replace($root,"",$uri);
					//echo $uri."\n";
					require_once(BASEPATH.SYSDIR."/routes/".$route['sub-resource']);
					$this->routes = $routes;
					$valid = $this->findRoute($uri);
				}
                else{
					//print_r($this->routes);
					$this->pattern .= str_replace(array("@^","$@","@"),"",$route['pattern']);
					$this->route = $route;
					$valid = true;
				}
                break;
            }
        }
        return $valid;

    }
    public function getRoute(){
        return $this->route;
    }
}
?>

<?php
namespace Barebones\Lib;
class ApplicationDataConnectionPool
{
    protected static $connections;
    protected static $pool;
    protected static $default = ""; // Name of default database
    public static function init(){
        //Establish Pool
        ApplicationDataConnectionPool::$pool = array();
        //Create connections collection
        ApplicationDataConnectionPool::$connections = array();
        ApplicationDataConnectionPool::$connections['connection_name'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("ip","user","passwd"));
			return $db;
        };
	}
    public static function get($name)
    {
		if( !isset(ApplicationDataConnectionPool::$pool[$name]) ){
			$connections = ApplicationDataConnectionPool::$connections;
			ApplicationDataConnectionPool::set($name, $connections[$name]());
		}
		return ApplicationDataConnectionPool::$pool[$name];
    }
    public static function set($name,$value)
    {
        ApplicationDataConnectionPool::$pool[$name] = $value;
    }
    public static function getDefaultConnection(){
    	return ApplicationDataConnectionPool::get(self::$default);
    }
}
?>

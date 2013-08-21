<?php
namespace BarebonesPHP;
class ApplicationDataConnectionPool
{
    protected static $connections;
    protected static $pool;
    public static function init(){
        //Establish Pool
        ApplicationDataConnectionPool::$pool = array();
        //Create connections collection
        ApplicationDataConnectionPool::$connections = array();
        ApplicationDataConnectionPool::$connections['static'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("staticdb.ip","username","passwd"));
			return $db;
        };
        ApplicationDataConnectionPool::$connections['session'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("sessiondb.ip","username","passwd"));
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
}
?>

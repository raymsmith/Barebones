<?php
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
			$db = new ApplicationDatabase(new MysqlDatabase("loc.dbdev.azaleahealth.com","web_app","azweb2010"));
			return $db;
        };
        ApplicationDataConnectionPool::$connections['session'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("loc.dbsess.azaleahealth.com","web_app","azweb2010"));
			return $db;
        };
        ApplicationDataConnectionPool::$connections['common'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("loc.dbdev.azaleahealth.com","web_app","azweb2010"));
			return $db;
        };
        ApplicationDataConnectionPool::$connections['customer'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("loc.dbdev.azaleahealth.com","web_app","azweb2010"));
			return $db;
			/*
			$db_type = CUSTOMER_DATABASE_TYPE;
			$db = new ApplicationDatabase(new $db_type(CUSTOMER_DATABASE_IP,CUSTOMER_DATABASE_USER,CUSTOMER_DATABASE_PASSWD));
			return $db;
			*/
        };
        ApplicationDataConnectionPool::$connections['test'] = function(){
			require_once(LIBPATH."mysql_database.class.php");
			$db = new ApplicationDatabase(new MysqlDatabase("loc.dbtest.azaleahealth.com","web_app","azweb2010"));
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
        //return ( isset($this->pool[$name]))?$this->pool[$name]:$this->connections[$name]();
    }
    public static function set($name,$value)
    {
        ApplicationDataConnectionPool::$pool[$name] = $value;
    }
}
?>

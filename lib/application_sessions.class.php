<?php
//extends anotherSessionClass
require_once(LIBPATH."session_interface.if.class.php");
require_once(LIBPATH."database_sessions.class.php");
require_once(LIBPATH."file_sessions.class.php");
class ApplicationSessions
{
    private static $strategy;
    public function __construct(SessionInterface $strategy=NULL)
    {
        if( $strategy == NULL)
            self::$strategy = new FileSessions();
        else
            self::$strategy = $strategy;
    }
    public static function setStrategy(SessionInterface $strategy=NULL)
    {
        if( $strategy == NULL)
            //self::$strategy = new FileSessions();
            self::$strategy = new Database_Sessions("ip","user","passwd");
        else
            self::$strategy = $strategy;
    }
    public static function load($name="")
    {
        self::$strategy->register();
        if( !empty($name) )
            self::$strategy->session_name($name);
        self::$strategy->session_start();
    }

}
?>

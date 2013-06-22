<?php
require_once(LIBPATH.'mysql_result.class.php');
class MysqlDatabase extends Database
{
    private $server;
    private $user;
    private $passwd;
    public function __construct($server,$user,$pass)
    {
        $this->server = $server;
        $this->user = $user;
        $this->passwd = $pass;
        $this->connect();
    }
    public function connect()
    {
        $this->connection = mysqli_connect($this->server,$this->user,$this->passwd);
		$this->connection->set_charset("utf8");
    }
    public function query($query)
    {
		$res = mysqli_query($this->connection,$query);
		
		if( $this->get_error() != "" )
			return false;
        return new MysqlResult($res);
    }
    public function get_error()
    {
        return mysqli_error($this->connection);
    }
	
    public function escape($query)
    {
        return $this->connection->real_escape_string($query);
    }
    public function close(){}
}
?>

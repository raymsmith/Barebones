<?php
require_once(LIBPATH."abstract_session.abs.class.php");
class Database_Sessions extends AbstractSession
{
    private $db_name;
    private $table;
    private $db_connection;
    public function __construct($host,$user,$pass,$db="",$table="",$lifetime=""){
		$this->db_name = ( !empty($db) )?trim($db):"sessions";
		$this->table = ( !empty($table) )?trim($table):"sessions";
		$this->session_lifetime = ( is_numeric($lifetime) )?$lifetime:60*60;
        $this->db_connection = mysql_connect( $host, $user, $pass, false, MYSQL_CLIENT_COMPRESS );
        if( $this->db_connection === false )
            //Handle Error

        $this->session_id = "";
    }
    public function open($save_path,$session_name="")
    {
        $this->session_start = $this->get_timestamp();
		if( !$session_name )
			$this->session_id = md5(rand());
		else
			$this->session_id = $session_name;

		return true;
    }
    public function close()
    {
        $this->gc();
    }
    public function read($session_id)
    {
        if( !$this->__acquire_lock($this->session_id) )
            return '';
		$session_res = mysql_query("SELECT * FROM `".$this->db_name."`.`".$this->table."` WHERE `session_id` = '".$this->session_id."';",$this->db_connection);
		if ( mysql_error($this->db_connection) != "" || mysql_num_rows($session_res) == 0  )
		{
            if( mysql_error($this->db_connection) != "")
                //Handle Error
            $this->__release_lock($this->session_id);
			return '';
        }
        //$this->__release_lock($session_id);
        $session_row = mysql_fetch_array($session_res);
        $ses_data = base64_decode($session_row["session_data"]);
        $this->session_start = $session_row['session_created'];
        return $ses_data;

    }
    public function write($session_id, $session_data)
	{
        if( !isset($this->session_start) )
			$this->session_start = $this->get_timestamp();
		if( $this->session_id == "PHPSESSID" || trim($this->session_id) == "" )
			$this->session_id = md5(rand());
		if ( isset($_SERVER["REMOTE_ADDR"]) )
			$ip = $_SERVER["REMOTE_ADDR"];
		else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		else
			$ip = "UNAVAILABLE";

        if( !$this->__acquire_lock($this->session_id) )
            return '';
        $previous_data = array();
        $session_sql = "SELECT * FROM `".$this->db_name."`.`".$this->table."` WHERE session_id='".$this->session_id."' LIMIT 1;";
        $res = mysql_query($session_sql,$this->db_connection);
        if( mysql_error($this->db_connection) != "" )
        {
			$this->__release_lock($this->session_id);
            die(mysql_error($this->db_connection)."\n".$session_sql."\n");
        }
        if( mysql_num_rows($res) == 0 )
        {
            $session_sql = "INSERT INTO `".$this->db_name."`.`".$this->table."` (`session_id`,`session_gc_time`,`session_data`, `session_modified`, `session_created`) VALUES ('".$this->session_id."','".time()."','".base64_encode($session_data)."',NOW(NULL), NOW(NULL))";
        }
        else
        {
            $sess_row = mysql_fetch_assoc($res);
            $session_sql = "UPDATE `".$this->db_name."`.`".$this->table."` SET `session_gc_time`='".time()."',`session_data`='".base64_encode($session_data)."' WHERE `session_prim_id` = '".$sess_row['session_prim_id']."' LIMIT 1;";
        }


        $session_res = mysql_query($session_sql,$this->db_connection);
        if( mysql_error($this->db_connection) != "" )
        {
            $retval = false;
        }
        else
            $retval = true;

		$this->__release_lock($this->session_id);
        return $retval;
    }
    public function destroy($session_id)
    {
        //if( !$this->__acquire_lock($session_id) )
        //    return '';
		$session_sql = "DELETE FROM `".$this->db_name."`.`".$this->table."` WHERE `session_id`='".$this->session_id."';";
		$res = mysql_query($session_sql,$this->db_connection);
		if( mysql_error($this->db_connection) != "" )
			// Handle Error			
        $this->__release_lock($this->session_id);
    }
    public function gc($max_lifetime="")
    {
		$session_life = time() - $this->session_lifetime;
		$session_sql = "DELETE FROM `".$this->db_name."`.`".$this->table."` WHERE `session_gc_time` < '".$session_life."' ";
        $session_res = mysql_query($session_sql,$this->db_connection);
        if (!$session_res)
			return false;
        else
			return true;
    }
    public function session_start()
    {
        if( $this->session_id != "" )
            session_name($this->session_id);
        session_start();
    }
    public function session_name($name)
    {
        $this->session_id = $name;
        return true;
    }
    public function register()
    {
		ini_set('session.save_handler','user');
        ini_set('session.use_only_cookies', '0');
		ini_set('session.save_path','');
		ini_set('session.use_cookies','0');
        return parent::register();
    }
    private function __acquire_lock($session_id)
    {
        $sql = "SELECT GET_LOCK('".$session_id."', 920) AS `locked`;";
		$res = mysql_query($sql,$this->db_connection);
		if( mysql_error($this->db_connection) != "" )
			mail("rsmith@azaleahealth.com","Session Error","Acquire Lock Failed:\nSession ID:".$this->session_id."\n".mysql_error($this->db_connection));
		$lock = mysql_fetch_assoc($res);
		if (!$lock['locked'])
		{
            $msg = "Failed to Get Lock After 20 seconds:\nSession ID:".$this->session_id."\n".mysql_error($this->db_connection)."\nServer:".php_uname("n")."";
            if( isset($_SESSION['usr_cur_client_name']) )
                $msg .= "\nClient:".$_SESSION['usr_cur_client_name'];
            if( isset($_SESSION['usr_id']) )
            {
                $msg .= "\nUser:".$_SESSION['usr_id'];
                if( isset($_SESSION['usr_fname']) )
                    $msg .= "".$_SESSION['usr_fname'];
                if( isset($_SESSION['usr_lname']) )
                    $msg .= " ".$_SESSION['usr_lname'];
            }
			return false;
		}
        else
            return true;
    }
    private function __release_lock($session_id)
    {
		$res = mysql_query("SELECT RELEASE_LOCK('".$session_id."');",$this->db_connection);
        if( mysql_error($this->db_connection) != "" )
            return false;
        else
            return true;
    }
    private function get_timestamp( )
    {
		return date('Y-m-d H:i:s');
    }

}

?>

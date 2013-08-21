<?php
namespace BarebonesPHP;
require_once(LIBPATH."abstract_session.abs.class.php");
class FileSessions extends AbstractSession
{
    public function __construct()
    {
    }
    public function open($save_path,$session_name){}
    public function close(){}
    public function read($session_id){}
    public function write($session_id, $session_data){}
    public function destroy($session_id){}
    public function gc($max_lifetime){}
    public function session_start()
    {
        if (!session_start())
            file_put_contents("log.log","session started: ".session_name(),FILE_APPEND);
    }
    public function session_name($name)
    {
        session_name($name);
        file_put_contents("log.log","$name started?",FILE_APPEND);
        return true;
    }
    public function register()
    {
		//ini_set('session.save_handler','files');
        //return parent::register();
    }

}
?>

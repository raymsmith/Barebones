<?php
namespace BarebonesPHP;
interface SessionInterface
{
    public function open($save_path,$session_name);
    public function close();
    public function read($session_id);
    public function write($session_id, $session_data);
    public function destroy($session_id);
    public function gc($max_lifetime);
    public function session_start();
    public function session_name($name);
    public function register();
}
?>

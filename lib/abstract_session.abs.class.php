<?php
namespace BarebonesPHP;
require_once(LIBPATH."session_interface.if.class.php");
abstract class AbstractSession implements SessionInterface
{
    private $session_id;
	private $session_lifetime;
	private $session_start;
    public function register()
    {
        register_shutdown_function('session_write_close');
        return session_set_save_handler(array( $this, 'open' ), array( $this, 'close' ), array( $this, 'read' ), array( $this, 'write' ), array( $this, 'destroy' ), array( $this, 'gc' ));
    }
}
?>

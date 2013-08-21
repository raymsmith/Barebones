<?php
// autoloader.php
// This file is reserved for classes that must be loaded with every request

require_once(LIBPATH."response.class.php");
require_once(LIBPATH."Router.class.php");
require_once(LIBPATH."database_interface.if.class.php");
require_once(LIBPATH."database.abs.class.php");
require_once(LIBPATH."database_result.class.php");
require_once(LIBPATH."application_database.class.php");
require_once(LIBPATH."application_data_connection_pool.class.php");
require_once(LIBPATH."Schema.abs.class.php");
require_once(LIBPATH."Model.abs.class.php");
require_once(LIBPATH."ComplexModel.abs.class.php");
require_once(LIBPATH."application_controller.abs.class.php");

?>

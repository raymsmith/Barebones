<?php
// autoloader.php
// This file is reserved for classes that must be loaded with every request
// Preloaded Classes
require_once(LIBPATH."Application.class.php");
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
require_once(LIBPATH."application_sessions.class.php");
// Autoloader function
spl_autoload_register(function($class){
    if( strpos($class,"\\") !== false ){
        $path = explode("\\",$class);
        $class = array_pop($path);
        require_once(BASEPATH.strtolower(implode("/",$path))."/".$class.".class.php");
    }
    else{
        require("classmap.php");
    	//if the file has a reference in the file, let's require it
    	if(isset($CLASSMAP[$class])){
	    	require_once(BASEPATH.SYSDIR."/".$CLASSMAP[$class]);
    	//if we can't find the file in the classmap, and we are in dev, we will just find the file
	    }
        else if(!isset($CLASSMAP[$class]) && !Application::isProduction() ){
		    //find files in the outer directory that match the class
    		$cmd = "find ".BASEPATH.SYSDIR."/ -type f -name ".$class.".class.php";
            $file = shell_exec($cmd);
	    	//remove the newline character from the string
            if(preg_match('/\n/', $file)){
                $file=substr($file, 0, -1);
            }
    		//if we have found the file, lets require it, else throw an exception
	    	if($file != "" && file_exists($file) ){
		    	require_once($file);
                require('generate_class_map.php');
            }
	    }
    }
});


?>

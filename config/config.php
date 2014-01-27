<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
if( file_exists(__DIR__."/web.config.php") )
	require_once(__DIR__."/web.config.php");

if( !defined('SYSDIR') )
    define("SYSDIR","BarebonesPHP");
if( !defined('BASEPATH') )
    define('BASEPATH',str_replace(SYSDIR."/config","",__DIR__));
if( !defined('LIBPATH') )
    define('LIBPATH',BASEPATH.SYSDIR."/lib/");
if( !defined('APIPATH') )
    define('APIPATH',BASEPATH."api/");
if( !defined('VENDORPATH') )
    define('VENDORPATH',BASEPATH.SYSDIR."/vendor/");
if( !defined('CONTROLLERSPATH') )
    define('CONTROLLERSPATH',BASEPATH.SYSDIR."/controllers/");
if( !defined('UTILITIESPATH') )
    define('UTILITIESPATH',BASEPATH.SYSDIR."/utilities/");
if( !defined('SCHEMAPATH') )
    define('SCHEMAPATH',BASEPATH.SYSDIR."/schemas/");
if( !defined('MODELPATH') )
    define('MODELPATH',BASEPATH.SYSDIR."/models/");
if( !defined('TMP_DIR') )
    define('TMP_DIR',BASEPATH."tmp/");


require_once("autoloader.php");
Barebones\Lib\ApplicationDataConnectionPool::init();
?>

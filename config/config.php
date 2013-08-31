<?php
use BarebonesPHP as Barebones;
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
if( !defined('CONTROLLERSPATH') )
    define('CONTROLLERSPATH',BASEPATH.SYSDIR."/controllers/");
if( !defined('SCHEMAPATH') )
    define('SCHEMAPATH',BASEPATH.SYSDIR."/schemas/");
if( !defined('MODELPATH') )
    define('MODELPATH',BASEPATH.SYSDIR."/models/");

require_once("autoloader.php");
// Initialize ApplicationDataConnectionPool
Barebones\ApplicationDataConnectionPool::init();
?>

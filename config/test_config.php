<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

if( file_exists(__DIR__."/web.config.php") )
	require_once(__DIR__."/web.config.php");

if( !defined('SYSDIR') )
	define("SYSDIR","barebones");

if( !defined('BASEPATH') )
    define('BASEPATH',str_replace(SYSDIR."/config","",__DIR__));
if( !defined('LIBPATH') )
    define('LIBPATH',BASEPATH.SYSDIR."/lib/");
if( !defined('APIPATH') )
    define('APIPATH',BASEPATH.SYSDIR."/api/");
if( !defined('CONTROLLERSPATH') )
    define('CONTROLLERSPATH',BASEPATH.SYSDIR."/controllers/");
if( !defined('TESTDIR') )
	define("TESTDIR",BASEPATH.SYSDIR."/tests/");
if( !defined('SCHEMAPATH') )
    define('SCHEMAPATH',TESTDIR."test_schemas/");
if( !defined('MODELPATH') )
    define('MODELPATH',BASEPATH.SYSDIR."/models/");
require_once("autoloader.php");

// Initialize ApplicationDataConnectionPool
Barebones\Lib\ApplicationDataConnectionPool::init();
?>

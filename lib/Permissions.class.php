<?php
namespace BarebonesPHP;
class Permissions{
	public static function load(){
		$db = ApplicationDataConnectionPool::get('static');
	}
	public static function hasAccess($perm){
	}
}
?>

<?php
class Permissions{
	public static function load(){
		$db = ApplicationDataConnectionPool::get('common');
		if(!isset($_SESSION['usr_cur_client_persmissions'])){
			$permissions = array();

			$sql = "SELECT * FROM `common`.`users_permissions` WHERE `upr_usr_id` = '" . $_SESSION['usr_id'] . 
			"' AND `upr_cus_id` = '" . $_SESSION['usr_cur_customer_id'] . 
			"' AND `upr_cli_id` = '" . $_SESSION['usr_cur_client_id'] . "';";

			$result = $db->query($sql);

			$row = $result->next();
			if($row['upr_admin'] || isset($_SESSION['usr_cur_client_customer_login']) && $_SESSION['usr_cur_client_customer_login'] == "1"){
				array_push($permissions, "upr_admin");
			}else if($row){
				foreach($row as $key=>$value){
					if($value == "1"){
						array_push($permissions, $key); 
					}
				}
			}
			$_SESSION['usr_cur_client_persmissions'] = $permissions;


		}//end if
	}
	public static function hasAccess($perm){
		if(!isset($_SESSION['usr_cur_client_persmissions'])){
			self::load();
		}
		
		if(in_array("upr_admin", $_SESSION['usr_cur_client_persmissions'])){
			return true;
		}else if(in_array($perm, $_SESSION['usr_cur_client_persmissions'])){
			return true;
		}else{
			return false;
		}

	}
}
?>

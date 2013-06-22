<?php
$routes['GET'][] = array(
   "name"=>"CommonController",
   "path"=>"{CONTROLLERSPATH}common_controller.class.php",
   "pattern"=>"@^general/common/$@",
   "method"=>"call"
);
$routes['PUT'][] = array(
   "name"=>"CommonController",
   "path"=>"{CONTROLLERSPATH}common_controller.class.php",
   "pattern"=>"@^general/common/$@",
   "method"=>"call"
);
$routes['GET'][] = array(
   "name"=>"CommonController",
   "path"=>"{CONTROLLERSPATH}common_controller.class.php",
   "pattern"=>"@^general/common/2/:alphanumeric$@",
   "method"=>"get"
);
$routes['GET'][] = array(
   "name"=>"PermissionsAPI",
   "path"=>"{APIPATH}timeme/api/permissions_api.class.php",
   "pattern"=>"@^lh/myrna/general/permissions/[0-9a-zA-Z_]{1,}$@",
   "method"=>"get"
);
?>

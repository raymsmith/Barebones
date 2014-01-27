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
?>

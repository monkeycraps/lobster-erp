<?php
$user = $controller->user;

$app = array(
	'user'=>array(
		'id'=>$user->id, 
		'username'=>$user->username, 
		'messae'=>$user->message, 
		'role_id'=>$user->role_id, 
		'role_name'=>rbac\Role::getRoleName( $user->role_id ), 
	), 
);
echo json_encode( $app );
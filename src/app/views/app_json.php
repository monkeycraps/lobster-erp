<?php
$user = $controller->user;

$app = array(
	'user'=>array(
		'id'=>$user->id, 
		'username'=>$user->username, 
		'messae'=>$user->message, 
		'role_id'=>$user->role_id, 
		'role_name'=>rbac\Role::getRoleName( $user->role_id ), 
		'role'=>array(
			'mission_type'=>rbac\Role::getRoleActions( $user->role_id ), 
		), 
		'session_id'=>session_id(), 
	), 
	'store'=> StoreModel::getAll(), 
	'now'=>Helper\Html::now(), 
);
echo json_encode( $app );
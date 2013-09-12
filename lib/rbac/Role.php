<?php 
namespace rbac;

class Role{

	static function getRoleName( $role_id ){
		return '客服';
	}

	static function getRoleActions(){

		return MissionType::getMissionTypeList();
	}
}
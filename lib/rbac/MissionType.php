<?php 
namespace rbac;
class MissionType{
	
	static function getMissionTypeTop(){
		return \R::getAll( 'select * from mission_type where pid = 0 order by id asc' );
	}
}
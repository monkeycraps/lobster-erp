<?php

class MissionTypeModel extends RedBean_SimpleModel {

	static function getName( $id ){
		return R::load( 'mission_type', $id )->name;
	}

	static function getParentName( $id ){
		return R::getCell( 'select p.name from mission_type p inner join mission_type m on p.id = m.pid and m.id = ?', array( $id ) );
	}
}

<?php

class MissionTypeModel extends RedBean_SimpleModel {

	static function getName( $id ){
		return R::load( 'mission_type', $id )->name;
	}
}

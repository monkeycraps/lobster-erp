<?php

class MissionExtModel extends RedBean_SimpleModel {

	static function setExt( $mission_type, $mission_id, $data ){

		$mission_type = 12;
		$func = "setExt{$mission_type}";
		return self::$func( $mission_id, $data );
	}

	static function setExt12( $mission_id, $data ){

		$form_ext = self::getFormExt( 12 );

		$data_filter = self::filter( $data, $form_ext );

		$ext = R::dispense( 'mission_ext' );
		$ext->mission_id = $mission_id;
		$ext->other = json_encode( $data_filter );
		R::store( $ext );
		return $ext;
	}

	static function getFormExt( $mission_type ){
		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );
		return $yaml['form'. $mission_type];
	}

	static function filter( $data, $form_ext ){

		$keys = array_keys( $form_ext );
		$data_filter = array();
		foreach( $data as $key=>$one ){
			if( in_array( $key, $keys ) ){
				$data_filter[$key] = $one;
			}
		}

		return $data_filter;
	}

}

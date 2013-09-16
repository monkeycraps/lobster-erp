<?php

class MissionExtModel extends RedBean_SimpleModel {

	static function setExt( $mission_type, $mission_id, $data ){

		switch( $mission_type ){

			// $func = "setExt{$mission_type}";
			default:
				$func = "setExtNormal";
		}
		return self::$func( $mission_type, $mission_id, $data );
	}

	static function setExtNormal( $mission_type, $mission_id, $data ){

		$form_ext = self::getFormExt( $mission_type );
		$data_filter = self::filter( $data, $form_ext );

		if( !$ext = current(R::find( 'mission_ext', 'mission_id = ?', array( $mission_id ) )) ){
			$ext = R::dispense( 'mission_ext' );
			$ext->mission_id = $mission_id;
			$ext->created = Helper\Html::now();
		}
		isset( $data['ext1'] ) && $ext->ext1 = $data['ext1'];
		isset( $data['ext2'] ) && $ext->ext2 = $data['ext2'];
		isset( $data['ext3'] ) && $ext->ext3 = $data['ext3'];
		$ext->updated = Helper\Html::now();
		$ext->other = json_encode( $data_filter );

		R::store( $ext );
		return $ext;
	}

	static function getFormExt( $mission_type ){
		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );
		if( !is_array( $yaml['form'. $mission_type]['ext'] ) ){
			$yaml['form'. $mission_type]['ext'] = array();
		}
		return $yaml['form'. $mission_type]['ext'];
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

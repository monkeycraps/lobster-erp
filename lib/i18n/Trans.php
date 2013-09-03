<?php

namespace i18n;
class Trans{

	static $category_list = array();

	static function getCategory( $category ){
		$yaml = yaml_parse_file( APP_PATH. '/config/lan/'. $category .'.ini' );
		header( 'Content-Type: text/html; charset=utf-8' );
		var_dump( $yaml );die;
	}

	static function t( $category, $key ){
		$category = self::getCategory( $category );
		return isset( $category[$key] ) ? isset( $category[$key] ) : $key;
	}

	static function f( $key ){
		return self::t( 'form', $key );
	}
}
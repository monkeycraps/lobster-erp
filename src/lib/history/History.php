<?php
namespace history;

class History{

	static function prev(){
		$session = \Yaf\Session::getInstance();
		$history = $session->get( 'history' );
		// return $history && isset( $history[count($history)-2] ) ? $history[count($history)-2] : '/';
		return '/';
	}

	static function set(){

		if( \http\HTTP::isAjax() ){
			return;
		}

		$session = \Yaf\Session::getInstance();
		$history = $session->get( 'history' );
		!$history && $history = array();
		if( count( $history ) > 10 ){
			array_shift( $history );
		}

		array_push( $history, $_SERVER['REQUEST_URI']);
		$session->history = $history;
	}
}

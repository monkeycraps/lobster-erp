<?php

function dump( $data ){
	var_dump( $data );
}

function debug( $msg, $key = null ){

	eYaf\Logger::getLogger()->log( "\r\n************* ". $key ." **************\r\n". ( is_array($msg) ? var_export($msg, 1) : $msg ). "\r\n***************************\r\n" );
}

function sdebug( $msg, $key = null ){

	if( !headers_sent() ){
		header( 'Http/1.0 500 sdebug' );
	}
	echo var_export( $msg, 1 );
}

function plugin( $plugin ){
	return Yaf\Application::app()->$plugin;
}
<?php

function dump( $data ){
	var_dump( $data );
}

function debug( $msg, $key = null ){

	eYaf\Logger::getLogger()->log( "*************". $key ."**************\r\n". ( is_array($msg) ? var_export($msg, 1) : $msg ). "***************************\r\n" );
}
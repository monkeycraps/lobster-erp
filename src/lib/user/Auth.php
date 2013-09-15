<?php 
namespace user;
class Auth{
	static function encrypt( $pwd ){
		$salt = self::unique_salt();
		return crypt($pwd, '$2a$10$'. $salt); 
	}

	static function unique_salt() { 
		return substr(sha1(mt_rand()),0,22); 
	} 

	static function check( $pwd, $hash ){
		if( !$hash or strlen( $hash ) < 30 )return false;
		return crypt( $pwd, substr( $hash, 0, 29 ) ) == $hash;
	}
}
<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

abstract class pac_Core {

	const ROLES_TABLE              = 'pac_roles';
	const ROLES_USERS_TABLE        = 'pac_users_roles';
	const ROLES_INCLUDES_TABLE     = 'pac_role_includes';
	const ROLES_DESTINATIONS_TABLE = 'pac_roles_destinations_values';

	protected static $pdoProvider;   ///< pac_PDOProvider
	protected static $destProvider;  ///< pac_DestinationProvider

	/**
	 * @param pac_PDOProvider         $pdoProvider
	 * @param pac_DestinationProvider $destProvider
	 */
	public static function init(pac_PDOProvider $pdoProvider, pac_DestinationProvider $destProvider) {
		self::$pdoProvider  = $pdoProvider;
		self::$destProvider = $destProvider;
	}

	/**
	 * @param  string $statement
	 * @return PDOStatement
	 */
	public static function prepare($statement) {
		self::selfTest();
		return self::pdo()->prepare($statement);
	}

	/**
	 * Return table prefix
	 *
	 * @param  string $tablename
	 * @return string
	 */
	public static function dbTable($tablename) {
		self::selfTest();
		return self::$pdoProvider->getPrefix().$tablename;
	}

	/**
	 * return current PDO instance
	 *
	 * @return PDO
	 */
	public static function pdo() {
		self::selfTest();
		return self::$pdoProvider->getPDO();
	}

	/**
	 * @param  string $token
	 * @return pac_Destination
	 */
	public static function getDestination($id) {
		self::selfTest();
		$destination = self::$destProvider->getDestination($id);
		if(!($destination instanceof pac_Destination))
			throw new pac_Exception ('Destination for '.$id.' could not be get from configured DestinationProvider');

		return $destination;
	}

	/**
	 * @throws pac_Exception
	 */
	protected static function selfTest() {
		if (!(self::$pdoProvider instanceof pac_PDOProvider) || !(self::$destProvider instanceof pac_DestinationProvider)) {
			throw new pac_Exception('pac_Core is not initialized!');
		}
	}
}

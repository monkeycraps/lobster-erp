<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

interface pac_PDOProvider {

	/**
	 * return current PDO instance
	 *
	 * @return PDO
	 */
	public function getPDO();

	/**
	 * return current table prefix
	 *
	 * @return string
	 */
	public function getPrefix();

}

<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class pac_Loader {
	public static function loadClass($className) {
		$className = str_replace('pac_', '', $className);
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$className.'.php')){
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.$className.'.php');
		}
	}

	public static function register() {
		if (function_exists('spl_autoload_register')) {
			spl_autoload_register(array(__CLASS__, 'loadClass'));
		}
		else {
			function __autoload($className) {
				self::loadClass($className);
			}
		}
	}
}

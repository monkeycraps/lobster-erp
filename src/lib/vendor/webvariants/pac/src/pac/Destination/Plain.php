<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class pac_Destination_Plain extends pac_Destination {

	/**
	 * @param string $token
	 */
	public function __construct($id, $tokens, array $supers = array()) {
		parent::__construct($id, $tokens, 'boolean', $supers);
	}

	public function evaluate($value, $checkValue) {
		return $value === $checkValue;
	}

	public function normalizeValue($value) {
		$value = parent::normalizeValue($value);

		if (empty($value))   return false;
		if (is_bool($value)) return $value;

		if (is_array($value) && count($value) == 1) {
			return $value[0];
		}

		throw new pac_Exception('Destination value is not valid.');
	}
}

<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class pac_Destination_List extends pac_Destination {

	public function __construct($id, $tokens, $vartype, $supers = array()) {
		parent::__construct($id, $tokens, $vartype, $supers);
	}

	public function evaluate($value, $checkValue) {
		return in_array($checkValue, $value, true);
	}
}

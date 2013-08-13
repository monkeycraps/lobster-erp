<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

abstract class pac_Destination {
	protected $id;       ///< string
	protected $tokens;   ///< array
	protected $supers;   ///< array
	protected $vartype;  ///< string

	/**
	 * @param string $token
	 */
	public function __construct($id, array $tokens, $vartype, array $supers) {
		$this->id      = $id;
		$this->tokens  = $tokens;
		$this->supers  = $supers;
		$this->vartype = $vartype;
	}

	/**
	 * @return array
	 */
	public function getTokens() {
		return $this->tokens;
	}

	/**
	 *
	 * @param string $token
	 * @return array
	 */
	public function getSupers($token) {
		if(isset($this->supers[$token])) {
			return $this->supers[$token];
		}
	}

	/**
	 * @param  mixed $value
	 * @return mixed
	 */
	public function normalizeValue($value) {
		if (is_array($value)) {
			foreach ($value as $idx => &$val) {
				settype($val, $this->vartype);
			}
		}
		else {
			settype($value, $this->vartype);
		}

		return $value;
	}

	/**
	 * get the id of this destination
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	public function validateToken($token, $throw = true) {
		if (!in_array($token, $this->tokens)) {
			if ($throw) throw new pac_Exception('The destination "'.$this->id.'" does not contain the token: '.$token);
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param  mixed $value       from roles
	 * @param  mixed $checkValue  value to have
	 * @return boolean            hasPermission
	 */
	abstract public function evaluate($value, $checkValue);
}

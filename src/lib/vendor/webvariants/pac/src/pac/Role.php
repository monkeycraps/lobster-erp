<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class pac_Role {

	private $id;
	private $includes;
	private $title;
	private $cache = array();

	public function __construct($id, $title, $includes) {
		$this->id       = $id;
		$this->title    = $title;
		$this->includes = $includes;
	}

	public function getId() {
		return $this->id;
	}

	public function getIncludes() {
		return $this->includes;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setIncludes($includes) {
		$this->includes = $includes;
	}

	/**
	 * @return array
	 */
	public function isIncludedIn() {
		$stmt = pac_Core::pdo()->prepare('SELECT `role_id` FROM `'.pac_Core::dbTable(pac_Core::ROLES_INCLUDES_TABLE).'` WHERE `included_role_id` = :included_role_id');
		$stmt->execute(array('included_role_id' => $this->id));
		$included_in = $stmt->fetchAll(PDO::FETCH_COLUMN, 'role_id');

		return $included_in;
	}

	/**
	 * @return boolean
	 */
	public function isIncluded() {
		$included_in = $this->isIncludedIn();
		return !empty($included_in);
	}

	/**
	 * @return array
	 */
	public function getAllIncludedRoles() {
		$res     = array();
		$service = pac_RoleService::getInstance();

		foreach ($this->includes as $include_id) {
			if (!array_key_exists($include_id, $res)) {
				$role             = $service->getRoleById($include_id);
				$res[$include_id] = $role;

				foreach ($role->getAllIncludedRoles() as $xrole) {
					if (!array_key_exists($xrole->getId(), $res)) {
						$res[] = $xrole;
					}
				}
 			}
		}

		return $res;
	}

	public function setTitle($title) {
		$this->title = (string) $title;
	}

	public function hasIncludes() {
		return !empty($this->includes);
	}

	public function hasPermission(pac_Destination $destination, $token, $checkValue = true) {
		$destination->validateToken($token);
		if(!isset($this->cache[$destination->getId()])) $this->cache[$destination->getId()] = array();
		if(!isset($this->cache[$destination->getId()][$token])) {
			$stmt = pac_Core::prepare('SELECT `value` FROM '.pac_Core::dbTable(pac_Core::ROLES_DESTINATIONS_TABLE).' WHERE `destination` = :destination AND `token` = :token AND `role_id` = :role_id');
			$stmt->execute(array('destination' => $destination->getId(), 'token' => $token, 'role_id' => $this->getId()));
			$this->cache[$destination->getId()][$token] = $destination->normalizeValue($stmt->fetchAll(PDO::FETCH_COLUMN, 'value'));
		}
		$value = $this->cache[$destination->getId()][$token];
		if(empty($value)) return false;
		return $destination->evaluate($value, $checkValue);
	}
}

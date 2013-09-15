<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class pac_User {
	private $id;
	private $roles;
	private $cache;

	public function __construct($id) {
		$stmt = pac_Core::pdo()->prepare('SELECT role_id FROM '.pac_Core::dbTable(pac_Core::ROLES_USERS_TABLE).' WHERE user_id = :user_id');
		$stmt->execute(array('user_id' => $id));

		$this->id    = $id;
		$this->roles = $stmt->fetchAll(PDO::FETCH_COLUMN, 'role_id');
		$this->cache = array();
	}

	public function getId() {
		return $this->id;
	}

	public function hasRole($roleId) {
		return in_array($roleId, $this->roles);
	}

	public function getRoles() {
		return $this->roles;
	}

	public function setRoles($roleIds) {
		$pdo = pac_Core::pdo();
		$pdo->beginTransaction();

		try {
			$stmt = pac_Core::pdo()->prepare('DELETE FROM '.pac_Core::dbTable(pac_Core::ROLES_USERS_TABLE).' WHERE user_id = :user_id');
			$stmt->execute(array('user_id' => $this->getId()));

			$stmt = pac_Core::pdo()->prepare('INSERT INTO '.pac_Core::dbTable(pac_Core::ROLES_USERS_TABLE).' (user_id, role_id) VALUES (:user_id, :role_id)');

			foreach ($roleIds as $roleId) {
				$stmt->execute(array('user_id' => $this->getId(), 'role_id' => $roleId));
			}

			$pdo->commit();
		}
		catch (Exception $e) {
			$pdo->rollBack();
			throw new pac_Exception($e->getMessage(), $e->getCode());
		}
	}

	protected function collectAllRoles() {
		$res     = array();
		$service = pac_RoleService::getInstance();

		//$stmt = pac_Core::pdo()->prepare('SELECT DISTINCT `included_role_id` FROM '.PAC_CORE::dbTable(pac_Core::ROLES_INCLUDES_TABLE).' WHERE `role_id` IN (?)');
		//$res = $stmt->fetchAll(PDO::FETCH_COLUMN, 'included_role_id');
		//sly_dump($res);

		foreach ($this->roles as $roleId) {
			$role = $service->getRoleById($roleId);
			$res[$roleId] = $role;

			foreach ($role->getAllIncludedRoles() as $xrole) {
				if (!array_key_exists($xrole->getId(), $res)) {
					$res[$xrole->getId()] = $xrole;
				}
			}
		}

		return $res;
	}

	public function hasPermission($destinationId, $token, $checkValue = true) {
		$destination = pac_Core::getDestination($destinationId);
		$result      = $this->getFromCache($destination, $token, $checkValue);

		if($result === null) {
			$destination->validateToken($token);
			$roles  = $this->collectAllRoles();
			$result = false;
			foreach($roles as $role) {
				$result = $role->hasPermission($destination, $token, $checkValue);
				if($result === true) break;
			}
			$this->setToCache($destination, $token, $checkValue, $result);
		}
		return $result;
	}

	private function getFromCache(pac_Destination $destination, $token, $checkValue) {
		if(!array_key_exists($destination->getId(), $this->cache)) return null;
		if(!array_key_exists($token, $this->cache[$destination->getId()])) return null;

		if($destination instanceof pac_Destination_Plain) {
			return $this->cache[$destination->getId()][$token];
		}elseif($destination instanceof pac_Destination_List) {
			if(array_key_exists($checkValue, $this->cache[$destination->getId()][$token])) {
				return $this->cache[$destination->getId()][$token][$checkValue];
			}
		}
		return null;
	}

	protected function setToCache(pac_Destination $destination, $token, $checkValue, $result) {
		if($destination instanceof pac_Destination_Plain) {
			$this->cache[$destination->getId()][$token] = $result;
		}elseif($destination instanceof pac_Destination_List) {
			$this->cache[$destination->getId()][$token][$checkValue] = $result;
		}
	}
}

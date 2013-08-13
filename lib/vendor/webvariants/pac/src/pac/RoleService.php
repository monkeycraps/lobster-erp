<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class pac_RoleService {

	private $list;
	private static $instance;

	/**
	 *
	 * @return pac_RoleService
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->list = array();
		$this->build();
	}

	/**
	 * do not clone!!!
	 */
	private function __clone() {

	}

	/**
	 * Reads the role graph.
	 *
	 */
	private function build() {
		// TODO: get from cache
		$pdo   = pac_Core::pdo();
		$stmt  = $pdo->prepare('SELECT * FROM '.pac_Core::dbTable(pac_Core::ROLES_TABLE));
		$stmt2 = $pdo->prepare('SELECT `included_role_id` FROM `'.pac_Core::dbTable(pac_Core::ROLES_INCLUDES_TABLE).'` WHERE `role_id` = :role_id');

		$stmt->execute();

		foreach ($stmt->fetchAll() as $row) {
			$stmt2->execute(array('role_id' => $row['id']));
			$includes = $stmt2->fetchAll(PDO::FETCH_COLUMN, 'included_role_id');

			$role = new pac_Role($row['id'], $row['title'], $includes);
			$this->list[$role->getId()] = $role;
		}
	}

	public function __destruct() {
		// TODO: set to cache
	}

	/**
	 * Createa a role.
	 *
	 * @param string $title
	 * @param array $includes
	 */
	public function createRole($title, $includes) {
		$pdo = pac_Core::pdo();
		$pdo->beginTransaction();

		try {
			$stmt = $pdo->prepare('INSERT INTO `'.pac_Core::dbTable(pac_Core::ROLES_TABLE).'` (title) VALUES (:title)');
			$stmt->execute(array('title' => $title));
			$roleId = $pdo->lastInsertId();

			if (!empty($includes)) {
				$stmt = $pdo->prepare('INSERT INTO `'.pac_Core::dbTable(pac_Core::ROLES_INCLUDES_TABLE).'` (`role_id`, `included_role_id`) VALUES (:role_id, :included_role_id)');

				foreach ($includes as $include) {
					$stmt->execute(array('role_id' => $roleId, 'included_role_id' => $include));
				}
			}

			$pdo->commit();

			$role = new pac_Role($roleId, $title, $includes);
			$this->list[$role->getId()] = $role;
		}
		catch (Exception $e) {
			$pdo->rollBack();
			throw new pac_Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Sets some Parames on a role.
	 *
	 * @param int    $roleId
	 * @param string $title
	 * @param array  $includes
	 */
	public function editRole($roleId, $title, $includes) {
		$this->validate($roleId);
		$pdo = pac_Core::pdo();
		$pdo->beginTransaction();

		try {
			$stmt = $pdo->prepare('UPDATE `'.pac_Core::dbTable(pac_Core::ROLES_TABLE).'` SET `title` = :title WHERE `id` = :role_id');
			$stmt->execute(array('title' => $title, 'role_id' => $roleId));
			$this->deleteRoleIncludes($roleId);

			if (!empty($includes)) {
				$stmt = $pdo->prepare('INSERT INTO `'.pac_Core::dbTable(pac_Core::ROLES_INCLUDES_TABLE).'` (`role_id`, `included_role_id`) VALUES (:role_id, :included_role_id)');

				foreach ($includes as $include) {
					$stmt->execute(array('role_id' => $roleId, 'included_role_id' => $include));
				}
			}

			$pdo->commit();

			$role = new pac_Role($roleId, $title, $includes);
			$this->list[$role->getId()] = $role;
		}
		catch (Exception $e) {
			$pdo->rollBack();
			throw new pac_Exception($e->getMessage().' '.$e->getCode());
		}
	}

	/**
	 * Deletes a role.
	 *
	 * @param int $roleId
	 */
	public function deleteRole($roleId) {
		$role       = $this->getRoleById($roleId);
		$includedIn = $role->isIncludedIn();

		// Do not allow deletion of roles which are included in others.
		if (!empty($includedIn)) {
			throw new pac_Exception('Cannot delete role! The role with ID "'.$roleId.'" in included in roles '.implode(', ', $includedIn).'.');
		}

		$pdo  = pac_Core::pdo();
		$data = array('role_id' => $roleId);
		$pdo->beginTransaction();

		try {
			$stmt = $pdo->prepare('DELETE FROM `'.pac_Core::dbTable(pac_Core::ROLES_TABLE).'` WHERE `id` = :role_id');
			$stmt->execute($data);
			$stmt = $pdo->prepare('DELETE FROM `'.pac_Core::dbTable(pac_Core::ROLES_USERS_TABLE).'` WHERE `role_id` = :role_id');
			$stmt->execute($data);
			$this->deleteRoleIncludes($roleId);
			$this->deleteDestinationValues($roleId);
			$pdo->commit();
			unset($this->list[$roleId]);
		}
		catch (Exception $e) {
			$pdo->rollBack();
			throw new pac_Exception($e->getMessage().' '.$e->getCode());
		}
	}

	/**
	 * Sets permission values for a role.
	 *
	 * $values is an assoc array of destination token and the value to set.
	 * Values can be arrays, to set list type destinations at once.
	 *
	 * @param int    $roleId
	 * @param string $destinationId
	 * @param array  $values
	 */
	public function setDestinationValues($roleId, $destinationId, $values) {
		$this->validate($roleId);
		$destination = pac_Core::getDestination($destinationId);

		$pdo = pac_Core::pdo();
		pac_Core::pdo()->beginTransaction();

		try {
			$this->deleteDestinationValues($roleId, $destinationId);
			$stmt = pac_Core::prepare('INSERT INTO '.pac_Core::dbTable(pac_Core::ROLES_DESTINATIONS_TABLE).' (`token`, `role_id`, `destination`, `value`) VALUES (:token, :role_id, :destination, :value)');
			foreach ($values as $token => $value) {
				$destination->validateToken($token);
				$value = $destination->normalizeValue($value);
				if(!is_array($value)) {
					$value = array($value);
				}
				foreach ($value as $val) {
					$stmt->execute(array('token' => $token, 'role_id' => $roleId, 'destination' => $destinationId, 'value' => $val));
				}
			}
			$pdo->commit();
		}
		catch (Exception $e) {
			$pdo->rollBack();
			throw new pac_Exception($e->getMessage().' '.$e->getCode());
		}
	}

	/**
	 * returns a group by its id, or return null
	 *
	 * @throws pac_Exception  if the role not exists.
	 * @param  int $roleId
	 * @return pac_Role
	 */
	public function getRoleById($roleId) {
		$this->validate($roleId);
		return $this->list[$roleId];
	}

	/**
	 * Checks if a role exists.
	 *
	 * @param  int $roleId
	 * @return boolean
	 */
	public function exists($roleId) {
		return array_key_exists($roleId, $this->list);
	}

	/**
	 * Checks if a role exists. Throws a pac_Exception is not.
	 *
	 * @throws pac_Exception  if the role not exists.
	 * @param  int $roleId
	 * @return boolean        always true
	 */
	public function validate($roleId) {
		if (!$this->exists($roleId)) {
			throw new pac_Exception('The role with ID "'.$roleId.'" does not exist!');
		}

		return true;
	}

	/**
	 * Returns all Roles in this configuration.
	 *
	 * @return array
	 */
	public function getAllRoles() {
		return $this->list;
	}

	/**
	 * Deletes all permission values for a role.
	 *
	 * @param  int $roleId
	 * @return boolean
	 */
	public function deleteDestinationValues($roleId, $destinationId = null) {
		$where = ' WHERE `role_id` = :role_id';
		$whereData = array('role_id' => $roleId);
		if($destinationId !== null) {
			$where .= ' AND `destination` = :destination';
			$whereData['destination'] = $destinationId;
		}
		$stmt = pac_Core::prepare('DELETE FROM `'.pac_Core::dbTable(pac_Core::ROLES_DESTINATIONS_TABLE).'`'.$where);
		return $stmt->execute($whereData);
	}

	/**
	 * Deletes all includes for a role.
	 *
	 * @param  int $roleId
	 * @return boolean
	 */
	protected function deleteRoleIncludes($roleId) {
		$stmt = pac_Core::prepare('DELETE FROM `'.pac_Core::dbTable(pac_Core::ROLES_INCLUDES_TABLE).'` WHERE `role_id` = :role_id');
		return $stmt->execute(array('role_id' => $roleId));
	}

	/**
	 * Returns the number of users that have a certain role assigned
	 *
	 * @param  pac_Role $role
	 * @return int
	 */
	public function getUsersWithRole(pac_Role $role) {
		$where     = ' WHERE `role_id` = :role_id';
		$whereData = array('role_id' => $role->getId());

		$stmt = pac_Core::prepare('SELECT COUNT(*) AS c FROM `'.pac_Core::dbTable(pac_Core::ROLES_USERS_TABLE).'`'.$where);
		$stmt->execute($whereData);

		return (int) $stmt->fetchColumn(0);
	}

	/**
	 * Returns a sorted list of user IDs for users with a certain role
	 *
	 * @param  pac_Role $role
	 * @return array
	 */
	public function getAllUsersWithRole(pac_Role $role) {
		$where     = ' WHERE `role_id` = :role_id';
		$whereData = array('role_id' => $role->getId());
		$userIds   = array();

		$stmt = pac_Core::prepare('SELECT user_id FROM `'.pac_Core::dbTable(pac_Core::ROLES_USERS_TABLE).'`'.$where);
		$stmt->execute($whereData);

		foreach ($stmt->fetchAll() as $row) {
			$userIds[] = (int) $row['user_id'];
		}

		sort($userIds);

		return array_values($userIds);
	}
}

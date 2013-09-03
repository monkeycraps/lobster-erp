<?php
class RoleModel extends RedBean_SimpleModel {
	
	private static $roles = array(); 

	static function getList($page = 1, $limit = 99999 ) {
		
		(!$page || !is_numeric($page)) && $page = 1;

		$pager = new pager\Pager ();
		$list = R::getAll ( 'select r.* from role r order by r.id asc limit :offset, :limit', array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		) );
		return array (
			$list,
			$pager 
		);
	}
	
	static function getRoleName( $role_id ){
		if( self::$roles ){
			return isset( self::$roles[$role_id] ) ? self::$roles[$role_id]['name'] : '异常';
		}
		list ( $tmp ) = self::getList();
		$list = array();
		foreach( $tmp as $one ){
			$list[$one['id']] = $one;
		}
		self::$roles = $list;
		return isset( self::$roles[$role_id] ) ? self::$roles[$role_id]['name'] : '异常';
	}
}
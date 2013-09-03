<?php
class UserModel extends RedBean_SimpleModel {
	
	static $status_names = array(
		0 => '正常', 
		101 => '暂停', 
		102 => '删除', 
	);

	static function getList($page = 1, $limit = 99999 ) {
		
		(!$page || !is_numeric($page)) && $page = 1;

		$pager = new pager\Pager ();
		$list = R::getAll ( 'select u.* from user u 
				where status <> 102
				order by u.id asc limit :offset, :limit', array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		) );
		
		foreach( $list as &$one ){
			$one['status_name'] = self::getStatusName( $one['status'] );
			$one['role_name'] = RoleModel::getRoleName( $one['role_id'] );
		}		
		return array (
			$list,
			$pager 
		);
	}
	
	static function getStatusName( $status ){
		return isset( self::$status_names[$status] ) ? self::$status_names[$status] : '异常';
	}

	static function getName( $id ){
		return R::load( 'user', $id )->name;
	}
}
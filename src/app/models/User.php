<?php
class UserModel extends RedBean_SimpleModel {

	const ROLE_KF = 1;
	const ROLE_CG = 2;
	const ROLE_DZ = 3;
	const ROLE_FCG = 4;
	
	static $state_names = array(
		0 => '正常', 
		101 => '暂停', 
		102 => '删除', 
	);

	static function getList($page = 1, $limit = 99999 ) {
		
		(!$page || !is_numeric($page)) && $page = 1;

		$pager = new pager\Pager ();
		$list = R::getAll ( 'select u.* from user u 
				where state <> 102
				order by u.id asc limit :offset, :limit', array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		) );
		
		foreach( $list as &$one ){
			$one['state_name'] = self::getStateName( $one['state'] );
			$one['role_name'] = RoleModel::getRoleName( $one['role_id'] );
		}		
		return array (
			$list,
			$pager 
		);
	}
	
	static function getStateName( $state ){
		return isset( self::$state_names[$state] ) ? self::$state_names[$state] : '异常';
	}

	static function getName( $id ){
		return R::load( 'user', $id )->name;
	}

	static function getKefuList(){
		return R::getAll( 'select * from user where role_id = ? ', array( self::ROLE_KF ) );
	}
}
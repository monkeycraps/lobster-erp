<?php

class ComebackModel extends RedBean_SimpleModel {

	const STATE_NORMAL = 1;
	const STATE_DONE = 2;
	const STATE_BACK = 3;

	static $_stateName = array(
		self::STATE_NORMAL => '未处理', 
		self::STATE_DONE => '已处理', 
		self::STATE_BACK => '已返厂', 
	);

	static function getList( $page=1, $limit = 99999, $key = null, $fcg = 0, $state = 0 ) {

		(!$page || !is_numeric($page)) && $page = 1;

		$pager = new pager\Pager ();

		$sql_key = '';
		$param = array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		);

		if( $key ){
		 	$sql_key .= ' and a.mail_num like ( :key ) ';
		 	$param[':key'] = '%'. $key. '%';
		}

		if( $fcg ){
		 	$sql_key .= ' and a.create_uid = :fcg ';
		 	$param[':fcg'] = $fcg;
		}

		if( $state ){
		 	$sql_key .= ' and a.state = :state ';
		 	$param[':state'] = $state;
		}

		$user = plugin( 'user' );
		if( $user->role_id == UserModel::ROLE_FCG ){
			$sql_key .= ' and a.create_uid = :uid ';
		 	$param[':uid'] = $user->id;
		}

		$tmp = R::getAll ( 'select a.*, 
				case when u.name is null then \'admin\' else u.name end as create_uname 
				from comeback a left join user u on a.create_uid = u.id
				where deleted is null '. $sql_key .'
				order by id desc limit :offset, :limit', $param );
		$list = array();

		foreach( $tmp as $one ){  
			$list[$one['id']] = $one;
		}
		return array (
			$list,
			$pager 
		);
	}

	static function getStateName( $state ){

		return isset( self::$_stateName[$state] ) ? self::$_stateName[$state] : '异常';
	}
}



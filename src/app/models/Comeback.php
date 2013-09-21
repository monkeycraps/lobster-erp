<?php

class ComebackModel extends RedBean_SimpleModel {

	const STATE_DRAFT = 0;
	const STATE_PUBLISHED = 1;

	static function getList( $page=1, $limit = 99999, $key = null ) {

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
}



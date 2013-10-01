<?php

class AnnounceModel extends RedBean_SimpleModel {

	const STATE_DRAFT = 0;
	const STATE_PUBLISHED = 1;

	static function getList($page=1, $limit = 99999 ) {

		(!$page || !is_numeric($page)) && $page = 1;
		
		$pager = new pager\Pager ();
		$tmp = R::getAll ( 'select a.*, 
				case when u.name is null then \'admin\' else u.name end as create_uname 
				from announce a left join user u on a.create_uid = u.id
				where deleted is null 
				order by id desc limit :offset, :limit', array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		) );
		$list = array();
		foreach( $tmp as $one ){  
			$list[$one['id']] = $one;
		}
		return array (
			$list,
			$pager 
		);
	}

	static function getListHeader( $limit = 2 ){

		$pager = new pager\Pager ();
		$tmp = R::getAll ( 'select a.*, 
				case when u.name is null then \'admin\' else u.name end as create_uname 
				from announce a left join user u on a.create_uid = u.id
				where deleted is null 
				and a.created > \''. date( 'Y-m-d', strtotime( '-3 day' ) ) .'\'
				order by id desc limit :offset, :limit', array (
			':offset' => 0,
			':limit' => $limit 
		) );
		$list = array();
		foreach( $tmp as $one ){  
			$list[$one['id']] = $one;
		}
	
		return array (
			$list,
			$pager 
		);
	}

	static function getListIndex( $page = 1, $limit = 10 ){

		$pager = new pager\Pager ();

		$cnt = R::getCell ( 'select count(1)
				from announce a left join user u on a.create_uid = u.id
				where deleted is null ');

		$pager->setSize( $limit );
		$pager->setPage( $page );
		$pager->setItemCount( $cnt );

		$tmp = R::getAll ( 'select a.*, 
				case when u.name is null then \'admin\' else u.name end as create_uname 
				from announce a left join user u on a.create_uid = u.id
				where deleted is null 
				order by id desc limit :offset, :limit', array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		) );
		$list = array();
		foreach( $tmp as $one ){  
			$list[$one['id']] = $one;
		}
	
		return array (
			$list,
			$pager 
		);
	}

	static function parse( $content ){
		return html_entity_decode( $content );
	}
}



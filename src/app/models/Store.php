<?php
class StoreModel extends RedBean_SimpleModel {

	static function getList($page = 1, $limit = 99999 ) {
		
		(!$page || !is_numeric($page)) && $page = 1;

		$pager = new pager\Pager ();
		$list = R::getAll ( 'select * from store order by id asc limit :offset, :limit', array (
			':offset' => ($page - 1) * $limit,
			':limit' => $limit 
		) );
		return array (
			$list,
			$pager 
		);
	}

	static function getAll(){
		list( $tmp ) = self::getList();
		$list = array();
		foreach( $tmp as $one ){
			$list[$one['id']] = $one;
		}
		return $list;
	}

	static function getName( $id ){
		return R::load( 'store', $id )->name;
	}
}
<?php
class CategoryModel extends RedBean_SimpleModel {

	static function getList($page=1, $limit = 99999 ) {

		(!$page || !is_numeric($page)) && $page = 1;
		
		$pager = new pager\Pager ();
		$tmp = R::getAll ( 'select * from category 
				where state <> 102
				order by id asc limit :offset, :limit', array (
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
	
	static function getName( $cate_id ){
		return R::load( 'category', $cate_id )->name;
	}
	
	static function getSelectList(){
		list( $list ) = self::getList();
		return $list;
	}

	static function getProductList( $cate_id ){

		$sql = 'select * from product where category_id = ? and state <> 102 ';
		return R::getAll( $sql, array( $cate_id ) );
	}

}

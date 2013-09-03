<?php
class ProductModel extends RedBean_SimpleModel {

	static function getList($page = 1, $limit = 99999 ) {
		
		(!$page || !is_numeric($page)) && $page = 1;

		$pager = new pager\Pager ();
		$tmp = R::getAll ( 'select p.*, c.name as category from product p 
				left join category c on p.category_id = c.id 
				where p.status <> 102
				order by p.id asc limit :offset, :limit', array (
			':offset' => ($page - 1)* $limit,
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
	
	static function getSelectList(){
		list( $tmp ) = self::getList();
		$list = array();
		foreach( $tmp as $one ){
			!isset( $list[$one['category_id']] ) && $list[$one['category_id']] = array();
			$list[$one['category_id']][$one['id']] = $one; 
		}
		return $list;
	}

	static function getName( $id ){
		return R::load( 'product', $id )->name;
	}
}

<?php
class ProductController extends AdminBaseController {
	protected $layout = 'admin';
	
	public $nav = 'product';

	public function indexAction() {

		list ( $products, $pager_product ) = ProductModel::getList ( $this->get ( 'pp' ) );
		list ( $categories, $pager_category ) = CategoryModel::getList ( $this->get ( 'pc' ) );
		$this->show ( 'index', array (
			'products' => $products,
			'categories' => $categories 
		) );
	}

	function productAction() {

		$request = $this->getRequest ();
		
		if ($request->isPut ()) {
			$id = $this->put ( 'id' );
			if (! $model = R::findOne ( 'product', 'id = ?', array (
				$id 
			) )) {
				throw new Exception ( 'model not found' );
			}
			$model->name = $this->put ( 'name' );
			$model->category_id = $this->put ( 'category_id' );
			$model->updated = date ( 'Y-m-d' );
			$id = R::store ( $model );
		} elseif ($request->isPost ()) {
			$model = R::dispense ( 'product' );
			$model->name = $this->post ( 'name' );
			$model->category_id = $this->post ( 'category_id' );
			$model->created = date ( 'Y-m-d' );
			$model->updated = date ( 'Y-m-d' );
			$id = R::store ( $model );
		} elseif( $request->isDelete() ) {
			$id = $this->get ( 'id' );
			$model = R::load( 'product', $id ); 
			$model->state = 102;
			R::store( $model );
			return $this->renderJson(array('error'=> 0));
		} else {
			$id = $this->get ( 'id' );
			$model = R::load ( 'product', $id );
		}
		
		$model->created = date ( 'Y-m-d', strtotime ( $model->created ) );
		$category_name = CategoryModel::getName ( $model->category_id );
		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'category' => $category_name 
		) ) );
	}

	function categoryAction() {

		$request = $this->getRequest ();
		
		if ($request->isPut ()) {
			$id = $this->put ( 'id' );
			if (! $model = R::findOne ( 'category', 'id = ?', array (
				$id 
			) )) {
				throw new Exception ( 'model not found' );
			}
			$model->name = $this->put ( 'name' );
			$model->updated = date ( 'Y-m-d' );
			$id = R::store ( $model );
		} elseif ($request->isPost ()) {
			$model = R::dispense ( 'category' );
			$model->name = $this->post ( 'name' );
			$model->created = date ( 'Y-m-d' );
			$model->updated = date ( 'Y-m-d' );
			$id = R::store ( $model );
		} elseif( $request->isDelete() ) {
			$id = $this->get ( 'id' );
			$model = R::load( 'category', $id ); 
			$model->state = 102;
			R::store( $model );
			return $this->renderJson(array('error'=> 0));
		} else {
			$id = $this->get ( 'id' );
			$model = R::load ( 'category', $id );
		}
		
		$model->created = date ( 'Y-m-d', strtotime ( $model->created ) );
		$this->renderJson ( $model->getIterator ()->getArrayCopy () );
	}
}

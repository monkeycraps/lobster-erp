<?php
class ComebackController extends ApplicationController {
	protected $layout = 'frontend';

	function init(){

		$this->nav= 'comeback';

		parent::init();
		$pass = $this->user->checkPermission( 'comeback' );

		if( !$pass ){
			if( yaf\Application::app()->controller->isAjax() ){
				throw new Exception( '登录时间超时，请重新登录！' );
			}
			throw new Exception( '没有权限', 403 );
			exit();
		}

	}

	function indexAction(){

		list ( $comeback_list, $pager_product ) = ComebackModel::getList ( $this->get ( 'pp' ) );
		$comeback_list;

		if( $this->user->role_id == UserModel::ROLE_FCG ){

			$this->comeback_list = $comeback_list;
			$this->show( 'index' );
		}else{

			$ids = array();
			foreach( $comeback_list as $one ){
				$ids[] = $one['id'];
			}

			$product_list = array();
			if( $ids ){

				if( $product_list_tmp = R::getAll( 'select * from comeback_product where comeback_id in ( '. implode( ', ', $ids ) .' ) and deleted is null' ) ){
					foreach( $product_list_tmp as $one ){
						!isset( $product_list[$one['comeback_id']] ) && $product_list[$one['comeback_id']] = array();
						$product_list[$one['comeback_id']][] = $one;
					}
				}
			}

			foreach( $comeback_list as $key=>$one ){

				$product_list_show = array();
				if( isset( $product_list[$one['id']] ) ){
					foreach( $product_list[$one['id']] as $one1 ){
						$product_list_show[] = CategoryModel::getName( $one1['category_id'] ). ' - '. ProductModel::getName( $one1['product_id'] ). ' - '. $one1['cnt'];
					}
				}
				$one['product_list'] = implode( '<br/>', $product_list_show );

				$comeback_list[$key] = $one;
			}
			$this->comeback_list = $comeback_list;
			$this->show( 'monitor' );
		}
	}
	
	function comebackAction() {

		$request = $this->getRequest ();
		
		if( $request->isPost() && ($action = $this->post( 'action' ) ) ){
			
			$id = $this->post ( 'id' );
			if (! $model = R::findOne ( 'comeback', 'id = ? and deleted is null', array (
				$id 
			) )) {
				throw new Exception ( 'model not found' );
			}
			switch( $action ){
				case 'publish':
					$model->state = ComebackModel::STATE_PUBLISHED;
					R::store( $model );
					break;
				default: 
					throw new Exception( 'no action' );
			}
		}else{
			
			if ($request->isPut ()) {
				$id = $this->put ( 'id' );
				if (! $model = R::findOne ( 'comeback', 'id = ? and deleted is null', array (
					$id 
				) )) {
					throw new Exception ( 'model not found' );
				}

				R::begin();
				try{

					$model->result = $this->put ( 'result' );
					$model->comment = $this->put ( 'comment' );
					$model->mail_num = $this->put ( 'mail_num' );
					$model->create_uid = $this->user->id;
					$model->updated = Helper\Html::now();

					$id = R::store ( $model );

					$this->updateProduct( $model, $request->getPut() );

					R::commit();

				}catch( Exception $e ){
					R::rollback();
					throw $e;
				}

			} elseif ($request->isPost ()) {

				R::begin();
				try{

					$model = R::dispense ( 'comeback' );
					$model->result = $this->post ( 'result' );
					$model->mail_num = $this->post ( 'mail_num' );
					$model->comment = $this->post ( 'comment' );
					$model->created = Helper\Html::now();
					$model->updated = Helper\Html::now();
					$model->create_uid = $this->user->id;
					$model->state = ComebackModel::STATE_DRAFT;

					$id = R::store ( $model );

					$this->updateProduct( $model, $request->getPost() );

					R::commit();

				}catch( Exception $e ){
					R::rollback();
					throw $e;
				}


			} elseif ($request->isDelete ()) {
				$id = $this->get ( 'id' );
				$model = R::load ( 'comeback', $id );
				$model->deleted = Helper\Html::now();
				R::store( $model );
				return $this->renderJson ( array (
					'error' => 0 
				) );
			} else {
				$id = $this->get ( 'id' );
				$model = R::load ( 'comeback', $id );
			}
		}
		
		$model->created = Helper\Html::date( $model->created );

		$comeback_product_list = array();
		if( $model_list = $model->withCondition( 'deleted is null' )->ownComebackProduct ){

			foreach( $model_list as $one ){
				switch( $one['type'] ){
					default:
						$comeback_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
							'state_name'=>ComebackProductModel::getStateName( $one->state ), 
						));
						break;
				}
			}
		}

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'create_uname' => $model->create_uid ? UserModel::getName( $model->create_uid ) : 'admin', 
			'comeback_product_list' => $comeback_product_list, 
		) ) );
	}

	public function updateProduct( $model, $arr ){

		$comeback_product_list_tmp = $model->withCondition( 'deleted is null' )->ownComebackProduct;
		$comeback_product_list = array();
		foreach( $comeback_product_list_tmp as $one ){
			$comeback_product_list[$one['id']] = $one;
		}
		if( isset( $arr['comeback_product'] ) && $arr['comeback_product'] ){

			foreach( $arr['comeback_product'] as $one ){

				if( !$one )continue;

				if( !isset( $comeback_product_list[$one['id']] ) ){

					$comeback_product = R::dispense( 'comeback_product' );
					$comeback_product->comeback_id = $model->id;
					$comeback_product->category_id = $one['category_id'];
					$comeback_product->product_id = $one['product_id'];
					$comeback_product->cnt = $one['cnt'];
					$comeback_product->created = Helper\Html::now();
					$comeback_product->updated = Helper\Html::now();
					$comeback_product->state = $one['state'];
					$model->ownComebackProduct[] = $comeback_product;

				}else{

					$comeback_product = $comeback_product_list[$one['id']];
					$comeback_product->updated = Helper\Html::now();
					$comeback_product->cnt = $one['cnt'];
					$comeback_product->state = $one['state'];

					unset( $comeback_product_list[$one['id']] );
				}
			}
		}

		foreach( $comeback_product_list as $one ){
			$one->deleted = Helper\Html::now();
			R::store( $one );
		}
		R::store( $model );
	}


	function searchAction(){
		if( !$key = $this->get( 'key' ) ){
			// throw new Exception( '没有搜索条件', 412 );
		}

		list ( $comeback_list, $pager_product ) = ComebackModel::getList ( 1, 9999, $key );

		if( $this->user->role_id == UserModel::ROLE_FCG ){

			$this->comeback_list = $comeback_list;
			echo $this->renderPartial( 'comeback/_search' );
		}else{

			$ids = array();
			foreach( $comeback_list as $one ){
				$ids[] = $one['id'];
			}

			$product_list = array();
			if( $ids ){

				if( $product_list_tmp = R::getAll( 'select * from comeback_product where comeback_id in ( '. implode( ', ', $ids ) .' ) and deleted is null' ) ){
					foreach( $product_list_tmp as $one ){
						!isset( $product_list[$one['comeback_id']] ) && $product_list[$one['comeback_id']] = array();
						$product_list[$one['comeback_id']][] = $one;
					}
				}
			}

			foreach( $comeback_list as $key=>$one ){

				$product_list_show = array();
				if( isset( $product_list[$one['id']] ) ){
					foreach( $product_list[$one['id']] as $one1 ){
						$product_list_show[] = CategoryModel::getName( $one1['category_id'] ). ' - '. ProductModel::getName( $one1['product_id'] ). ' - '. $one1['cnt'];
					}
				}
				$one['product_list'] = implode( '<br/>', $product_list_show );

				$comeback_list[$key] = $one;
			}
			$this->comeback_list = $comeback_list;
			echo $this->renderPartial( 'comeback/_search_monitor' );
		}
	}
}

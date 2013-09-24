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


		if( $this->user->role_id == UserModel::ROLE_FCG ){

			$this->show( 'index' );
		}else{

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
					$model->mail_company = $this->put ( 'mail_company' );
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
					$model->mail_company = $this->post ( 'mail_company' );
					$model->comment = $this->post ( 'comment' );
					$model->created = Helper\Html::now();
					$model->updated = Helper\Html::now();
					$model->create_uid = $this->user->id;
					$model->state = ComebackModel::STATE_NORMAL;

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
		
		$this->renderModel( $model );
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
		$key = $this->get( 'key' );
		$fcg = $this->get( 'fcg' );
		$state = $this->get( 'state' );
		$type = $this->get( 'type', 'tuihuo' );

		if( $this->user->role_id == UserModel::ROLE_FCG ){

			list ( $comeback_list, $pager_comeback ) = ComebackModel::getList ( 1, 9999, $key );

			$this->comeback_list = $comeback_list;
			echo $this->renderPartial( 'comeback/_search' );
		}else{

			list ( $comeback_list, $pager_comeback ) = ComebackModel::getList ( 1, 9999, $key, $fcg, $state );

			switch( $type ){
				case 'tuihuo':

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
								$product_list_show[] = CategoryModel::getName( $one1['category_id'] ). ' - '. 
									ProductModel::getName( $one1['product_id'] ). ' - '. 
									$one1['cnt']. ' - '. ComebackProductModel::getStateName( $one1['state'] );
							}
						}
						$one['product_list'] = implode( '<br/>', $product_list_show );

						$comeback_list[$key] = $one;
					}
					$this->comeback_list = $comeback_list;
					echo $this->renderPartial( 'comeback/_search_monitor' );

					break;
				case 'fanchan':

					$params = array();
					$sqlFcg = '';
					if( $fcg ){
						$sqlFcg = ' and c.create_uid = ? ';
						$params[] = $fcg;
					}

					$sql = 'select cp.category_id as category, cp.product_id as product, pc.name as category_name, pp.name as product_name, sum( cp.cnt ) as cnt from comeback c 
							inner join comeback_product cp on c.id = cp.comeback_id and cp.deleted is null and cp.state = '. ComebackProductModel::STATE_DEALWITH .'
							inner join category pc on cp.category_id = pc.id
							inner join product pp on cp.product_id = pp.id
						where c.state = '. ComebackModel::STATE_DONE .' and c.deleted is null '. $sqlFcg .'
						group by cp.category_id, cp.product_id';
					$this->fanchan_list = R::getAll( $sql, $params );

					echo $this->renderPartial( 'comeback/_search_monitor_fanchan' );

					break;
			}

		}
	}

	function dealwithAction(){
		if( !($id = $this->post( 'id' )) or !( $model = R::findOne( 'comeback', 'id = ?', array( $id ) ) ) ){
			throw new Exception( 'no id or no comeback', 412 );
		}

		$model->state = ComebackModel::STATE_DONE;
		$model->updated = Helper\Html::now();
		R::store( $model );

		foreach( $model->ownComebackProduct as $one ){
			if( $one->state == ComebackProductModel::STATE_NEW ){

				$one->state = ComebackProductModel::STATE_DEALWITH;
				$one->updated = Helper\Html::now();
				R::store( $one );
			}
		}

		$this->renderModel( $model );
	}

	function fanchanAction(){
		if( !($category = $this->post( 'category' )) or !($product = $this->post( 'product' )) ){
			throw new Exception( 'no category or no product', 412 );
		}

		R::begin();
		try{

			$sql = 'select cp.id, c.id as comeback_id from comeback c 
					inner join comeback_product cp on c.id = cp.comeback_id 
				where c.state = '. ComebackModel::STATE_DONE .' and cp.category_id = ? and cp.product_id = ? 
					and cp.state = '. ComebackProductModel::STATE_DEALWITH;
			$list = R::getAll( $sql, array(
				$category, 
				$product, 
			) );

			$ids_comeback = array();
			$ids_comeback_product = array();
			if( $list ){

				foreach( $list as $one ){
					$ids_comeback_product[] = $one['id'];
					$ids_comeback[] = $one['comeback_id'];
				}

				$sql = 'update comeback_product set state = '. ComebackProductModel::STATE_DONE. ', 
					updated = \''. Helper\Html::now() .'\'
					where id in ('. implode( ', ', $ids_comeback_product ) .') ';
				$rs = R::exec( $sql );

				$sql = 'select c.id from comeback c left join ( 
							select c.id from comeback c inner join comeback_product cp on 
								c.id = cp.comeback_id and ( cp.state = '. ComebackProductModel::STATE_DEALWITH .' or cp.state = '. ComebackProductModel::STATE_NEW .' ) and cp.deleted is null 
							where c.id in ( '. implode( ', ', $ids_comeback ) .' ) group by c.id
						)t on c.id = t.id where t.id is null and c.state = '. ComebackModel::STATE_DONE .' and 
							c.deleted is null and 
							c.id in ( '. implode( ', ', $ids_comeback ) .' )';
				if( $ids_comeback_done = R::getCol( $sql ) ){
					$sql = 'update comeback set state = '. ComebackModel::STATE_BACK. ', 
					updated = \''. Helper\Html::now() .'\'
					where id in ('. implode( ', ', $ids_comeback_done ) .') ';
					R::exec( $sql );
				}

			}

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderJson(array('err'=>0));
	}


	private function renderModel( $model ){

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

		$product_list_show = array();
		foreach( $comeback_product_list as $key=>$one ){
			$product_list_show[] = $one['category']. ' - '. $one['product']. ' - '. $one['cnt']. ' - '. $one['state_name'];
		}

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'comment'=> nl2br( $model->comment ), 
			'state_name'=>ComebackModel::getStateName( $model->state ), 
			'create_uname' => $model->create_uid ? UserModel::getName( $model->create_uid ) : 'admin', 
			'comeback_product_list' => $comeback_product_list, 
			'product_list_show' => implode( '<br/>', $product_list_show ), 
		) ) );
	}
}

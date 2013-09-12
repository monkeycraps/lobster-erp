<?php
class MissionController extends ApplicationController {
	
	protected $layout = 'frontend';

	function init(){

		parent::init();
		$this->user->checkPermission( 'mission' );

	}

	public function indexAction() {

		$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting' );
		$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing' );
		$this->done_list = MissionModel::getList( $this->user->id, 'done' );
		$this->closed_list = MissionModel::getList( $this->user->id, 'closed' );
		$this->show( 'index' );
	}

	function searchAction(){

		$key = intval( $this->get( 'key' ) );

		$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting', $key );
		$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing', $key );
		$this->done_list = MissionModel::getList( $this->user->id, 'done', $key );
		$this->closed_list = MissionModel::getList( $this->user->id, 'closed', $key );

		if( $key ){
			echo $this->renderPartial( 'mission/search' );
		}else{
			echo $this->renderPartial( 'mission/list' );
		}
	}

	public function formAction() {
		
		$cate_id = $this->get( 'cate' );
		$sub_cate_id = $this->get( 'subcate' );

		echo $this->renderPartial( 'mission/form-'. $cate_id. '-'. $sub_cate_id );
	}
	
	function missionAction(){
		$request = $this->getRequest ();
		
		if( ($request->isPut() || $request->isPost() ) && ($action = $this->post( 'action' ) ? $this->post( 'action' ) : $this->put( 'action' ) ) ){
			
			$id = $this->post ( 'id' ) ? $this->post ( 'id' ) : $this->put ( 'id' );
			if (! $model = R::findOne ( 'mission', 'id = ?', array (
				$id 
			) )) {
				throw new Exception ( 'model not found' );
			}
			switch( $action ){
				case 'close':
					$model->closeMission();
				break;
				default: 
					throw new Exception( 'no action' );
			}
		}else{
			
			if ($request->isPut ()) {
				$id = $this->put ( 'id' );
				if (! $model = R::findOne ( 'mission', 'id = ?', array (
					$id 
				) )) {
					throw new Exception ( 'model not found' );
				}
				$model->updateMission( $request->getPut() );

			} elseif ($request->isPost ()) {

				debug( $request->getPost() );
				$model = MissionModel::createMission( $request->getPost() );

			} elseif ($request->isDelete ()) {
				// $id = $this->get ( 'id' );
				// $model = R::load ( 'mission', $id );
				// $model->state = 102;
				// // R::store( $model );
				// return $this->renderJson ( array (
				// 	'error' => 0 
				// ) );
			} else {
				$id = $this->get ( 'id' );
				if( (!$id = $this->get ( 'id' )) or (!$model = R::findOne ( 'mission', 'id = ?', array( $id ) )) ){
					throw new Exception( 'no mission id', 404 );
				}
			}
		}
		
		$model->created = Helper\Html::date( $model->created );
		$model->logined = Helper\Html::date( $model->logined );

		$order_num_list = '';
		if( $model_list = $model->withCondition( 'deleted is null' )->ownMissionOrder ){

			$arr = array();
			foreach( $model_list as $one ){
				$arr[] = $one['order_num'];
			}
			$order_num_list = implode( ' ', $arr );
		}

		$ext = array();
		if( $model_list = $model->ownMissionExt ){

			$model_ext = current($model_list);
			$ext = array_merge( array(
				'ext1'=>$model_ext->ext1, 
				'ext2'=>$model_ext->ext2, 
				'ext3'=>$model_ext->ext3, 
			), json_decode( $model_ext->other, true ) );
		}

		$send_back_product_list = array();
		$send_to_product_list = array();
		$send_product_list = array();
		if( $model_list = $model->withCondition( 'deleted is null' )->ownMissionProduct ){

			foreach( $model_list as $one ){
				switch( $one['type'] ){
					case MissionProductModel::TYPE_BACK:
						$send_back_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
						));
						break;
					case MissionProductModel::TYPE_TO:
						$send_to_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
						));
						break;
					default: 
						$send_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
						));
						break;
				}
			}
		}

		$arr = R::getRow( 'select m.id, sub.id as sub_id, m.name, sub.name as sub_name from mission_type m inner join mission_type sub on m.id = sub.pid where sub.id = ? ', array( $model->mission_type_id ) );
		list( $category_id, $sub_category_id, $category, $sub_category ) = array_values($arr);

		$store = StoreModel::getName( $model->store_id );

		$user_state = R::getCell( 'select state from mission_user where uid = ? and mission_id = ? ', array( $this->user->id, $model->id ) );
		$kf_state = R::getCell( 'select state from mission_user where uid = ? and mission_id = ? ', array( $model->kf_uid, $model->id ) );
		$cg_state = R::getCell( 'select state from mission_user where uid = ? and mission_id = ? ', array( $model->cg_uid, $model->id ) );

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'store' => $store, 
			'category_id' => $category_id, 
			'category' => $category, 
			'sub_category_id' => $sub_category_id, 
			'sub_category' => $sub_category, 
			'order_num_list' => $order_num_list, 
			'send_back_product_list' => $send_back_product_list, 
			'send_to_product_list' => $send_to_product_list, 
			'send_product_list' => $send_product_list, 
			'user_state' => $user_state, 
			'user_state_name' => MissionUserModel::getStateName( $user_state ), 
			'mission_state' => MissionModel::getMissionStateName( $model->getIterator()->getArrayCopy() ), 
			'create_uname' => UserModel::getName( $model->create_uid ), 
			'kf_uname' => UserModel::getName( $model->kf_uid ), 
			'kf_state' => $kf_state, 
			'kf_state_name' => MissionUserModel::getStateName( $kf_state ), 
			'cg_state' => $cg_state, 
			'cg_state_name' => MissionUserModel::getStateName( $cg_state ), 
		), $ext ) );
	}

	function historyAction(){
		$id = $this->get( 'id' );
		if( !$mission = R::findOne( 'mission', 'id = ?', array( $id ) ) ){
			throw new Exception( 'no mission', 404 );
		}

		$this->model = $mission;
		$arr = R::getRow( 'select m.id, sub.id as sub_id, m.name, sub.name as sub_name from mission_type m inner join mission_type sub on m.id = sub.pid where sub.id = ? ', array( $mission->mission_type_id ) );
		list( $category_id, $sub_category_id, $category, $sub_category ) = array_values($arr);

		$this->category = $category;
		$this->sub_category = $sub_category;

		$this->created = Helper\Html::date( $mission->created );
		$this->create_uname = UserModel::getName( $mission->create_uid );
		$this->kf_uname = UserModel::getName( $mission->kf_uid );

		$this->history = R::getAll( 'select uid, changed, created from mission_change_log where mission_id = ? order by updated desc ', array( $id ) );
		echo $this->renderPartial( 'mission/history' );
	}
}







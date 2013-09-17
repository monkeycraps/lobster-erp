<?php
class MissionController extends ApplicationController {
	
	protected $layout = 'frontend';

	function init(){

		parent::init();
		$this->user->checkPermission( 'mission' );

	}

	public function indexAction() {

		switch( $this->user->role_id ){
			case UserModel::ROLE_DZ:
				$this->waiting_list_drawback = MissionModel::getList( $this->user->id, 'waiting_drawback' );
				$this->waiting_list_refundment = MissionModel::getList( $this->user->id, 'waiting_refundment' );
				$this->unclosed_list = MissionModel::getList( $this->user->id, 'dz_unclosed' );
				$this->closed_list = MissionModel::getList( $this->user->id, 'dz_closed' );
				$this->show( 'index' );
				break;	
			case UserModel::ROLE_FCG:
				$this->show( 'index' );
				break;
			default: 
				$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting' );
				$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing' );
				$this->done_list = MissionModel::getList( $this->user->id, 'done' );
				$this->closed_list = MissionModel::getList( $this->user->id, 'closed' );
				$this->show( 'index' );
				break;
		}
	}

	function reloadAction(){

		$id = $this->get( 'search' );

		switch( $this->user->role_id ){
			case UserModel::ROLE_DZ:
				$this->waiting_list_drawback = MissionModel::getList( $this->user->id, 'waiting_drawback' );
				$this->waiting_list_refundment = MissionModel::getList( $this->user->id, 'waiting_refundment' );
				$this->unclosed_list = MissionModel::getList( $this->user->id, 'dz_unclosed' );
				$this->closed_list = MissionModel::getList( $this->user->id, 'dz_closed' );
				echo $this->renderPartial( 'mission/list' );
				break;	
			case UserModel::ROLE_FCG:
				$this->show( 'index' );
				break;
			default: 
				$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting' );
				$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing' );
				$this->done_list = MissionModel::getList( $this->user->id, 'done' );
				$this->closed_list = MissionModel::getList( $this->user->id, 'closed' );
				echo $this->renderPartial( 'mission/list' );
				break;
		}
	}

	function searchAction(){

		$id = intval( $this->get( 'id' ) );
		$order_num = $this->get( 'order_num' );
		$wanwan = $this->get( 'wanwan' );

		if( $id ){

			switch( $this->user->role_id ){
				case UserModel::ROLE_DZ:
					$this->waiting_list_drawback = MissionModel::getList( $this->user->id, 'waiting_drawback', array( 'id'=>$id ) );
					$this->waiting_list_refundment = MissionModel::getList( $this->user->id, 'waiting_refundment', array( 'id'=>$id ) );
					$this->unclosed_list = MissionModel::getList( $this->user->id, 'dz_unclosed', array( 'id'=>$id ) );
					$this->closed_list = MissionModel::getList( $this->user->id, 'dz_closed', array( 'id'=>$id ) );
					echo $this->renderPartial( 'mission/search' );
					break;	
				case UserModel::ROLE_FCG:
					$this->show( 'index' );
					break;
				default: 
					$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting', array( 'id'=>$id ) );
					$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing', array( 'id'=>$id ) );
					$this->done_list = MissionModel::getList( $this->user->id, 'done', array( 'id'=>$id ) );
					$this->closed_list = MissionModel::getList( $this->user->id, 'closed', array( 'id'=>$id ) );
					echo $this->renderPartial( 'mission/search' );
					break;
			}

		}elseif( $order_num or $wanwan ){

			switch( $this->user->role_id ){
				case UserModel::ROLE_DZ:
					$this->waiting_list_drawback = MissionModel::getList( $this->user->id, 'waiting_drawback', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					$this->waiting_list_refundment = MissionModel::getList( $this->user->id, 'waiting_refundment', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					$this->unclosed_list = MissionModel::getList( $this->user->id, 'dz_unclosed', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					$this->closed_list = MissionModel::getList( $this->user->id, 'dz_closed', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					echo $this->renderPartial( 'mission/search' );
					break;	
				case UserModel::ROLE_FCG:
					$this->show( 'index' );
					break;
				default: 
					$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					$this->done_list = MissionModel::getList( $this->user->id, 'done', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					$this->closed_list = MissionModel::getList( $this->user->id, 'closed', array( 'order_num'=>$order_num, 'wanwan'=>$wanwan ) );
					echo $this->renderPartial( 'mission/search' );
					break;
			}

		}else{

			switch( $this->user->role_id ){
				case UserModel::ROLE_DZ:
					$this->waiting_list_drawback = MissionModel::getList( $this->user->id, 'waiting_drawback' );
					$this->waiting_list_refundment = MissionModel::getList( $this->user->id, 'waiting_refundment' );
					$this->unclosed_list = MissionModel::getList( $this->user->id, 'dz_unclosed' );
					$this->closed_list = MissionModel::getList( $this->user->id, 'dz_closed' );
					echo $this->renderPartial( 'mission/list' );
					break;	
				case UserModel::ROLE_FCG:
					$this->show( 'index' );
					break;
				default: 
					$this->waiting_list = MissionModel::getList( $this->user->id, 'waiting' );
					$this->dealing_list = MissionModel::getList( $this->user->id, 'dealing' );
					$this->done_list = MissionModel::getList( $this->user->id, 'done' );
					$this->closed_list = MissionModel::getList( $this->user->id, 'closed' );
					echo $this->renderPartial( 'mission/list' );
					break;
			}
		}
	}

	public function formAction() {
		
		$cate_id = $this->get( 'cate' );
		$sub_cate_id = $this->get( 'subcate' );
		$this->form_name = MissionTypeModel::getName( $cate_id ) . ' - '. 
					MissionTypeModel::getName( $sub_cate_id );

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
		
		$this->renderMission( $model );
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


		$arr = array();
		$arr[$mission->id] = R::getAll( 'select uid, changed, created from mission_change_log where mission_id = ? order by updated desc ', array( $mission->id ) );

		while( $mission->pid != 0 ){
			$mission = R::findOne( 'mission', 'id = ?', array( $mission->pid ) );
			$arr[$mission->id] = R::getAll( 'select uid, changed, created from mission_change_log where mission_id = ? order by updated desc ', array( $mission->id ) );
		}

		$this->history = $arr;

		echo $this->renderPartial( 'mission/history' );
	}

	function rukuAction(){

		if( !($id = $this->post( 'id' )) or !( $model = R::findOne( 'mission_product', 'id = ?', array( $id ) ) )  ){
			throw new Exception( 'mo mission product yet', 404 );
		}

		$model->state = MissionProductModel::STATE_DONE;
		R::store( $model );

		$this->renderJson( array_merge( $model->getIterator()->getArrayCopy(), array(
			'category'=>CategoryModel::getName( $model->category_id ), 
			'product'=>ProductModel::getName( $model->product_id ), 
			'state_name'=>MissionProductModel::getStateName( $model->state ), 
		) ) );
	}

	function rukuCancelAction(){

		if( !($id = $this->post( 'id' )) or !( $model = R::findOne( 'mission_product', 'id = ?', array( $id ) ) )  ){
			throw new Exception( 'mo mission product yet', 404 );
		}

		$model->state = MissionProductModel::STATE_NEW;
		R::store( $model );

		$this->renderJson( array_merge( $model->getIterator()->getArrayCopy(), array(
			'category'=>CategoryModel::getName( $model->category_id ), 
			'product'=>ProductModel::getName( $model->product_id ), 
			'state_name'=>MissionProductModel::getStateName( $model->state ), 
		) ) );
	}

	function flagAction(){

		if( !($id = $this->post( 'id' )) or !( $mission = R::findOne( 'mission', 'id = ?', array( $id ) ) )  ){
			throw new Exception( 'mo mission yet', 404 );
		}

		if( $this->user->role_id != UserModel::ROLE_DZ ){
			throw new Exception( '店长才能插旗', 403 );
		}

		$flag = $this->post( 'flag' );
		!is_array( $flag ) && $flag = array();
		$remarks = $this->post( 'remarks' );

		if( !$mission_flag = R::findOne( 'mission_flag', 'mission_id = ?', array( $mission->id ) ) ){
			$mission_flag = R::dispense( 'mission_flag' );
			$mission_flag->created = Helper\Html::now();
			$mission_flag->mission_id = $mission->id;
		}
		$mission_flag->dz_uid = $this->user->id;
		$mission_flag->kf_uid = $mission->kf_uid;
		$mission_flag->cg_uid = $mission->cg_uid;
		$mission_flag->updated = Helper\Html::now();
		$mission_flag->kf_mistake = in_array( MissionFlagModel::MIST_KF, $flag ) ? 1 : 0;
		$mission_flag->cg_mistake = in_array( MissionFlagModel::MIST_CG, $flag ) ? 1 : 0;
		$mission_flag->ck_mistake = in_array( MissionFlagModel::MIST_CK, $flag ) ? 1 : 0;
		$mission_flag->other_mistake = in_array( MissionFlagModel::MIST_OTHER, $flag ) ? 1 : 0;
		$mission_flag->remarks = $remarks;

		R::store( $mission_flag );

		$this->renderJson( array(
			'flag_list'=>$flag, 
			'flag_remarks'=>$remarks, 
		) );
	}

	function changeTypeAction(){

		if( !( $id = $this->post( 'id' ) ) || !( $mission = R::findOne( 'mission', 'id = ?', array( $id ) ) ) ){
			throw new Exception( 'no mission', 412 );
		}

		if( !( $mission_type = $this->post( 'mission_type' ) ) ){
			throw new Exception( 'no mission_type', 412 );
		}

		R::begin();
		try{

			$mission->last_uid = $this->user->id;
			$mission->last_uname = $this->user->name;

			R::store( $mission );
			
			$mission_new = $mission->copyMission( $mission_type );

			$mission->state = MissionModel::STATE_TO_OTHER;
			R::store( $mission );

			if( $mission->cg_uid ){

				$title = $mission->id. ' - '. $this->user->name. ' 更改了任务类型';
				$content = 
					$mission->id. ' - '. 
					MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. 
					MissionTypeModel::getName( $mission->mission_type_id ). ' - '. 
					$mission->wanwan;
				$this->user->message->send( $mission->cg_uid, $title, $content, $mission->id );
			}


			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderJson(array(
			'id'=>$mission_new->id
		));
	}

	function changeKefuAction(){
		
		if( !( $id = $this->post( 'id' ) ) || !( $mission = R::findOne( 'mission', 'id = ?', array( $id ) ) ) ){
			throw new Exception( 'no mission', 412 );
		}

		if( !( $uid = $this->post( 'uid' ) ) || !( $new_user = R::findOne( 'user', 'id = ?', array( $uid ) ) ) ){
			throw new Exception( 'no user', 412 );
		}

		R::begin();
		try{

			$user = $this->user;
			$mission->last_uid = $user->id;
			$mission->last_uname = $user->name;
			$mission->kf_uid = $new_user->id;
			R::store( $mission );

			if( !$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $mission->id, $user->id ) )){
				throw new Exception( 'no mission user', 412 );
			}
			$state_before = $mission_user->state;
			$mission_user->state = MissionUserModel::STATE_TO_OTHER;
			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			if( !$mission_user_new = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $mission->id, $new_user->id ) ) ){

				$mission_user_new = R::dispense( 'mission_user' );
				$mission_user_new->mission_id = $mission->id;
				$mission_user_new->uid = $new_user->id;
				$mission_user_new->created = Helper\Html::now();
			}
			$mission_user_new->state = $state_before;
			$mission_user_new->updated = Helper\Html::now();
			R::store( $mission_user_new );

			$title = $mission->id. ' - '. $this->user->name. ' 转了个任务给你';
			$content = 
				$mission->id. ' - '. 
				MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. 
				MissionTypeModel::getName( $mission->mission_type_id ). ' - '. 
				$mission->wanwan;
			$this->user->message->send( $new_user->id, $title, $content, $mission->id );

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

	}

	function reopenAction(){
		if( !( $id = $this->post( 'id' ) ) or !( $mission = R::findOne( 'mission', 'id = ? ', array( $id ) ) ) ){
			throw new Exception( 'no mission', 412 );
		}

		R::begin();

		try{

			$action = $this->post( 'action' );
			switch( $action ){
				case 'kf':
					$uid = $mission->cg_uid;
					$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $mission->id, $uid ) );
					$mission_user->state = MissionUserModel::STATE_WAITING;
					R::store( $mission_user );

					break;
				case 'cg':

					$uid = $mission->kf_uid;
					$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $mission->id, $uid ) );
					$mission_user->state = MissionUserModel::STATE_WAITING;
					R::store( $mission_user );

					break;
				case 'dz':
					throw new Exception( 'invalid action', 412 );
					break;
				default:
					throw new Exception( 'invalid action', 412 );
					break;
			}

			$title = $mission->id. ' - '. $this->user->name. ' 重新打开了任务。';
			$content = 
				$mission->id. ' - '. 
				MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. 
				MissionTypeModel::getName( $mission->mission_type_id ). ' - '. 
				$mission->wanwan;
			$this->user->message->send( $uid, $title, $content, $mission->id );

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderMission( $mission );
	}

	function renderMission( $model ){

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
		$send_old_product_list = array();
		if( $model_list = $model->withCondition( 'deleted is null' )->ownMissionProduct ){

			foreach( $model_list as $one ){
				switch( $one['type'] ){
					case MissionProductModel::TYPE_BACK:
						$send_back_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
							'state_name'=>MissionProductModel::getStateName( $one->state ), 
						));
						break;
					case MissionProductModel::TYPE_TO:
						$send_to_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
							'state_name'=>MissionProductModel::getStateName( $one->state ), 
						));
						break;
					case MissionProductModel::TYPE_OLD:
						$send_old_product_list[] = array_merge( $one->getIterator ()->getArrayCopy (), array(
							'category'=>CategoryModel::getName( $one->category_id ), 
							'product'=>ProductModel::getName( $one->product_id ), 
							'state_name'=>MissionProductModel::getStateName( $one->state ), 
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

		if( !$drawback = R::findOne( 'mission_drawback', 'mission_id = ? and deleted is null ', array( $model->id ) ) ){
			$drawback = R::dispense( 'mission_drawback' );
		}

		if( !$refundment = R::findOne( 'mission_refundment', 'mission_id = ? and deleted is null ', array( $model->id ) ) ){
			$refundment = R::dispense( 'mission_refundment' );
		}

		if( !$mission_flag = R::findOne( 'mission_flag', 'mission_id = ? ', array( $model->id ) ) ){
			$mission_flag = R::dispense( 'mission_flag' );
		}
		$mission_flag_lisg = array();
		if( $mission_flag->kf_mistake )$mission_flag_lisg[] = MissionFlagModel::MIST_KF;
		if( $mission_flag->cg_mistake )$mission_flag_lisg[] = MissionFlagModel::MIST_CG;
		if( $mission_flag->ck_mistake )$mission_flag_lisg[] = MissionFlagModel::MIST_CK;
		if( $mission_flag->other_mistake )$mission_flag_lisg[] = MissionFlagModel::MIST_OTHER;

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'store' => $store, 
			'category_id' => $category_id, 
			'category' => $category, 
			'sub_category_id' => $sub_category_id, 
			'sub_category' => $sub_category, 
			'order_num_list' => $order_num_list, 
			'send_back_product_list' => $send_back_product_list, 
			'send_to_product_list' => $send_to_product_list, 
			'send_old_product_list' => $send_old_product_list, 
			'user_state' => $user_state, 
			'user_state_name' => MissionUserModel::getStateName( $user_state ), 
			'mission_state' => MissionModel::getMissionStateName( $model->getIterator()->getArrayCopy() ), 
			'create_uname' => UserModel::getName( $model->create_uid ), 
			'kf_uname' => UserModel::getName( $model->kf_uid ), 
			'kf_state' => $kf_state, 
			'kf_state_name' => MissionUserModel::getStateName( $kf_state ), 
			'cg_state' => $cg_state, 
			'cg_state_name' => MissionUserModel::getStateName( $cg_state ), 
			'drawback'=> $drawback->money, 
			'drawback_zhifubao'=> $drawback->zhifubao, 
			'drawback_reason'=> $drawback->reason, 
			'drawback_state'=> $drawback->state, 
			'drawback_state_name'=> MissionDrawbackModel::getStateName( $drawback->state ), 
			'refundment_state'=> $refundment->state, 
			'refundment_state_name'=> MissionRefundmentModel::getStateName( $refundment->state ), 
			'flag_list'=> $mission_flag_lisg, 
			'flag_remarks'=> $mission_flag->remarks, 
		), $ext ) );
	}


	function reloadCntAction(){
		$this->renderJson( $this->getListCnt() );
	}

	function getListCnt(){

		switch( $this->user->role_id ){
			case UserModel::ROLE_DZ:
				$waiting_list_drawback_cnt = MissionModel::getListCnt( $this->user->id, 'waiting_drawback' );
				$waiting_list_refundment_cnt = MissionModel::getListCnt( $this->user->id, 'waiting_refundment' );
				$unclosed_list_cnt = MissionModel::getListCnt( $this->user->id, 'dz_unclosed' );
				$closed_list_cnt = MissionModel::getListCnt( $this->user->id, 'dz_closed' );
				return array(
					MissionUserModel::STATE_WAITING_DRAWING => $waiting_list_drawback_cnt, 
					MissionUserModel::STATE_WAITING_REFUNDMENT => $waiting_list_refundment_cnt, 
					MissionUserModel::SHOW_STATE_DZ_CONCLOSED => $unclosed_list_cnt, 
					MissionUserModel::SHOW_STATE_DZ_CLOSED => $closed_list_cnt, 
				);
				break;	
			case UserModel::ROLE_FCG:
				return array();
				break;
			default: 
				$waiting_list_cnt = MissionModel::getListCnt( $this->user->id, 'waiting' );
				$dealing_list_cnt = MissionModel::getListCnt( $this->user->id, 'dealing' );
				$done_list_cnt = MissionModel::getListCnt( $this->user->id, 'done' );
				$closed_list_cnt = MissionModel::getListCnt( $this->user->id, 'closed' );
				return array(
					MissionUserModel::STATE_WAITING => $waiting_list_cnt, 
					MissionUserModel::STATE_DEALING => $dealing_list_cnt, 
					MissionUserModel::STATE_DONE => $done_list_cnt, 
					MissionUserModel::STATE_CLOSED => $closed_list_cnt, 
				);
				break;
		}
	}
}







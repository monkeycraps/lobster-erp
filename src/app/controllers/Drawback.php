<?php
class DrawbackController extends ApplicationController {

	public function applyAction() {
		
		if( !($id = $this->post( 'id' )) or 
			!( $drawback = R::findOne( 'mission_drawback', 'mission_id = ?', array( $id ) ) ) ){

			throw new Exception( 'mission drawback not found', 412 );
		}

		if( $drawback->state != MissionDrawbackModel::STATE_NEW ){
			throw new Exception( '任务不是等待中', 412 );
		}

		R::begin();
		try{

			$dz = RoleModel::getDZ( $this->user->id );
			$drawback->dz_uid = $dz->id;
			$drawback->updated = Helper\Html::now();
			$drawback->state = MissionDrawbackModel::STATE_APPLY;

			R::store( $drawback );

			if( !$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ? ', array( $drawback->mission_id, $dz->id ) ) ){
				$mission_user = R::dispense( 'mission_user' );
				$mission_user->mission_id = $drawback->mission_id;
				$mission_user->uid = $dz->id;
				$mission_user->created = Helper\Html::now();
			}

			$mission_user->state = MissionUserModel::STATE_WAITING_DRAWING;
			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			MissionChangeLogModel::saveChangeAction( $drawback->mission_id, 'drawback',  MissionDrawbackModel::STATE_APPLY );

			// $mission = R::findOne( 'mission', 'id = ?', array( $drawback->mission_id ) );
			// $title = $mission->id. ' - '. $this->user->name. '申请返现'. ' - '. MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
			// $content = 
			// 	'金额：'. $drawback->money .'<br/>'. 
			// 	'返现原因：'. $drawback->reason .'<br/>'. 
			// 	'发起客服：'. UserModel::getName($drawback->kf_uid) .'<br/>';
			// $this->user->message->send( $dz->id, $title, $content, $drawback->mission_id );

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderJson( array(
			'drawback'=> $drawback->money, 
			'drawback_zhifubao'=> $drawback->zhifubao, 
			'drawback_reason'=> $drawback->reason, 
			'drawback_state'=> $drawback->state, 
			'drawback_state_name'=> MissionDrawbackModel::getStateName( $drawback->state ), 
		) );
	}

	public function cancelAction() {
		
		if( !($id = $this->post( 'id' )) or 
			!( $drawback = R::findOne( 'mission_drawback', 'mission_id = ?', array( $id ) ) ) ){

			throw new Exception( 'mission drawback not found', 412 );
		}

		if( $drawback->state == MissionDrawbackModel::STATE_DONE ){
			throw new Exception( '店长已经处理。', 412 );
		}

		if( $drawback->state == MissionDrawbackModel::STATE_NEW ){
			throw new Exception( '之前已经取消了申请。请刷新表单。', 412 );
		}

		R::begin();
		try{

			$dz = RoleModel::getDZ( $this->user->id );
			$drawback->dz_uid = $dz->id;
			$drawback->updated = Helper\Html::now();
			$drawback->state = MissionDrawbackModel::STATE_NEW;

			R::store( $drawback );

			if( !$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ? ', array( $drawback->mission_id, $dz->id ) ) ){
				$mission_user = R::dispense( 'mission_user' );
				$mission_user->mission_id = $drawback->mission_id;
				$mission_user->uid = $dz->id;
				$mission_user->created = Helper\Html::now();
			}

			$mission_user->state = MissionUserModel::STATE_CANCEL;
			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			// $mission = R::findOne( 'mission', 'id = ?', array( $drawback->mission_id ) );
			// $title = $mission->id. ' - '. $this->user->name. '取消了返现申请'. ' - '. MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
			// $content = 
			// 	'金额：'. $drawback->money .'<br/>'. 
			// 	'返现原因：'. $drawback->reason .'<br/>'. 
			// 	'发起客服：'. UserModel::getName($drawback->kf_uid) .'<br/>';
			// $this->user->message->send( $dz->id, $title, $content, $drawback->mission_id );

			MissionChangeLogModel::saveChangeAction( $drawback->mission_id, 'drawback',  MissionDrawbackModel::STATE_NEW );

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderJson( array(
			'drawback'=> $drawback->money, 
			'drawback_zhifubao'=> $drawback->zhifubao, 
			'drawback_reason'=> $drawback->reason, 
			'drawback_state'=> $drawback->state, 
			'drawback_state_name'=> MissionDrawbackModel::getStateName( $drawback->state ), 
		) );
	}


	function batchAction(){

		if( !$this->user->role_id == UserModel::ROLE_DZ ){
			throw new Exception( '不是店长没有操作权限。', 412 );
		}

		if( !$ids = $this->post( 'batch' ) ){
			throw new Exception( 'no list', 412 );
		}

		$ids_sql = implode( ',', $ids );
		if( !preg_match( '/^[0-9|,]*$/', $ids_sql ) ){
			throw new Exception( 'invalid ids', 412 );
		}

		$error = 0;
		$msg = '返现成功';

		R::begin();
		try{
			$sql = 'update mission_drawback set state = '. MissionDrawbackModel::STATE_DONE . ' 
				where mission_id in ('. $ids_sql .') and state = '. MissionDrawbackModel::STATE_APPLY;
			$cnt = R::exec( $sql );
			if( $cnt != count( $ids ) ){
				throw new Exception( '有任务取消了返现，或者已经被处理，请刷新重试。', 2 );
			}

			$sql = 'update mission_user set state = '. MissionUserModel::STATE_DONE. ' 
				where mission_id in ( '. $ids_sql .' )  and uid = ? and 
				state = '. MissionUserModel::STATE_WAITING_DRAWING. ' ';
			$cnt = R::exec( $sql, array( $this->user->id ) );
			if( $cnt != count( $ids ) ){
				throw new Exception( '有任务取消了返现，或者已经被处理，请刷新重试。', 2 );
			}

			$sql = 'select * from mission_drawback d inner join mission m on d.mission_id = m.id where 
				m.id in ( '. $ids_sql .' )' ; 
			$list = R::getAll( $sql );


			foreach( $list as $one ){

				$title = $one['id']. ' - 店长 '. $this->user->name. ' 通过了返现申请';
				$content = 
					$one['id']. ' - '. 
					MissionTypeModel::getParentName( $one['mission_type_id'] ) . ' - '. 
					MissionTypeModel::getName( $one['mission_type_id'] ). ' - '. 
					$one['wanwan'];
				$this->user->message->send( $one['kf_uid'], $title, $content, $one['mission_id'] );

				MissionChangeLogModel::saveChangeAction( $one['id'], 'drawback',  MissionDrawbackModel::STATE_DONE );
			}

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			$error = $e->getCode() ? $e->getCode() : 1;
			$msg = $e->getMessage();
			debug( $e->getTraceAsString() );
		}

		$this->renderJson( array(
			'error'=> $error, 
			'msg' => $msg
		) );
	}

}
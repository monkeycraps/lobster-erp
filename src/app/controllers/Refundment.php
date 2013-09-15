<?php
class RefundmentController extends ApplicationController {

	public function applyAction() {
		
		if( !($id = $this->post( 'id' )) or 
			!( $refundment = R::findOne( 'mission_refundment', 'mission_id = ?', array( $id ) ) ) ){

			throw new Exception( 'mission refundment not found', 412 );
		}

		if( $refundment->state != MissionRefundmentModel::STATE_NEW ){
			throw new Exception( '任务不是等待中', 412 );
		}

		R::begin();
		try{

			$dz = RoleModel::getDZ( $this->user->id );
			$refundment->dz_uid = $dz->id;
			$refundment->updated = Helper\Html::now();
			$refundment->state = MissionRefundmentModel::STATE_APPLY;

			R::store( $refundment );

			if( !$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ? ', array( $refundment->mission_id, $dz->id ) ) ){
				$mission_user = R::dispense( 'mission_user' );
				$mission_user->mission_id = $refundment->mission_id;
				$mission_user->uid = $dz->id;
				$mission_user->created = Helper\Html::now();
			}

			$mission_user->state = MissionUserModel::STATE_WAITING_REFUNDMENT;
			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			$mission = R::findOne( 'mission', 'id = ?', array( $refundment->mission_id ) );
			$title = $mission->id. ' - '. $this->user->name. '申请退款'. ' - '. MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
			$content = '';
			$this->user->message->send( $dz->id, $title, $content, $refundment->mission_id );

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderJson( array(
			'refundment_state'=> $refundment->state, 
			'refundment_state_name'=> MissionRefundmentModel::getStateName( $refundment->state ), 
		) );
	}

	public function cancelAction() {
		
		if( !($id = $this->post( 'id' )) or 
			!( $refundment = R::findOne( 'mission_refundment', 'mission_id = ?', array( $id ) ) ) ){

			throw new Exception( 'mission refundment not found', 412 );
		}

		if( $refundment->state == MissionRefundmentModel::STATE_DONE ){
			throw new Exception( '店长已经处理。', 412 );
		}

		if( $refundment->state == MissionRefundmentModel::STATE_NEW ){
			throw new Exception( '之前已经取消了申请。请刷新表单。', 412 );
		}

		R::begin();
		try{

			$dz = RoleModel::getDZ( $this->user->id );
			$refundment->dz_uid = $dz->id;
			$refundment->updated = Helper\Html::now();
			$refundment->state = MissionRefundmentModel::STATE_NEW;

			R::store( $refundment );

			if( !$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ? ', array( $refundment->mission_id, $dz->id ) ) ){
				$mission_user = R::dispense( 'mission_user' );
				$mission_user->mission_id = $refundment->mission_id;
				$mission_user->uid = $dz->id;
				$mission_user->created = Helper\Html::now();
			}

			$mission_user->state = MissionUserModel::STATE_CANCEL;
			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			$mission = R::findOne( 'mission', 'id = ?', array( $refundment->mission_id ) );
			$title = $mission->id. ' - '. $this->user->name. '取消了退款申请'. ' - '. MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
			$content = '';
			$this->user->message->send( $dz->id, $title, $content, $refundment->mission_id );

			R::commit();
		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		$this->renderJson( array(
			'refundment_state'=> $refundment->state, 
			'refundment_state_name'=> MissionRefundmentModel::getStateName( $refundment->state ), 
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
		$msg = '退款成功';

		R::begin();
		try{
			$sql = 'update mission_refundment set state = '. MissionRefundmentModel::STATE_DONE . ' 
				where mission_id in ('. $ids_sql .') and state = '. MissionRefundmentModel::STATE_APPLY;
			$cnt = R::exec( $sql );
			if( $cnt != count( $ids ) ){
				throw new Exception( '有任务取消了退款，或者已经被处理，请刷新重试。', 2 );
			}

			$sql = 'update mission_user set state = '. MissionUserModel::STATE_DONE. ' 
				where mission_id in ( '. $ids_sql .' )  and uid = ? and 
				state = '. MissionUserModel::STATE_WAITING_REFUNDMENT. ' ';
			$cnt = R::exec( $sql, array( $this->user->id ) );
			if( $cnt != count( $ids ) ){
				throw new Exception( '有任务取消了退款，或者已经被处理，请刷新重试。', 2 );
			}

			$sql = 'select * from mission_refundment d inner join mission m on d.mission_id = m.id where 
				m.id in ( '. $ids_sql .' )' ; 
			$list = R::getAll( $sql );


			foreach( $list as $one ){
				$title = $one['id']. ' - '. $this->user->name. '通过了退款申请'. ' - '. 
					MissionTypeModel::getParentName( $one['mission_type_id'] ) . ' - '. 
					MissionTypeModel::getName( $one['mission_type_id'] );
				$content = '';
				$this->user->message->send( $this->user->id, $title, $content, $one['mission_id'] );
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
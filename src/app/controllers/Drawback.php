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

			$mission = R::findOne( 'mission', 'id = ?', array( $drawback->mission_id ) );
			$title = $mission->id. ' - ' $this->user->name. '申请返现'. ' - '. MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
			$content = 
				'金额：'. $drawback->money .'<br/>'. 
				'返现原因：'. $drawback->reason .'<br/>'. 
				'发起客服：'. UserModel::getName($drawback->kf_uid) .'<br/>';
			$this->user->message->send( $dz->id, $title, $content, $drawback->mission_id );

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

			$mission = R::findOne( 'mission', 'id = ?', array( $drawback->mission_id ) );
			$title = $mission->id. ' - ' $this->user->name. '取消了返现申请'. ' - '. MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
			$content = 
				'金额：'. $drawback->money .'<br/>'. 
				'返现原因：'. $drawback->reason .'<br/>'. 
				'发起客服：'. UserModel::getName($drawback->kf_uid) .'<br/>';
			$this->user->message->send( $dz->id, $title, $content, $drawback->mission_id );

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


}
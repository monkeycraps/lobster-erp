<?php

class MissionUserModel extends RedBean_SimpleModel {

	const STATE_DRAFT = 0;
	const STATE_WAITING = 1;
	const STATE_DEALING = 2;
	const STATE_DONE = 3;
	const STATE_CLOSED = 4;

	static function getStateName( $state ){
		switch( intval($state) ){
			case 0:
				return '草稿';
				break;
			case 1:
				return '待处理';
				break;
			case 2:
				return '处理中';
				break;
			case 3:
				return '已处理';
				break;
			case 4:
				return '已关闭';
				break;
			default:
				return '异常';
				break;
		}
	}

	function doneMission(){

		$user_role = R::getCell( 'select role_id from user where id = ? ', array( $this->uid ) );

		switch( $user_role ){
			case UserModel::ROLE_KF:
				$cg = R::findOne( 'user', 'role_id=?', array( UserModel::ROLE_CG ) );

				if( !$mission_user = R::findOne( 'mission_user', 'uid = ? and mission_id = ?', array( $cg->id, $this->mission_id ) ) ){
					$mission_user = R::dispense( 'mission_user' );
					$mission_user->mission_id = $this->mission_id;
					$mission_user->uid = $cg->id;
					$mission_user->created = Helper\Html::now();
				}
				$mission_user->updated = Helper\Html::now();
				$mission_user->state = self::STATE_WAITING;
				R::store( $mission_user );

				$mission = R::findOne( 'mission', 'id = ?', array( $this->mission_id ) );
				$mission->cg_uid = $cg->id;
				R::store( $mission );

				break;
			case UserModel::ROLE_CG:

				$kf_uid = R::getCell( ' select m.kf_uid from mission m
					where m.id = ?', array( $this->mission_id ) );

				if( !$mission_user = R::findOne( 'mission_user', 'uid = ? and mission_id = ?', array( $kf_uid, $this->mission_id ) ) ){
					throw new Exception( 'mission kefu not found.', 404 );
				}
				$mission_user->updated = Helper\Html::now();
				$mission_user->state = self::STATE_WAITING;
				R::store( $mission_user );

				break;
			default:
				break;
		}
	}
}

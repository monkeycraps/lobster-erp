<?php

class MissionUserModel extends RedBean_SimpleModel {

	const STATE_DRAFT = 0;
	const STATE_WAITING = 1;
	const STATE_DEALING = 2;
	const STATE_DONE = 3;
	const STATE_CLOSED = 4;
	const STATE_CANCEL = 5;
	const STATE_WAITING_DRAWING = 6;
	const STATE_WAITING_REFUNDMENT = 7;
	const SHOW_STATE_DZ_CONCLOSED = 8;
	const SHOW_STATE_DZ_CLOSED = 9;
	const STATE_TO_OTHER = 10;

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
			case 5:
				return '取消';
				break;
			case 6:
				return '等待返现';
				break;
			case 7:
				return '等待退款';
				break;
			case 10:
				return '转移给别人';
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
				$cg = RoleModel::getCG( $this->uid );

				$mission = R::findOne( 'mission', 'id = ?', array( $this->mission_id ) );
				
				if( !$mission_user = R::findOne( 'mission_user', 'uid = ? and mission_id = ?', array( $cg->id, $this->mission_id ) ) ){
					$mission_user = R::dispense( 'mission_user' );
					$mission_user->mission_id = $this->mission_id;
					$mission_user->uid = $cg->id;
					$mission_user->created = Helper\Html::now();

					$mission->is_new = 1;
					$mission->is_changed = 0;
				}else{
					if( $mission_user->state == self::STATE_CLOSED ){
						return;
					}
				}
				$mission_user->updated = Helper\Html::now();
				$mission_user->state = self::STATE_WAITING;
				R::store( $mission_user );

				$mission->cg_uid = $cg->id;
				R::store( $mission );

				// $controller = Yaf\Application::app()->controller;
				// if( $controller->post( 'do_publish' ) or $controller->put( 'do_publish' ) ){

				// 	$user = plugin( 'user' );
				// 	$title = $user->name. '发布了新任务：';
				// 	$content = MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. MissionTypeModel::getName( $mission->mission_type_id );
				// 	$order_num_list = $controller->post( 'order_num' ) ? $controller->post( 'order_num' ) : $controller->put( 'order_num' );
				// 	$content .="<br/>". $order_num_list;
				// 	plugin( 'user' )->message->send( $mission->cg_uid, $title, $content, $mission->id );
				// }

				break;
			case UserModel::ROLE_CG:

				$mission = R::findOne( 'mission', 'id = ?', array( $this->mission_id ) );

				$kf_uid = R::getCell( ' select m.kf_uid from mission m
					where m.id = ?', array( $this->mission_id ) );

				if( !$mission_user = R::findOne( 'mission_user', 'uid = ? and mission_id = ?', array( $kf_uid, $this->mission_id ) ) ){
					throw new Exception( 'mission kefu not found.', 404 );
				}
				$mission_user->updated = Helper\Html::now();
				$mission_user->state = self::STATE_WAITING;
				R::store( $mission_user );

				$user = plugin( 'user' );
				$title = $mission->id. ' - 仓管 '. $user->name. ' 完成任务';
				$content = 
					$mission->id. ' - '. 
					MissionTypeModel::getParentName( $mission->mission_type_id ) . ' - '. 
					MissionTypeModel::getName( $mission->mission_type_id ). ' - '. 
					$mission->wanwan;

				$user->message->send( $kf_uid, $title, $content, $this->mission_id );

				break;
			default:
				break;
		}
	}
}

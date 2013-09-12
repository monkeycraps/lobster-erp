<?php

class MissionModel extends RedBean_SimpleModel {

	const STATE_ON = 1;
	const STATE_CLOSED = 2;

	static function createMission( $arr ){

		$user = Yaf\Application::app()->user;

		R::begin();
		try{

			$model = R::dispense ( 'mission' );
			$model->mission_type_id = $arr['mission_type_id'];
			$model->created = Helper\Html::now();
			$model->updated = Helper\Html::now();
			$model->last_uid = $user->id;
			$model->last_uname = $user->name;
			$model->kf_uid = $user->id;
			$model->create_uid = $user->id;
			$model->store_id = $arr['store_id'];
			$model->wanwan = $arr['wanwan'];
			$model->state = self::STATE_ON;
			
			$model->state = 0;
			$id = R::store ( $model );

			$ext = MissionExtModel::setExt( $arr['mission_type_id'], $id, $arr );
			$model->ownMissionExt[] = $ext;

			$mission_user = R::dispense( 'mission_user' );
			$mission_user->mission_id = $id;
			$mission_user->uid = $user->id;
			$mission_user->state = $arr['user_state'];
			$mission_user->created = Helper\Html::now();
			$mission_user->updated = Helper\Html::now();
			$model->ownMissionUser[] = $mission_user;

			if( isset( $arr['order_num'] ) && $arr['order_num'] ){

				$arr['order_num'] = explode( ' ', $arr['order_num'] );

				foreach( $arr['order_num'] as $key=>$one ){

					if( !$one ){
						unset( $arr['order_num'][$key] );
						continue;
					}
					$mission_order = R::dispense( 'mission_order' );
					$mission_order->mission_id = $id;
					$mission_order->order_num = $one;
					$mission_order->created = Helper\Html::now();
					$model->ownMissionOrder[] = $mission_order;
				}
			}

			if( isset( $arr['send_product_back'] ) && $arr['send_product_back'] ){

				foreach( $arr['send_product_back'] as $one ){
					$mission_product = R::dispense( 'mission_product' );
					$mission_product->mission_id = $id;
					$mission_product->type = MissionProductModel::TYPE_BACK;
					$mission_product->category_id = $one['category_id'];
					$mission_product->product_id = $one['product_id'];
					$mission_product->cnt = $one['cnt'];
					$mission_product->created = Helper\Html::now();
					$mission_product->updated = Helper\Html::now();
					$mission_product->state = $one['state'];
					$model->ownMissionProduct[] = $mission_product;
				}
			}

			if( isset( $arr['send_product_to'] ) && $arr['send_product_to'] ){

				foreach( $arr['send_product_to'] as $one ){
					$mission_product = R::dispense( 'mission_product' );
					$mission_product->mission_id = $id;
					$mission_product->type = MissionProductModel::TYPE_TO;
					$mission_product->category_id = $one['category_id'];
					$mission_product->product_id = $one['product_id'];
					$mission_product->cnt = $one['cnt'];
					$mission_product->created = Helper\Html::now();
					$mission_product->updated = Helper\Html::now();
					$mission_product->state = $one['state'];
					$model->ownMissionProduct[] = $mission_product;
				}
			}

			R::store($model);

			if( $mission_user->state != MissionUserModel::STATE_DRAFT ){
				MissionChangeLogModel::saveChange( $model['mission_type_id'], null, MissionChangeLogModel::getAttrs( $id ) );
			}

			if( $mission_user->state == MissionUserModel::STATE_DONE ){
				$mission_user->doneMission();
			}

			R::commit();

		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		return $model;
	}

	function updateMission( $arr ){

		$model = $this->unbox();
		$user = Yaf\Application::app()->user;

		if( $user->role_id == UserModel::ROLE_KF ){
			if( $model->kf_uid != $user->id ){
				throw new Exception( 'not your mission', 403 );
			}
		}

		if( isset( $arr['user_state'] ) && $arr['user_state'] != MissionUserModel::STATE_DRAFT ){
			if( isset( $arr['do_publish'] ) ){
				$model_original = null;	
			}else{
				$model_original = MissionChangeLogModel::getAttrs( $model->id );
			}
		}

		R::begin();
		try{

			$model->updated = Helper\Html::now();
			$model->last_uid = $user->id;
			$model->last_uname = $user->name;
			$model->store_id = $arr['store_id'];
			$model->wanwan = $arr['wanwan'];
			if( $user->role_id == UserModel::ROLE_CG ){
				$model->cg_uid = $user->id;
			}
			
			R::store ( $model );

			MissionExtModel::setExt( $model->mission_type_id, $model->id, $arr );

			if( !$mission_user = current(R::find( 'mission_user', 'mission_id = ? and uid = ?', array( $model->id, $user->id ) ) ) ){
				$mission_user = R::dispense( 'mission_user' );
				$mission_user->mission_id = $id;
				$mission_user->uid = $user->id;
				$mission_user->created = Helper\Html::now();
			}
			if( isset( $arr['user_state'] ) ){
				$mission_user->state = $arr['user_state'];
			}

			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			// order_num 
			$mission_order_list_tmp = $model->withCondition( 'deleted is null' )->ownMissionOrder;
			$mission_order_list = array();
			foreach( $mission_order_list_tmp as $one ){
				$mission_order_list[$one['order_num']] = $one;
			}
			$mission_order_list_keys = array_keys( $mission_order_list );
			if( isset( $arr['order_num'] ) && $arr['order_num'] ){

				$arr['order_num'] = explode( ' ', $arr['order_num'] );

				foreach( $arr['order_num'] as $key=>$one ){

					$one = trim( $one );
					if( !$one ){
						unset( $arr['order_num'][$key] );
						continue;
					}

					if( !isset( $mission_order_list[$one] ) && !in_array( $one, $mission_order_list_keys ) ){
						$mission_order = R::dispense( 'mission_order' );
						$mission_order->mission_id = $model->id;
						$mission_order->order_num = $one;
						$mission_order->created = Helper\Html::now();
						$model->ownMissionOrder[] = $mission_order;
					}else{
						unset( $mission_order_list[$one] );
					}
				}
			}
			foreach( $mission_order_list as $one ){
				$one->deleted = Helper\Html::now();
				R::store( $one );
			}

			// send back product 
			$send_back_product_list_tmp = $model->withCondition( 'deleted is null and type = '. MissionProductModel::TYPE_BACK )->ownMissionProduct;
			$send_back_product_list = array();
			foreach( $send_back_product_list_tmp as $one ){
				$send_back_product_list[$one['id']] = $one;
			}
			if( isset( $arr['send_product_back'] ) && $arr['send_product_back'] ){

				foreach( $arr['send_product_back'] as $one ){

					if( !$one )continue;

					if( !isset( $send_back_product_list[$one['id']] ) ){

						$mission_product = R::dispense( 'mission_product' );
						$mission_product->mission_id = $model->id;
						$mission_product->type = MissionProductModel::TYPE_BACK;
						$mission_product->category_id = $one['category_id'];
						$mission_product->product_id = $one['product_id'];
						$mission_product->cnt = $one['cnt'];
						$mission_product->created = Helper\Html::now();
						$mission_product->updated = Helper\Html::now();
						$mission_product->state = $one['state'];
						$model->ownMissionProduct[] = $mission_product;

					}else{

						$mission_product = $send_back_product_list[$one['id']];
						$mission_product->updated = Helper\Html::now();
						$mission_product->cnt = $one['cnt'];
						$mission_product->state = $one['state'];

						unset( $send_back_product_list[$one['id']] );
					}
				}
			}
			// sdebug( array_keys($send_back_product_list) );
			// sdebug( $arr['send_product_back'] );
			// die;
			foreach( $send_back_product_list as $one ){
				$one->deleted = Helper\Html::now();
				R::store( $one );
			}
			R::store( $model );

			// send to product 
			$send_to_product_list_tmp = $model->withCondition( 'deleted is null and type = '. MissionProductModel::TYPE_TO )->ownMissionProduct;
			$send_to_product_list = array();
			foreach( $send_to_product_list_tmp as $one ){
				$send_to_product_list[$one['id']] = $one;
			}
			if( isset( $arr['send_product_to'] ) && $arr['send_product_to'] ){

				foreach( $arr['send_product_to'] as $one ){

					if( !$one )continue;
					
					if( !isset( $send_to_product_list[$one['id']] ) ){

						$mission_product = R::dispense( 'mission_product' );
						$mission_product->mission_id = $model->id;
						$mission_product->type = MissionProductModel::TYPE_TO;
						$mission_product->category_id = $one['category_id'];
						$mission_product->product_id = $one['product_id'];
						$mission_product->cnt = $one['cnt'];
						$mission_product->created = Helper\Html::now();
						$mission_product->updated = Helper\Html::now();
						$mission_product->state = $one['state'];
						$model->ownMissionProduct[] = $mission_product;

						R::store( $model );

					}else{

						$mission_product = $send_to_product_list[$one['id']];
						$mission_product->updated = Helper\Html::now();
						$mission_product->cnt = $one['cnt'];
						$mission_product->state = $one['state'];

						unset( $send_to_product_list[$one['id']] );
					}
				}
			}
			foreach( $send_to_product_list as $one ){
				$one->deleted = Helper\Html::now();
				R::store( $one );
			}

			R::store($model);

			if( $mission_user->state != MissionUserModel::STATE_DRAFT ){
				MissionChangeLogModel::saveChange( $model->mission_type_id, $model_original, MissionChangeLogModel::getAttrs( $model->id ) );
			}

			if( $mission_user->state == MissionUserModel::STATE_DONE ){
				$mission_user->doneMission();
			}

			R::commit();

		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		return $this;
	}

	function closeMission(){

		$model = $this->unbox();
		$user = Yaf\Application::app()->user;

		if( $user->role_id == UserModel::ROLE_KF ){
			if( $model->kf_uid != $user->id ){
				throw new Exception( 'not your mission', 403 );
			}
		}

		$mission_user = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $model->id, $user->id ) );
		$model_original = array();
		if( $mission_user->user_state != MissionUserModel::STATE_DRAFT ){
			$model_original = MissionChangeLogModel::getAttrs( $model->id );
		}

		R::begin();
		try{

			$model->updated = Helper\Html::now();
			$model->last_uid = $user->id;
			$model->last_uname = $user->name;
			
			R::store ( $model );

			$mission_user->state = MissionUserModel::STATE_CLOSED;

			$mission_user->updated = Helper\Html::now();
			R::store( $mission_user );

			if( $user->role_id == UserModel::ROLE_KF ){
				$mission_user_other = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $model->id, $model->cg_uid ) );
			}else{
				$mission_user_other = R::findOne( 'mission_user', 'mission_id = ? and uid = ?', array( $model->id, $model->kf_uid ) );
			}
			if( $mission_user_other['state'] == MissionUserModel::STATE_CLOSED ){
				$model->state = self::STATE_CLOSED;
			}

			R::store($model);

			if( $mission_user->state != MissionUserModel::STATE_DRAFT ){
				MissionChangeLogModel::saveChange( $model->mission_type_id, $model_original, MissionChangeLogModel::getAttrs( $model->id ) );
			}

			R::commit();

		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		return $this;
	}

	static function getList( $uid, $type, $order_num = 0 ){

		$param = array( $uid );

		$sql_type = '';
		switch( $type ){
			case 'waiting':
				$sql_type = ' and ( mu.state = '. MissionUserModel::STATE_DRAFT .' or mu.state = '. MissionUserModel::STATE_WAITING .' ) ';
				break;
			case 'dealing':
				$sql_type = ' and mu.state = '. MissionUserModel::STATE_DEALING .' ';
				break;
			case 'done':
				$sql_type = ' and mu.state = '. MissionUserModel::STATE_DONE .' ';
				break;
			case 'closed':
				$sql_type = ' and mu.state = '. MissionUserModel::STATE_CLOSED .' ';
				break;
		}

		$sql_join_order = $sql_where_order = '';
		if( $order_num ){
			$sql_join_order = ' inner join mission_order mo on mo.mission_id = m.id and mo.deleted is null ';
			$sql_where_order = ' and mo.order_num = ? ';
			$param[] = $order_num;
		}

		$sql = 'select m.*, s.name store, mu.state user_state, c.name category, sc.name sub_category, 
			c.id as category_id, sc.id as sub_category_id, 
			u.name create_uname, kf.name kf_uname
			from mission m 
			inner join mission_user mu on m.id = mu.mission_id
			inner join mission_type sc on m.mission_type_id = sc.id
			inner join mission_type c on sc.pid = c.id
			inner join store s on s.id = m.store_id
			inner join user u on u.id = m.create_uid
			inner join user kf on kf.id = m.kf_uid '. $sql_join_order .'
			where mu.uid = ? '. $sql_type. $sql_where_order .'
		 order by m.id desc';
		$tmp = R::getAll( $sql, $param );
		$list = $ids = array();
		foreach( $tmp as $one ){
			$one['user_state_name'] = MissionUserModel::getStateName( $one['user_state'] );
			$one['mission_state'] = MissionModel::getMissionStateName( $one );

			$list[$one['id']] = $one;
			$ids[] = $one['id'];
		}

		if( $ids ){

			$sql = 'select * from mission_order where mission_id in ('. implode( ',', $ids ) .') and deleted is null ';
			$order_num_list = R::getAll( $sql );
			foreach( $order_num_list as $one ){
				!isset( $list[$one['mission_id']]['order_num_list'] ) && $list[$one['mission_id']]['order_num_list'] = array();
				$list[$one['mission_id']]['order_num_list'][] = $one['order_num'];
			}

			$sql = 'select * from mission_product where mission_id in ('. implode( ',', $ids ) .') and deleted is null ';
			$product_list = R::getAll( $sql );
			foreach( $product_list as $one ){
				!isset( $list[$one['mission_id']]['product_list'] ) && $list[$one['mission_id']]['product_list'] = array();
				$list[$one['mission_id']]['product_list'][] = $one;
			}
		}

		return $list;
	}

	static function getMissionStateName( $one ){

		$user = Yaf\Application::app()->user;
		if( $user->role_id == UserModel::ROLE_KF ){
			return '';
		}
		// return $user->role_id;

		$arr = array();
		$one['is_changed'] && $arr[] = 'is_changed';
		$one['is_second'] && $arr[] = 'is_second';
		($one['pid'] > 0 ) && $arr[] = 'has_pid';

		// $arr = array(
		// 	'is_changed', 
		// 	'is_second', 
		// 	'has_pid', 
		// );
		$out = '<ul class="list-inline">';
		foreach( $arr as $one ){
			$out .= "<li><img src='/img/ms_{$one}_1.jpg' /></li>";
		}
		$out .= '</ul>';
		return $out;
	}
}

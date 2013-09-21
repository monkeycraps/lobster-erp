<?php

class MissionModel extends RedBean_SimpleModel {

	const STATE_DRAFT = 0;
	const STATE_ON = 1;
	const STATE_CLOSED = 2;
	const STATE_TO_OTHER = 3;

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
			$model->state = $arr['user_state'] == MissionUserModel::STATE_DRAFT ? self::STATE_DRAFT : self::STATE_ON;
			$model->is_changed = 0;
			$model->is_second = 0;
			$model->pid = 0;

			isset( $arr['store_id'] ) && $model->store_id = $arr['store_id'];
			if( isset( $arr['wanwan'] )){
				$model->wanwan = $arr['wanwan'];
				if( $arr['wanwan'] && R::findOne( 'mission', 'wanwan = ?', array( $arr['wanwan'] ) ) ){
					$model->is_second = 1;
				}
			}
			isset( $arr['remarks'] ) && $model->remarks = $arr['remarks'];

			
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
					if( !$one )continue;

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
					if( !$one )continue;
					
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

			if( isset( $arr['send_product_old'] ) && $arr['send_product_old'] ){

				foreach( $arr['send_product_old'] as $one ){
					if( !$one )continue;
					
					$mission_product = R::dispense( 'mission_product' );
					$mission_product->mission_id = $id;
					$mission_product->type = MissionProductModel::TYPE_OLD;
					$mission_product->category_id = $one['category_id'];
					$mission_product->product_id = $one['product_id'];
					$mission_product->cnt = $one['cnt'];
					$mission_product->created = Helper\Html::now();
					$mission_product->updated = Helper\Html::now();
					$mission_product->state = $one['state'];
					$model->ownMissionProduct[] = $mission_product;
				}
			}

			if( isset( $arr['drawback'] ) && $arr['drawback'] ){

				$drawback = R::dispense( 'mission_drawback' );
				$drawback->money = $arr['drawback']['money'];
				$drawback->reason = $arr['drawback']['reason'];
				$drawback->zhifubao = $arr['drawback']['zhifubao'];
				$drawback->created = Helper\Html::now();
				$drawback->updated = Helper\Html::now();
				$drawback->kf_uid = $user->id;
				$drawback->mission_id = $id;
				$drawback->state = MissionDrawbackModel::STATE_NEW;
				R::store( $drawback );
			}

			if( isset( $arr['refundment'] ) && !$refundment = R::findOne( 'mission_refundment', 'mission_id = ?', array( $model->id ) ) ){
				$refundment = R::dispense( 'mission_refundment' );
				$refundment->created = Helper\Html::now();
				$refundment->updated = Helper\Html::now();
				$refundment->kf_uid = $user->id;
				$refundment->mission_id = $id;
				$refundment->state = MissionRefundmentModel::STATE_NEW;
				R::store( $refundment );
			}

			R::store($model);


			$changed = array();

			if( $mission_user->state != MissionUserModel::STATE_DRAFT ){
				$changed = MissionChangeLogModel::saveChange( $model['mission_type_id'], null, MissionChangeLogModel::getAttrs( $id ) );
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

		$mission_state_changed = false;

		if( $user->role_id == UserModel::ROLE_KF ){
			if( $model->kf_uid != $user->id ){
				throw new Exception( 'not your mission', 403 );
			}
		}

		if( isset( $arr['user_state'] ) && $arr['user_state'] != MissionUserModel::STATE_DRAFT ){
			if( isset( $arr['do_publish'] ) && $arr['do_publish'] ){
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
			$model->state = $arr['user_state'] == MissionUserModel::STATE_DRAFT ? self::STATE_DRAFT : self::STATE_ON;
			
			isset( $arr['store_id'] ) && $model->store_id = $arr['store_id'];

			if( isset( $arr['wanwan'] )){
				$model->wanwan = $arr['wanwan'];
				if( R::findOne( 'mission', 'wanwan = ? and id <> ?', array( $arr['wanwan'], $model->id ) ) ){
					$model->is_second = 1;
				}else{
					$model->is_second = 0;
				}
			}

			isset( $arr['remarks'] ) && $model->remarks = $arr['remarks'];
			if( $user->role_id == UserModel::ROLE_CG ){
				$model->cg_uid = $user->id;
				$model->is_new = 0;
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

				if( $mission_user->state != $arr['user_state'] ){
					$mission_state_changed = true;
				}

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

			// old
			$send_old_product_list_tmp = $model->withCondition( 'deleted is null and type = '. MissionProductModel::TYPE_OLD )->ownMissionProduct;
			$send_old_product_list = array();
			foreach( $send_old_product_list_tmp as $one ){
				$send_old_product_list[$one['id']] = $one;
			}
			if( isset( $arr['send_product_old'] ) && $arr['send_product_old'] ){

				foreach( $arr['send_product_old'] as $one ){

					if( !$one )continue;
					
					if( !isset( $send_old_product_list[$one['id']] ) ){

						$mission_product = R::dispense( 'mission_product' );
						$mission_product->mission_id = $model->id;
						$mission_product->type = MissionProductModel::TYPE_OLD;
						$mission_product->category_id = $one['category_id'];
						$mission_product->product_id = $one['product_id'];
						$mission_product->cnt = $one['cnt'];
						$mission_product->created = Helper\Html::now();
						$mission_product->updated = Helper\Html::now();
						$mission_product->state = $one['state'];
						$model->ownMissionProduct[] = $mission_product;

						R::store( $model );

					}else{

						$mission_product = $send_old_product_list[$one['id']];
						$mission_product->updated = Helper\Html::now();
						$mission_product->cnt = $one['cnt'];
						$mission_product->state = $one['state'];

						unset( $send_old_product_list[$one['id']] );
					}
				}
			}
			foreach( $send_old_product_list as $one ){
				$one->deleted = Helper\Html::now();
				R::store( $one );
			}


			if( isset( $arr['drawback'] ) && $arr['drawback'] ){

				if( $user->role_id == UserModel::ROLE_KF ){

					if( !$drawback = R::findOne( 'mission_drawback', 'mission_id = ?', array( $model->id ) ) ){

						$drawback = R::dispense( 'mission_drawback' );
						$drawback->created = Helper\Html::now();
						$drawback->mission_id = $model->id;
						$drawback->state = MissionDrawbackModel::STATE_NEW;
					}
					$drawback->kf_uid = $user->id;
					$drawback->money = $arr['drawback']['money'];
					$drawback->reason = $arr['drawback']['reason'];
					$drawback->zhifubao = $arr['drawback']['zhifubao'];
					$drawback->updated = Helper\Html::now();
					R::store( $drawback );
				}

			}

			if( isset( $arr['refundment'] ) && !$refundment = R::findOne( 'mission_refundment', 'mission_id = ?', array( $model->id ) ) ){
				$refundment = R::dispense( 'mission_refundment' );
				$refundment->created = Helper\Html::now();
				$refundment->updated = Helper\Html::now();
				$refundment->kf_uid = $user->id;
				$refundment->mission_id = $model->id;
				$refundment->state = MissionRefundmentModel::STATE_NEW;
				R::store( $refundment );
			}

			R::store($model);

			$changed = array();

			if( $mission_user->state != MissionUserModel::STATE_DRAFT ){
				$changed = MissionChangeLogModel::saveChange( $model->mission_type_id, $model_original, MissionChangeLogModel::getAttrs( $model->id ) );
			}

			if( $mission_user->state == MissionUserModel::STATE_DONE ){
				$mission_user->doneMission();
			}

			if( $changed or ($mission_state_changed && $mission_user->state == MissionUserModel::STATE_DONE) ){

				// 发布有别的发送
				// 客服修改信息，要发送给仓管
				if( !isset( $arr['do_publish'] ) && $user->role_id == UserModel::ROLE_KF ){

					// if( $model->cg_uid ){
					// 	$order_num_list_tmp = $model->ownMissionOrder;

					// 	$title = '任务有更改。';
					// 	$fix = '';
					// 	if( $order_num_list_tmp ){
					// 		$fis = '订单号: ';
					// 		foreach( $order_num_list_tmp as $one1 ){
					// 			$fix .= $one1['order_num']. ' \ ';
					// 		}
					// 		$fix = substr($fix, 0, strlen( $fix ) - 3 );
					// 	}else{
					// 		$fix = 'id号: '+ $model->id;
					// 	}
					// 	$content = $fix. '<br/>';
					// 	if( $changed ){
					// 		foreach( $changed as $key1=>$one1 ){
					// 			$action = MissionChangeLogModel::getAction( $one1 );
					// 			$content .= '【'. $action. '】'. $one1['key']. ' - '. (isset( $one1[$key1] ) ? $one1[$key1] : '异常记录'). '<br/>' ;
					// 		}
					// 	}
					// 	plugin( 'user' )->message->send( $model->cg_uid, $title, $content, $model->id );
					// }

				}
			}

			if( $user->role_id == UserModel::ROLE_CG ){

				if( $mission_state_changed && $mission_user->state == MissionUserModel::STATE_DONE ){

					// $order_num_list_tmp = $model->ownMissionOrder;

					// $title = '仓管完成任务';
					// $fix = '';
					// if( $order_num_list_tmp ){
					// 	$fis = '订单号: ';
					// 	foreach( $order_num_list_tmp as $one1 ){
					// 		$fix .= $one1['order_num']. ' \ ';
					// 	}
					// 	$fix = substr($fix, 0, strlen( $fix ) - 3 );
					// }else{
					// 	$fix = 'id号: '+ $model->id;
					// }
					// $content = $fix. "</br>". MissionTypeModel::getParentName( $model->mission_type_id ) . ' - '. MissionTypeModel::getName( $model->mission_type_id );
					// if( $changed ){
					// 	foreach( $changed as $key1=>$one1 ){
					// 		$action = MissionChangeLogModel::getAction( $one1 );
					// 		$content .= '【'. $action. '】'. $one1['key']. ' - '. (isset( $one1[$key1] ) ? $one1[$key1] : '异常记录'). '<br/>' ;
					// 	}
					// }

				}
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

			if( $user->role_id == UserModel::ROLE_CG ) {

				$title = $model->id. ' - 仓管 '. $user->name. ' 关闭任务';
				$content = 
					$model->id. ' - '. 
					MissionTypeModel::getParentName( $model->mission_type_id ) . ' - '. 
					MissionTypeModel::getName( $model->mission_type_id ). ' - '. 
					$model->wanwan;

				$user->message->send( $model->kf_uid, $title, $content, $model->id );

				$mission_user_other->state = MissionUserModel::STATE_WAITING;
				R::store( $mission_user_other );
			}

			R::commit();

		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		return $this;
	}

	static function getListCnt( $uid, $type ){

		list( $sql, $param ) = self::getListSql( 'cnt', $uid, $type );

		return R::getCell( $sql, $param );
	}

	static function getList( $uid, $type, $search=array(), $goto = 0 ){

		list( $sql, $param ) = self::getListSql( 'list', $uid, $type, $search, $goto );

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

	static function getListSql( $sql_data_type, $uid, $type, $search_opt = array(), $goto = 0 ){

		$param = array( $uid );

		$sql_type = '';
		switch( $type ){
			case 'waiting':
				$param = array_merge( $param, array( $uid ) );
				$sql_type = ' and mu.uid = ? and ( mu.state = '. MissionUserModel::STATE_DRAFT .' or mu.state = '. MissionUserModel::STATE_WAITING .' ) ';
				break;
			case 'dealing':
				$param = array_merge( $param, array( $uid ) );
				$sql_type = ' and mu.uid = ? and mu.state = '. MissionUserModel::STATE_DEALING .' ';
				break;
			case 'done':
				$param = array_merge( $param, array( $uid ) );
				$sql_type = ' and mu.uid = ? and mu.state = '. MissionUserModel::STATE_DONE .' ';
				break;
			case 'closed':
				$param = array_merge( $param, array( $uid ) );
				$sql_type = ' and mu.uid = ? and mu.state = '. MissionUserModel::STATE_CLOSED .' ';
				break;
			case 'waiting_drawback':
				$param = array_merge( $param, array( $uid ) );
				$sql_type = ' and mu.uid = ? and m.state <> '. self::STATE_CLOSED .' and mu.state = '. MissionUserModel::STATE_WAITING_DRAWING .' ';
				break;
			case 'waiting_refundment':
				$param = array_merge( $param, array( $uid ) );
				$sql_type = ' and mu.uid = ? and m.state <> '. self::STATE_CLOSED .' and mu.state = '. MissionUserModel::STATE_WAITING_REFUNDMENT .' ';
				break;
			case 'dz_unclosed':
				$sql_type = ' and m.state <> '. self::STATE_CLOSED .' and (
					mu.state is null or (
						mu.state <> '. MissionUserModel::STATE_WAITING_DRAWING .' and 
						mu.state <> '. MissionUserModel::STATE_WAITING_REFUNDMENT.' 
					) )';
				break;
			case 'dz_closed':
				$sql_type = ' and m.state = '. self::STATE_CLOSED.' ';
				break;
		}

		$sql_join_order = $sql_where_order = '';

		if( $search_opt ){
			foreach( $search_opt as $key=>$one ){
				if( !$one )continue;
				switch( $key ){
					case 'id':
						$sql_where_order .= ' and m.id = ? ';
						$param[] = $one;
						break;
					case 'order_num':
						$sql_join_order .= ' inner join mission_order mo on mo.mission_id = m.id and mo.deleted is null ';
						$sql_where_order .= ' and mo.order_num like( ? ) ';
						$param[] = '%'. $one. '%';
						break;
					case 'wanwan':
						$sql_where_order .= ' and m.wanwan like( ? ) ';
						$param[] = '%'. $one. '%';
						break;
				}
			}
		}

		switch( $sql_data_type ){
			case 'list':
				$sql = 'select m.*, s.name store, mu.state user_state, c.name category, sc.name sub_category, 
					c.id as category_id, sc.id as sub_category_id, 
					u.name create_uname, kf.name kf_uname, 
					md.zhifubao as drawback_zhifubao, md.money as drawback_money
					from mission m 
					left join mission_user mu on m.id = mu.mission_id and mu.uid = ?
					inner join mission_type sc on m.mission_type_id = sc.id
					inner join mission_type c on sc.pid = c.id
					left join store s on s.id = m.store_id
					left join mission_drawback md on m.id = md.mission_id
					inner join user u on u.id = m.create_uid
					inner join user kf on kf.id = m.kf_uid '. $sql_join_order .'
					where 1 and m.state <> '. self::STATE_TO_OTHER .' '. $sql_type. $sql_where_order .'
				 order by m.id desc';
				break;
			case 'cnt':
				$sql = 'select count(1)
					from mission m 
					left join mission_user mu on m.id = mu.mission_id and mu.uid = ?
					inner join mission_type sc on m.mission_type_id = sc.id
					inner join mission_type c on sc.pid = c.id
					left join store s on s.id = m.store_id
					inner join user u on u.id = m.create_uid
					inner join user kf on kf.id = m.kf_uid '. $sql_join_order .'
					where 1 and m.state <> '. self::STATE_TO_OTHER .' '. $sql_type. $sql_where_order .'
				 order by m.id desc';
				break;
			default: 
				throw new Exception( 'not valid sql data type' );
				break;
		}
		
		return array( $sql, $param );
	}

	static function getMissionStateName( $one ){

		$user = Yaf\Application::app()->user;
		// if( $user->role_id == UserModel::ROLE_KF ){
		// 	return '';
		// }
		// return $user->role_id;

		$arr = array();
		switch( $user->role_id ){
			case UserModel::ROLE_KF:
				$one['state'] == self::STATE_DRAFT && $arr[] = 'is_draft';
				break;
			case UserModel::ROLE_CG:
				$one['is_new'] && $arr[] = 'is_new';
				$one['is_changed'] && $arr[] = 'is_changed';
				($one['pid'] > 0 ) && $arr[] = 'has_pid';
			default:
				break;
		}
		$one['is_second'] && $arr[] = 'is_second';

		// $arr = array(
		// 	'is_changed', 
		// 	'is_second', 
		// 	'has_pid', 
		// );
		$out = '<ul class="list-inline">';
		foreach( $arr as $one ){
			$out .= "<li><img src='/img/ms_{$one}_3.jpg' /></li>";
		}
		$out .= '</ul>';
		return $out;
	}

	function copyMission( $mission_type ){

		$atts = current( R::exportAll( $this->unbox() ) );
		$mission_new = R::dispense( 'mission' );

		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );
		if( is_array( $yaml['form'. $mission_type] ) ){

			if( !is_array( $yaml['form'. $mission_type]['ext'] ) ){
				$yaml['form'. $mission_type]['ext'] = array();
			}
			$labels = array_merge(
				$yaml['form'], 
				$yaml['form'. $mission_type]['data'], 
				$yaml['form'. $mission_type]['ext']
			);
		}

		foreach( $atts as $key=>$one ){
			$subkey = strtolower( substr( $key, 0, 3 ) );
			if( $subkey != 'own' && $key != 'sharedProduct' ){
				switch( $key ){
					case 'id':
						break;
					case 'mission_type_id':
						$mission_new->mission_type_id = $mission_type;
						break;
					default: 
						$mission_new->setAttr( $key, $one );
				}
			}
		}

		$mission_new->pid = $this->id;
		R::store( $mission_new );

		foreach( $atts as $key=>$one ){
			$subkey = strtolower( substr( $key, 0, 3 ) );
			if( $key != 'sharedProduct' ){
				
				switch( $key ){
					case 'ownMission_order':
						if( isset( $labels['order_num'] ) ){

							foreach( $one as $one1 ){
								$order = R::dispense( 'mission_order' );
								$order->import( $one1 );
								$order->id = null;
								$order->created = Helper\Html::now();
								$order->mission_id = $mission_new->id;
								$mission_new->ownMissionOrder[] = $order;
							}
						}
						break;
					case 'ownMission_drawback':
						if( isset( $labels['drawback'] ) ){

							foreach( $one as $one1 ){
								$drawback = R::dispense( 'mission_drawback' );
								$drawback->import( $one1 );
								$drawback->id = null;
								$drawback->created = Helper\Html::now();
								$drawback->updated = Helper\Html::now();
								$drawback->mission_id = $mission_new->id;
								$mission_new->ownMissionDrawback[] = $drawback;
							}
						}
						break;
					case 'ownMission_refundment':
						if( isset( $labels['refundment'] ) ){

							foreach( $one as $one1 ){
								$refundment = R::dispense( 'mission_refundment' );
								$refundment->import( $one1 );
								$refundment->id = null;
								$refundment->created = Helper\Html::now();
								$refundment->updated = Helper\Html::now();
								$refundment->mission_id = $mission_new->id;
								$mission_new->ownMissionRefundment[] = $refundment;
							}
						}
						break;
					case 'ownMission_flag':
						if( isset( $labels['flag'] ) ){

							foreach( $one as $one1 ){
								$flag = R::dispense( 'mission_flag' );
								$flag->import( $one1 );
								$flag->id = null;
								$flag->created = Helper\Html::now();
								$flag->updated = Helper\Html::now();
								$flag->mission_id = $mission_new->id;
								$mission_new->ownMissionRefundment[] = $flag;
							}
						}
						break;
					case 'ownMission_product':
						foreach( $one as $one1 ){

							$goon = true;
							switch( $one1['type'] ){
								case MissionProductModel::TYPE_BACK:
									if( !isset( $labels['product_back'] ) )$goon = false;
									break;
								case MissionProductModel::TYPE_TO:
									if( !isset( $labels['product_to'] ) )$goon = false;
									break;
								case MissionProductModel::TYPE_OLD:
									if( !isset( $labels['product_old'] ) )$goon = false;
									break;
								default: 
									$goon = false;
									break;
							}
							if( !$goon )continue;

							$product = R::dispense( 'mission_product' );
							$product->import( $one1 );
							$product->id = null;
							$product->created = Helper\Html::now();
							$product->updated = Helper\Html::now();
							$product->mission_id = $mission_new->id;
							$mission_new->ownMissionProduct[] = $product;
						}
						break;
					case 'ownMission_ext':
						foreach( $one as $one1 ){
							$ext = R::dispense( 'mission_ext' );
							$ext->import( $one1 );
							$ext->id = null;
							$ext->created = Helper\Html::now();
							$ext->updated = Helper\Html::now();
							$ext->mission_id = $mission_new->id;
							$mission_new->ownMissionExt[] = $ext;
						}
						break;
					case 'ownMission_user':
						foreach( $one as $one1 ){
							$user = R::dispense( 'mission_user' );
							$user->import( $one1 );
							$user->id = null;
							$user->created = Helper\Html::now();
							$user->updated = Helper\Html::now();
							$user->mission_id = $mission_new->id;
							$mission_new->ownMissionUser[] = $user;
						}
						break;
				}
			}
		}

		R::store( $mission_new );

		return $mission_new;
	}
}

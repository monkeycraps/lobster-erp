<?php

class MissionModel extends RedBean_SimpleModel {

	const STATE_DRAFT = 0;
	const STATE_WAITING= 1;
	const STATE_ON = 2;
	const STATE_DONE = 100;
	const STATE_CLOSE = 101;
	const STATE_DELETE = 102;

	static function createMission( $arr, $is_draft = true, $pid = 0 ){

		$arr['mission_type_id'] = 12;

		$user = Yaf\Application::app()->user;

		R::begin();
		try{

			$model = R::dispense ( 'mission' );
			$model->mission_type_id = $arr['mission_type_id'];
			$model->created = Helper\Html::now();
			$model->updated = Helper\Html::now();
			$model->last_uid = $user->id;
			$model->last_uname = $user->name;
			$model->create_uid = $user->id;
			$model->store = $arr['store'];
			$model->wanwan = $arr['wanwan'];
			
			$model->status = 0;
			$id = R::store ( $model );

			$ext = MissionExtModel::setExt( $arr['mission_type_id'], $id, $arr );
			$model->ownMissionExt[] = $ext;

			$mission_user = R::dispense( 'mission_user' );
			$mission_user->mission_id = $id;
			$mission_user->uid = $user->id;
			$mission_user->state = $is_draft ? MissionModel::STATE_DRAFT : MissionModel::STATE_DONE;
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
					$mission_product->state = MissionProductModel::STATE_NEW;
					$model->ownMissionProduct[] = $mission_product;
				}
			}

			if( isset( $arr['send_product_out'] ) && $arr['send_product_out'] ){

				foreach( $arr['send_product_out'] as $one ){
					$mission_product = R::dispense( 'mission_product' );
					$mission_product->mission_id = $id;
					$mission_product->type = MissionProductModel::TYPE_OUT;
					$mission_product->category_id = $one['category_id'];
					$mission_product->product_id = $one['product_id'];
					$mission_product->cnt = $one['cnt'];
					$mission_product->created = Helper\Html::now();
					$mission_product->state = MissionProductModel::STATE_NEW;
					$model->ownMissionProduct[] = $mission_product;
				}
			}

			R::store($model);

			MissionChangeLogModel::saveChange( $model['mission_type_id'], null, MissionChangeLogModel::getAttrs( $id ) );

			R::commit();

		}catch( Exception $e ){
			R::rollback();
			throw $e;
		}

		return $model;
	}

	static function getWaitingList( $uid ){

		$sql = 'select m.*, mu.status, c.name category, sc.name sub_category
			from mission m 
			inner join mission_user mu on m.id = mu.mission_id 
			inner join mission_type sc on m.mission_type_id = sc.id
			inner join mission_type c on sc.pid = c.id
			where mu.uid = ?
		 order by m.id desc';
		$tmp = R::getAll( $sql, array( $uid ) );
		$list = $ids = array();
		foreach( $tmp as $one ){
			$list[$one['id']] = $one;
			$ids[] = $one['id'];
		}

		if( $ids ){

			$sql = 'select * from mission_order where mission_id in ('. implode( ',', $ids ) .') ';
			$order_num_list = R::getAll( $sql );
			foreach( $order_num_list as $one ){
				!isset( $list[$one['mission_id']]['order_num_list'] ) && $list[$one['mission_id']]['order_num_list'] = array();
				$list[$one['mission_id']]['order_num_list'][] = $one['order_num'];
			}

			$sql = 'select * from mission_product where mission_id in ('. implode( ',', $ids ) .') ';
			$product_list = R::getAll( $sql );
			foreach( $product_list as $one ){
				!isset( $list[$one['mission_id']]['product_list'] ) && $list[$one['mission_id']]['product_list'] = array();
				$list[$one['mission_id']]['product_list'][] = $one;
			}
		}
		return $list;
	}

}

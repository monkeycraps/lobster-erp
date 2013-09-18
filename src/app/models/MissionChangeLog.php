<?php

class MissionChangeLogModel extends RedBean_SimpleModel {

	static $label;
	static $mission_type;

	static function saveChangeAction( $mission_id, $action, $state ){

		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );

		self::$label = $yaml['form'];

		$changed = array();
		switch( $action ){
			case 'drawback':
				$changed[] = array(
					'key'=>self::getLabel('drawback_state'),
					'change'=> MissionDrawbackModel::getStateName( $state ), 
				);
				break;
			case 'refundment':
				$changed[] = array(
					'key'=>self::getLabel('refundment_state'),
					'change'=> MissionRefundmentModel::getStateName( $state ), 
				);
				break;
		}

		$model = R::dispense( 'mission_change_log' );
		$model->mission_id = $mission_id;
		$model->changed = json_encode($changed);
		$model->created = Helper\Html::now();
		$model->updated = Helper\Html::now();
		$model->uid = yaf\Application::app()->user->id;
		R::store( $model );

	}

	static function saveChange( $mission_type, $before, $now ){

		$is_new = true;
		$before && $is_new = false;

		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );
		if( is_array( $yaml['form'. $mission_type] ) ){

			if( !is_array( $yaml['form'. $mission_type]['ext'] ) ){
				$yaml['form'. $mission_type]['ext'] = array();
			}
			self::$label = array_merge(
				$yaml['form'], 
				$yaml['form'. $mission_type]['data'], 
				$yaml['form'. $mission_type]['ext']
			);
		}

		$user = Yaf\Application::app()->user;

		if( $changed = self::GetChanged( $is_new ? array() : $before, $now ) ){

			$model = R::dispense( 'mission_change_log' );
			$model->mission_id = $now['id'];
			$model->changed = json_encode($changed);
			$model->created = Helper\Html::now();
			$model->updated = Helper\Html::now();
			$model->uid = yaf\Application::app()->user->id;
			R::store( $model );

			$omit = array(
				'id', 'updated', 'created', 'state', 'closed', 'user_state', 
				'drawback_state', 'refundment_state', 'profit', 'profit_reason', 
				'drawback', 'drawback_money', 'drawback_zhifubao', 
			);

			// kefu mission 修改，记录为 is_changed
			if( $user->role_id == UserModel::ROLE_KF ){

				$mission = R::findOne( 'mission', 'id = ?', array( $now['id'] ) );
				if( $mission->state > 0 ){

					foreach( $changed as $one ){
						if( isset( $one['key'] ) ){
							if( in_array( $one['key'], $omit ) ){
								continue;
							}
							$mission->is_changed = 1;
							$mission->is_new = 0;
							R::store( $mission );

							break;
						}
					}
				}

			}elseif( $user->role_id == UserModel::ROLE_CG ){

				foreach( $changed as $one ){
					if( isset( $one['key'] ) && $one['key'] == '动作' ){
						if( isset( $one['change'] ) && $now['user_state'] == MissionUserModel::STATE_DONE ){
							$mission = R::findOne( 'mission', 'id = ?', array( $now['id'] ) );
							$mission->is_changed = 0;
							R::store( $mission );
							break;
						}
					}
				}
			}
		}
		return $changed;
	}

	static function getChanged( $before, $now ){

		$changed = array();
		foreach( $now as $key=>$one ){
			switch( $key ){
				case 'ext':
					$changed = self::getChangedExt( $changed, $before, $now  );
					break;
				case 'product_old':
				case 'product_back':
				case 'product_to':
					$changed = self::getChangedProduct( $changed, $before, $now, $key );
					break;
				case 'order':
					$changed = self::getChangedOrder( $changed, $before, $now );
					break;
				case 'drawback':
					$changed = self::getChangedDrawback( $changed, $before, $now );
					break;
				case 'refundment':
					$changed = self::getChangedRefundment( $changed, $before, $now );
					break;
				default:
					if( !self::getLabel($key) ){
						continue;
					}
					switch( $key ){
						case 'mission_type_id':
							if( isset( $before[$key] ) ){
								$before[$key] = MissionTypeModel::getName( $before[$key] );
							}
							$one = MissionTypeModel::getName( $one );
							break;
						case 'store':
							if( isset( $before[$key] ) ){
								$before[$key] = StoreModel::getName( $before[$key] );
							}
							$one = StoreModel::getName( $one );
							break;
						case 'user_state':
							if( isset( $before[$key] ) ){
								$before[$key] = MissionUserModel::getStateName( $before[$key] );
							}
							$one = MissionUserModel::getStateName( $one );
							break;
					}
					if( !isset( $before[$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key),
							'add'=>$one, 
						);
					}else{
						if( self::diff( $before[$key], $one ) ){
							$changed[] = array(
								'key'=>self::getLabel($key),
								'change'=>$before[$key]. '=>'. $one, 
							);
						}
					}
					break;
			}
		}

		$changed = self::checkDelete( $changed, $before, $now );

		return $changed;
	}

	static function checkDelete( $changed, $before, $now ){
		foreach( $before as $key=>$one ){
			switch( $key ){
				case 'ext':
					$changed = self::checkDelteExt( $changed, $before, $now );
					break;
				case 'drawback':
					$changed = self::checkDelteDrawback( $changed, $before, $now );
					break;
				case 'refundment':
					$changed = self::checkDelteRefundment( $changed, $before, $now );
					break;
				case 'product_old':
				case 'product_back':
				case 'product_to':
					break;
				case 'order':
					break;
				default:
					if( !self::getLabel($key) ){
						continue;
					}
					if( !isset( $now[$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key1),
							'delete'=>$one, 
						);
					}
					break;
			}
		}

		return $changed;
	}

	static function checkDelteDrawback($changed, $before, $now ){

		foreach( $before['drawback'] as $key=>$one ){
			switch( $key ){
				default:
					$key_label = 'drawback_'. $key;
					if( !self::getLabel($key_label) ){
						continue;
					}
					if( !isset( $now['drawback'][$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key_label),
							'delete'=>$one, 
						);
					}
					break;				
			}
		}

		return $changed;
	}

	static function checkDelteRefundment($changed, $before, $now ){

		foreach( $before['refundment'] as $key=>$one ){
			switch( $key ){
				default:
					$key_label = 'refundment_'. $key;
					if( !self::getLabel($key_label) ){
						continue;
					}
					if( !isset( $now['refundment'][$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key_label),
							'delete'=>$one, 
						);
					}
					break;				
			}
		}

		return $changed;
	}

	static function checkDelteExt($changed, $before, $now ){
		foreach( $before['ext'] as $key=>$one ){
			switch( $key ){

				case 'other':
					foreach( $before['ext']['other'] as $key1=>$one1 ){
						if( !self::getLabel($key1) ){
							continue;
						}
						if( !isset( $now['ext']['other'][$key1] ) ){
							$changed[] = array(
								'key'=>self::getLabel($key1),
								'delete'=>$one1, 
							);
						}
					}
					break;
				default:
					if( !self::getLabel($key) ){
						continue;
					}
					if( !isset( $now['ext'][$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key),
							'delete'=>$one, 
						);
					}
					break;				
			}
		}

		return $changed;
	}

	static function diff( $old, $new ){
		if( $old === $new ){
			return false;
		}
		return true;
	}

	static function getChangedExt( $changed, $before, $now ){

		foreach( $now['ext'] as $key=>$one ){

			switch( $key ){
				case 'other':

					foreach( $now['ext']['other'] as $key1=>$one1 ){

						if( !self::getLabel($key1) ){
							continue;
						}

						if( !isset( $before['ext']['other'][$key1] ) ){
							$changed[] = array(
								'key'=>self::getLabel($key1), 
								'add'=>$one, 
							);
						}else{
							if( self::diff( $before['ext']['other'][$key1], $one1 ) ){
								$changed[] = array(
									'key'=>self::getLabel($key1), 
									'change'=>$before['ext']['other'][$key1]. '=>'. $one, 
								);
							}
						}
					}

					break;
				default:
					if( !self::getLabel($key) ){
						continue;
					}
					if( !isset( $before['ext'][$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key), 
							'add'=>$one, 
						);
					}else{
						if( self::diff( $before['ext'][$key], $one ) ){
							$changed[] = array(
								'key'=>self::getLabel($key), 
								'change'=>$before['ext'][$key]. '=>' .$one, 
							);
						}
					}
					break;
			}
		}
		return $changed;
	}

	static function getChangedProduct( $changed, $before, $now, $product_type ){
		$is_new = $before ? false : true;

		$product_old = array();
		if( !$is_new ){
			foreach( $before[$product_type] as $one ){
				$product_old[$one['id']] = $one;
			}
		}

		$product_new = array();
		foreach( $now[$product_type] as $one ){
			$product_new[$one['id']] = $one;
		}

		foreach( $product_new as $key=>$one ){
			if( $is_new ){
				$changed[] = array(
					'key'=>self::getLabel($product_type), 
					'add'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
						ProductModel::getName( $one['product_id'] ). ' - '. 
						$one['cnt'], 
				);
			}else{
				if( !isset( $product_old[$key] ) ){
					$changed[] = array(
						'key'=>self::getLabel($product_type), 
						'add'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
							ProductModel::getName( $one['product_id'] ). ' - '. 
							$one['cnt'], 
					);
				}else{
					if( $product_old[$key]['cnt'] != $one['cnt'] ){
						$changed[] = array(
							'key'=>self::getLabel($product_type), 
							'change'=>
								CategoryModel::getName( $one['category_id'] ). ' - '. 
								ProductModel::getName( $one['product_id'] ). ' - '. 
								'('. $product_old[$key]['cnt']. '=>' .$one['cnt']. ')', 
						);
					}
				}
			}
		}

		foreach( $product_old as $key=>$one ){
			if( !isset($product_new[$key]) ){
				$changed[] = array(
					'key'=>self::getLabel($product_type), 
					'delete'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
						ProductModel::getName( $one['product_id'] ). ' - '. 
						$one['cnt'], 
				);
			}
		}

		return $changed;
	}

	static function getChangedDrawback( $changed, $before, $now ){

		foreach( $now['drawback'] as $key=>$one ){

			switch( $key ){
				default:
					$key_label = 'drawback_'. $key;
					if( !self::getLabel($key_label) ){
						continue;
					}

					switch( $key ){
						case 'state':
							if( isset( $before['drawback'][$key] ) ){
								$before['drawback'][$key] = MissionDrawbackModel::getStateName( $before['drawback'][$key] );
							}
							$one = MissionDrawbackModel::getStateName( $one );
							break;
					}


					if( !isset( $before['drawback'][$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key_label), 
							'add'=>$one, 
						);
					}else{
						if( self::diff( $before['drawback'][$key], $one ) ){
							$changed[] = array(
								'key'=>self::getLabel($key_label), 
								'change'=>$before['drawback'][$key]. '=>' .$one, 
							);
						}
					}
					break;
			}
		}
		return $changed;
	}

	static function getChangedRefundment( $changed, $before, $now ){

		foreach( $now['refundment'] as $key=>$one ){

			switch( $key ){
				default:
					$key_label = 'refundment_'. $key;
					if( !self::getLabel($key_label) ){
						continue;
					}

					switch( $key ){
						case 'state':
							if( isset( $before['refundment'][$key] ) ){
								$before['refundment'][$key] = MissionDrawbackModel::getStateName( $before['refundment'][$key] );
							}
							$one = MissionRefundmentModel::getStateName( $one );
							break;
					}


					if( !isset( $before['refundment'][$key] ) ){
						$changed[] = array(
							'key'=>self::getLabel($key_label), 
							'add'=>$one, 
						);
					}else{
						if( self::diff( $before['refundment'][$key], $one ) ){
							$changed[] = array(
								'key'=>self::getLabel($key_label), 
								'change'=>$before['refundment'][$key]. '=>' .$one, 
							);
						}
					}
					break;
			}
		}
		return $changed;
	}

	static function getChangedOrder( $changed, $before, $now ){

		$order_num_old = $order_num_new = array();
		if( $before ){
			foreach( $before['order'] as $one ){
				$order_num_old[] = $one['order_num'];
			}
		}
		foreach( $now['order'] as $one ){
			$order_num_new[] = $one['order_num'];
		}

		foreach( $order_num_new as $one ){
			if( !in_array($one, $order_num_old) ){
				$changed[] = array(
					'key'=>self::getLabel('order_num'),
					'add'=>$one, 
				);
			}		
		}

		foreach( $order_num_old as $one ){
			if( !in_array($one, $order_num_new) ){
				$changed[] = array(
					'key'=>self::getLabel('order_num'),
					'delete'=>$one, 
				);
			}		
		}

		return $changed;
	}

	static function getLabel( $key ){
		if( isset( self::$label[$key] )){
			return self::$label[$key];
		}
		return false;
	}

	static function getAttrs( $mid ){

		$user = Yaf\Application::app()->user;

		if( !$model = R::findOne( 'mission', ' id = ? ', array( $mid ) ) ){
			throw new Exception( 'no mission for mid: '. $mid );
		}

		$mission = $model->getIterator ()->getArrayCopy ();

		if( $model->ownMissionExt ){
			$ext = current($model->ownMissionExt);

			$arr = $ext->getIterator()->getArrayCopy();
			$jrr = json_decode( $arr['other'], true );

			$mission = array_merge( $mission, array(
				'ext'=>array_merge( 
					array(
						'ext1'=>$arr['ext1'], 
						'ext2'=>$arr['ext2'], 
						'ext3'=>$arr['ext3'], 
					), 
					$jrr
				)
			) );
		}

		if( $model->ownMissionDrawback ){
			$drawback = current($model->ownMissionDrawback);
			$arr = $drawback->getIterator()->getArrayCopy();
			$mission = array_merge( $mission, array( 
				'drawback'=>$arr
			));
		}

		if( $model->ownMissionRefundment ){
			$refundment = current($model->ownMissionRefundment);
			$arr = $refundment->getIterator()->getArrayCopy();
			$mission = array_merge( $mission, array( 
				'refundment'=>$arr
			));
		}

		$send_back_product_list = array();
		$send_to_product_list = array();
		$send_old_product_list = array();
		if( $model->withCondition( 'deleted is null' )->ownMissionProduct ){

			foreach( $model->ownMissionProduct as $one ){
				switch( $one->type ){
					case MissionProductModel::TYPE_BACK:
						$send_back_product_list[] = $one->getIterator()->getArrayCopy();
						break;
					case MissionProductModel::TYPE_TO:
						$send_to_product_list[] = $one->getIterator()->getArrayCopy();
						break;
					case MissionProductModel::TYPE_OLD:
						$send_old_product_list[] = $one->getIterator()->getArrayCopy();
						break;
				}
			}
		}
		$mission = array_merge( $mission, array(
			'product_back'=>$send_back_product_list, 
			'product_to'=>$send_to_product_list, 
			'product_old'=>$send_old_product_list, 
		) );

		$order_list = array();
		if( $model->withCondition( 'deleted is null' )->ownMissionOrder ){

			foreach( $model->ownMissionOrder as $one ){
				$order_list[] = $one->getIterator()->getArrayCopy();
			}
		}
		$mission = array_merge( $mission, array(
			'order'=>$order_list
		) );

		$mission_user = current( $model->withCondition( 'uid = ?', array( $user->id ) )->ownMissionUser );
		if( !$mission_user ){
			throw new Exception( 'no mission user' );
		}
		$mission = array_merge( $mission, array(
			'user_state'=>$mission_user->state
		) );

		return $mission;
	}

	static function getAction( $change ){
		$action = isset( $change['add'] ) ? '增加': ( 
			isset( $change['change'] ) ? '修改' : ( 
				isset( $change['delete'] ) ? '删除': '异常记录' ) );
		return $action;
	}

	static function getValue( $change ){
		$key = isset( $change['add'] ) ? 'add': ( 
			isset( $change['change'] ) ? 'change' : ( 
				isset( $change['delete'] ) ? 'delete': 'unknow' ) );

		if( $key == 'unknow' ){
			return '';
		}

		return $change[$key];
	}

}

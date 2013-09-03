<?php

class MissionChangeLogModel extends RedBean_SimpleModel {

	static $label;
	static $mission_type;

	static function saveChange( $mission_type, $before, $now ){

		$is_new = true;
		$before && $is_new = false;

		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );
		self::$label = array_merge(
			$yaml['form'], 
			$yaml['form'. $mission_type]
		);

		$changed = self::GetChanged( $is_new ? array() : $before, $now );

		$model = R::dispense( 'mission_change_log' );
		$model->mission_id = $now['id'];
		$model->changed = json_encode($changed);
		$model->created = Helper\Html::now();
		$model->updated = Helper\Html::now();
		$model->uid = yaf\Application::app()->user->id;
		R::store( $model );

	}

	static function getChanged( $before, $now ){

		$changed = array();
		foreach( $now as $key=>$one ){
			switch( $key ){
				case 'ext':
					$changed = self::getChangedExt( $changed, $before, $now  );
					break;
				case 'product':
					$changed = self::getChangedProduct( $changed, $before, $now  );
					break;
				case 'order':
					$changed = self::getChangedOrder( $changed, $before, $now );
					break;
				default:
					if( !self::getLabel($key) ){
						continue;
					}
					switch( $key ){
						case 'mission_type_id':
							$one = MissionTypeModel::getName( $one );
							break;
						case 'store':
							$one = StoreModel::getName( $one );
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
								'change'=>$one, 
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
				case 'product':
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
			return true;
		}
	}

	static function getChangedExt( $changed, $before, $now ){

		debug( $now['ext'] );
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
									'change'=>$one, 
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
								'change'=>$one, 
							);
						}
					}
					break;
			}
		}
		return $changed;
	}

	static function getChangedProduct( $changed, $before, $now ){
		$is_new = $before ? false : true;

		$product_old = array();
		if( !$is_new ){
			foreach( $before['product'] as $one ){
				$product_old[$one['category_id'].'_'.$one['product_id']] = $one;
			}
		}

		$product_new = array();
		foreach( $now['product'] as $one ){
			$product_old[$one['category_id'].'_'.$one['product_id']] = $one;
		}

		foreach( $product_new as $key=>$one ){
			if( $is_new ){
				$changed[] = array(
					'key'=>self::getLabel('product'), 
					'add'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
						ProductModel::getName( $one['product_id'] ). ' - '. 
						$one['cnt'], 
				);
			}else{
				if( !isset( $product_old[$key] ) ){
					$changed[] = array(
						'key'=>self::getLabel('product'), 
						'add'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
							ProductModel::getName( $one['product_id'] ). ' - '. 
							$one['cnt'], 
					);
				}else{
					if( $product_old[$key]['cnt'] != $one['cnt'] ){
						$changed[] = array(
							'key'=>self::getLabel('product'), 
							'change'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
								ProductModel::getName( $one['product_id'] ). ' - '. 
								$one['cnt'], 
						);
					}
				}
			}
		}

		foreach( $product_old as $key=>$one ){
			if( !isset($product_new[$key]) ){
				$changed[] = array(
					'key'=>self::getLabel('product'), 
					'delete'=>CategoryModel::getName( $one['category_id'] ). ' - '. 
						ProductModel::getName( $one['product_id'] ). ' - '. 
						$one['cnt'], 
				);
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

		debug( $order_num_new, 'order_num_new' );
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

		if( !$model = R::findOne( 'mission', ' id = ? ', array( $mid ) ) ){
			throw new Exception( 'no mission for mid: '. $mid );
		}

		$mission = $model->getIterator ()->getArrayCopy ();

		if( $model->ownMissionExt ){
			$ext = current($model->ownMissionExt);

			$arr = $ext->getIterator()->getArrayCopy();
			$jrr = json_decode( $arr['other'], true );
			debug( $jrr, 'mission ext others' );

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

		if( $model->ownMissionProduct ){

			$product_list = array();
			foreach( $model->ownMissionProduct as $one ){
				$product_list[] = $one->getIterator()->getArrayCopy();
			}
			$mission = array_merge( $mission, array(
				'product'=>$product_list
			) );
		}

		if( $model->ownMissionOrder ){

			$order_list = array();
			foreach( $model->ownMissionOrder as $one ){
				$order_list[] = $one->getIterator()->getArrayCopy();
			}
			$mission = array_merge( $mission, array(
				'order'=>$order_list
			) );
		}


		debug( $mission, 'mission' );
		return $mission;
	}
}

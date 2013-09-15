<?php

class MissionRefundmentModel extends RedBean_SimpleModel {

	const STATE_NEW = 1;
	const STATE_APPLY = 2;
	const STATE_DONE = 3;

	static function getStateName( $state ){
		switch( $state ){
			case self::STATE_NEW:
				return '待提交';
				break;
			case self::STATE_APPLY:
				return '申请退款中';
				break;
			case self::STATE_DONE:
				return '已退款';
				break;
		}
	}
}

<?php

class MissionDrawbackModel extends RedBean_SimpleModel {

	const STATE_NEW = 1;
	const STATE_APPLY = 2;
	const STATE_DONE = 3;

	static function getStateName( $state ){
		switch( $state ){
			case self::STATE_NEW:
				return '待提交';
				break;
			case self::STATE_APPLY:
				return '申请返现中';
				break;
			case self::STATE_DONE:
				return '店长已返现';
				break;
		}
	}
}

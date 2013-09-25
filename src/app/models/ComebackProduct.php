<?php

class ComebackProductModel extends RedBean_SimpleModel {

	const STATE_NEW = 1;
	const STATE_DONE = 2;
	const STATE_DELETE = 3;
	const STATE_DEALWITH = 4;
	const STATE_DONE_NEW = 5;

	static function getStateName( $state ){
		switch( $state ){
			case self::STATE_NEW:
				return '未处理';
				break;
			case self::STATE_DEALWITH:
				return '已处理';
				break;
			case self::STATE_DONE:
				return '已返厂';
				break;
			case self::STATE_DELETE:
				return '删除';
				break;
			case self::STATE_DONE_NEW:
				return '已返厂（客户未认领）';
				break;
		}
		return '异常';
	}
}

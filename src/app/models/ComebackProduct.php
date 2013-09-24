<?php

class ComebackProductModel extends RedBean_SimpleModel {

	const STATE_NEW = 1;
	const STATE_DONE = 2;
	const STATE_DELETE = 3;

	static function getStateName( $state ){
		switch( $state ){
			case self::STATE_NEW:
				return '新建';
				break;
			case self::STATE_DONE:
				return '已返厂';
				break;
			case self::STATE_DELETE:
				return '删除';
				break;
		}
		return '异常';
	}
}

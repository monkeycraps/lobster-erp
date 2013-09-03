<?php 
namespace rbac;

class Role{

	static function getRoleName( $role_id ){
		return '客服';
	}

	static function getRoleActions(){

		$list = \R::findAll( 'mission_type', 'order by pid asc' );
		$mission_type = array();
		foreach( $list as $one ){
			$one = $one->getIterator()->getArrayCopy();
			if( $one['pid'] == 0 ){
				!isset( $mission_type[$one['id']] ) && $mission_type[$one['id']] = array();
				$mission_type[$one['id']]['data'] = $one;
				$mission_type[$one['id']]['children'] = array();
			}else{
				if( !isset( $mission_type[$one['pid']] ) ){
					throw new \Exception( 'no pid for: '. print_r( $one, 1 ) );
				}
				$mission_type[$one['pid']]['children'][$one['id']] = $one;
			}
		}

		// \eYaf\Logger::getLogger()->log( $list );
		// \eYaf\Logger::getLogger()->log( $mission_type );
		return $mission_type;
	}
}
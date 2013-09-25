<?php
class AnalyseController extends AdminBaseController {
	protected $layout = 'admin';
	
	function init(){
		parent::init();
	}

	function searchAction(){

		list( $condition, $params ) = $this->getFilter();

		$list = R::getAll( '
			select count(1) cnt, \'new\' type from ( select m.id from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null where closed is null '. $condition .' group by m.id ) t union 
			select count(1) cnt, \'second\' type from ( select m.id from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null where is_second = 1 '. $condition .' group by m.id ) t1 union 
			select count(1) cnt, \'has_pid\' type from ( select m.id from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null where pid > 0 '. $condition .' group by m.id ) t2 union 
			select count(1) cnt, \'closed\' type from ( select m.id from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null where closed is not null '. $condition .' group by m.id ) t3
		', $params );

		foreach( $list as $one ){
			$key = 'cnt_'. $one['type'];
			$this->$key = $one['cnt'];
		}

		echo $this->renderPartial( 'mission/_analyse_all' );
	}

	function searchPersonAction(){

		list( $condition, $params ) = $this->getFilter();

		$list_tmp = R::getAll( '
			select count(1) cnt, \'new\' type, uid from ( 
				select m.id, mu.uid from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null
					inner join mission_user mu on mu.mission_id = m.id
					inner join user u on mu.uid = u.id and u.role_id = '. UserModel::ROLE_KF .'
					where m.closed is null '. $condition .' group by m.id, mu.uid ) t group by uid union 
			select count(1) cnt, \'second\' type, uid from ( 
				select m.id, mu.uid from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null
					inner join mission_user mu on mu.mission_id = m.id
					inner join user u on mu.uid = u.id and u.role_id = '. UserModel::ROLE_KF .'
					where m.is_second = 1 '. $condition .' group by m.id, mu.uid ) t1 group by uid union 
			select count(1) cnt, \'has_pid\' type, uid from ( 
				select m.id, mu.uid from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null
					inner join mission_user mu on mu.mission_id = m.id
					inner join user u on mu.uid = u.id and u.role_id = '. UserModel::ROLE_KF .'
					where m.pid > 0 '. $condition .' group by m.id, mu.uid ) t2 group by uid union 
			select count(1) cnt, \'closed\' type, uid from ( 
				select m.id, mu.uid from mission m left join mission_product mt on mt.mission_id = m.id and mt.deleted is null
					inner join mission_user mu on mu.mission_id = m.id
					inner join user u on mu.uid = u.id and u.role_id = '. UserModel::ROLE_KF .'
					where m.closed is not null '. $condition .' group by m.id, mu.uid ) t3 group by uid
		', $params );

		$list = array();
		foreach( $list_tmp as $one ){
			$uid = $one['uid'];
			!isset( $list[$uid] ) &&  $list[$uid] = array();
			$list[$uid][$one['type']] = $one['cnt'];
		}

		$user_list = R::getAll( 'select id, name, role_id from user where state = 0 and role_id = '. UserModel::ROLE_KF );
		$default = array(
			'new'=>0, 
			'second'=>0, 
			'has_pid'=>0, 
			'closed'=>0, 
		);

		foreach( $user_list as $key=>$one ){
			if( isset( $list[$one['id']] ) ){
				$one = array_merge( $one, $default, $list[$one['id']] );
			}else{
				$one = array_merge( $one, $default );
			}
			$user_list[$key] = $one;
		}
		$this->user_list = $user_list;

		echo $this->renderPartial( 'mission/_analyse_person' );
	}

	function getFilter(){

		$filter = $this->getRequest()->getQuery();

		$params = array();
		$condition = '';

		if( ($category = $this->get( 'category' )) && !( $this->get( 'product'  ) ) ){

			if( $product_list_tmp = CategoryModel::getProductList( $category ) ){
				$product_list = array();
				foreach( $product_list_tmp as $one ){
					$product_list[] = $one['id'];
				}
				$condition .= ' and mt.product_id in ( '. implode( ', ', $product_list ). ' ) ';
			}else{
				$condition .= ' and mt.product_id = -1 ';
			}
		}

		if( $product = $this->get( 'product' ) ){

			$condition .= ' and mt.product_id = '. $product;
		}

		if( ($mission_category = $this->get( 'mission_category' )) && !(  $mission_sub_category = $this->get( 'mission_sub_category' )  ) ){

			$mission_type_list = rbac\MissionType::getMissionTypeList();
			foreach( $mission_type_list as $one ){
				if( $one['data']['id'] != $mission_category )continue;

				$list = array_keys( $one['children'] );
				$condition .= ' and mission_type_id in ( '. implode( ', ', $list ) .' ) ';
			}
		}

		if( $mission_sub_category = $this->get( 'mission_sub_category' ) ){

			$condition .= ' and mission_type_id = '. $mission_sub_category;
		}

		$from = $this->get( 'from' );
		$to = $this->get( 'to' );
		if( $from or $to ){

			// 有日期，time 为空
			unset( $filter['time'] );

			if( $from ){
				$from = date( 'Y-m-d 00:00:00', strtotime( $from ) );
				$condition .= ' and m.created >= \''. $from. '\' ';
			}

			if( $to ){
				$to = date( 'Y-m-d 00:00:00', strtotime( $to ) );
				$condition .= ' and m.created <= \''. $to. '\' ';
			}

		}else{

			$filter['time'] = isset( $filter['time'] ) ? $filter['time'] : 'today';

			$from = null;
			switch( $filter['time'] ){
				case 'today': 
					$from = date( 'Y-m-d 00:00:00' );
					$to = date( 'Y-m-d 23:59:59' );
					break;
				case 'yestorday': 
					$from = date( 'Y-m-d 00:00:00', strtotime( '-1 day' ) );
					$to = date( 'Y-m-d 23:59:59', strtotime( '-1 day' )  );
					break;
				case 'last7': 
					$from = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
					$to = date( 'Y-m-d 23:59:59', strtotime( '-1 day' )  );
					break;
				case 'last30': 
					$from = date( 'Y-m-d 00:00:00', strtotime( '-30 day' ) );
					$to = date( 'Y-m-d 23:59:59', strtotime( '-1 day' )  );
					break;
			}
			if( $from ){
				$condition .= ' and m.created >= \''. $from. '\' and m.created <= \''. $to. '\' ';
			}
		}

		return array( $condition, $params );
	}
}

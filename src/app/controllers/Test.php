<?php
class TestController extends ApplicationController {
	protected $layout = null;

	public function indexAction() {


		echo Helper\Html::dateOfWeek( '2013-09-23' );
		
		echo $this->getRequest()->getHostName();die;

		// $ext = R::dispense( 'mission_ext' );
		// $ext->mission_id  = 4;
		// R::store($ext);

		// $this->show( 'index' );

		// MissionExtModel::setExt( 12, array( 'id'=>1 ) );

		// R::begin();
		// $mission_user = R::dispense( 'mission_user' );
		// $mission_user->mission_id = 1;
		// $mission_user->uid = 1;
		// $mission_user->state = MissionModel::STATE_DRAFT;
		// $mission_user->created = Helper\Html::now();
		// $mission_user->updated = Helper\Html::now();
		// R::store( $mission_user );
		// R::commit();

		// i18n\Trans::getCategory( 'form' );

		// $ext = current(R::find( 'mission_ext', 'mission_id = ?', array( 1700 ) ));
		// dump( $ext );die;

		// $arr = R::getRow( 'select m.id, sub.id as sub_id, m.name, sub.name as sub_name from mission_type m inner join mission_type sub on m.id = sub.pid where sub.id = ? ', array( 12 ) );
		// var_export( $arr );
		// var_export( array_values($arr) );
		// list( $category_id, $sub_category_id, $category, $sub_category ) = array_values($arr);
		// echo $sub_category;

		// $arr = R::getAssoc( 'select m.id, sub.id as sub_id, m.name, sub.name as sub_name from mission_type m inner join mission_type sub on m.id = sub.pid where sub.id = ? ', array( 12 ) );
		// var_export( $arr );

		// $arr = R::getCell( 'select sub.name as sub_name from mission_type m inner join mission_type sub on m.id = sub.pid where sub.id = ? ', array( 12 ) );
		// var_export( $arr );

		// $sql = 'update mission_drawback set state = '. MissionDrawbackModel::STATE_DONE . ' where mission_id = ? and state = '. MissionDrawbackModel::STATE_APPLY;
		// dump( R::exec( $sql, array( 189 ) ) );
		
		// $mission = R::load( 'mission', 188 );
		
		// $mission_new = $mission->copyMission( 19 );
		// echo $mission_new->id;

	}
}
<?php
class TestController extends ApplicationController {
	protected $layout = null;

	public function indexAction() {
		
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

		$model = R::findOne( 'mission', 'id=85' );
		var_dump( $model->ownMissionExt );die;

	}
}
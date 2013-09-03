<?php
class MissionController extends ApplicationController {
	protected $layout = 'frontend';

	function init(){

		parent::init();
		$this->user->checkPermission( 'mission' );

	}

	public function indexAction() {

		$this->waiting_list = MissionModel::getWaitingList( $this->user->id );
		$this->done_list = array();
		$this->show( 'index' );
	}

	public function addAction() {
		
		$cate_id = $this->get( 'cate' );
		$sub_cate_id = $this->get( 'subcate' );

		$this->renderPartial( 'form-'. $cate_id. '-'. $sub_cate_id );
	}
	
	function missionAction(){
		$request = $this->getRequest ();
		
		if( $request->isPost() && ($action = $this->post( 'action' ) ) ){
			
			$id = $this->post ( 'id' );
			if (! $model = R::findOne ( 'user', 'id = ?', array (
				$id 
			) )) {
				throw new Exception ( 'model not found' );
			}
			switch( $action ){
				case 'ban':
					$model->status = 101;
					R::store( $model );
					break;
				case 'restore':
					$model->status = 0;
					// R::store( $model );
					break;
				default: 
					throw new Exception( 'no action' );
			}
		}else{
			
			if ($request->isPut ()) {
				$id = $this->put ( 'id' );
				if (! $model = R::findOne ( 'mission', 'id = ?', array (
					$id 
				) )) {
					throw new Exception ( 'model not found' );
				}
				$model->name = $this->put ( 'name' );
				$model->username = $this->put ( 'username' );
				if( $this->put( 'pwd' ) ){
					$model->pwd = user\Auth::encrypt( $this->put( 'pwd' ) );
				} 
				$model->role_id = $this->put ( 'role_id' );
				$model->updated = date ( 'Y-m-d' );
				// $id = R::store ( $model );
			} elseif ($request->isPost ()) {

				debug( $request->getPost() );
				$model = MissionModel::createMission( $request->getPost() );
				die;

			} elseif ($request->isDelete ()) {
				$id = $this->get ( 'id' );
				$model = R::load ( 'mission', $id );
				$model->status = 102;
				// R::store( $model );
				return $this->renderJson ( array (
					'error' => 0 
				) );
			} else {
				$id = $this->get ( 'id' );
				if( (!$id = $this->get ( 'id' )) or (!$model = R::findOne ( 'mission', 'id = ?', array( $id ) )) ){
					throw new Exception( 'no mission id', 404 );
				}
			}
		}
		
		$model->created = Helper\Html::date( $model->created );
		$model->logined = Helper\Html::date( $model->logined );
		$role_name = RoleModel::getRoleName( $model->role_id );
		$status_name = UserModel::getStatusName( $model->status );
		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'role_name' => $role_name, 
			'status_name' => $status_name
		) ) );
	}

	function historyAction(){
		$id = $this->get( 'id' );
		$this->history = R::getAll( 'select uid, changed, created from mission_change_log where mission_id = ? order by updated desc ', array( $id ) );
		$this->show( 'history' );
	}
}







<?php
class UserController extends AdminBaseController {
	protected $layout = 'admin';
	public $nav = 'user';

	public function indexAction() {

		list ( $users ) = UserModel::getList ();
		list ( $role_list ) = RoleModel::getList ();
		$this->show ( 'index', array (
			'users' => $users,
			'role_list' => $role_list 
		) );
	}

	function userAction() {

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
					$model->state = 101;
					R::store( $model );
					break;
				case 'restore':
					$model->state = 0;
					R::store( $model );
					break;
				default: 
					throw new Exception( 'no action' );
			}
		}else{
			
			if ($request->isPut ()) {
				$id = $this->put ( 'id' );
				if (! $model = R::findOne ( 'user', 'id = ?', array (
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
				$id = R::store ( $model );
			} elseif ($request->isPost ()) {
				$model = R::dispense ( 'user' );
				$model->name = $this->post ( 'name' );
				$model->username = $this->post ( 'username' );
				if( $this->post( 'pwd' ) ){
					$model->pwd = user\Auth::encrypt( $this->post( 'pwd' ) );
				} 
				$model->role_id = $this->post ( 'role_id' );
				$model->created = date ( 'Y-m-d' );
				$model->updated = date ( 'Y-m-d' );
				$model->state = 0;
				$id = R::store ( $model );
			} elseif ($request->isDelete ()) {
				$id = $this->get ( 'id' );
				$model = R::load ( 'user', $id );
				$model->state = 102;
				R::store( $model );
				return $this->renderJson ( array (
					'error' => 0 
				) );
			} else {
				$id = $this->get ( 'id' );
				$model = R::load ( 'user', $id );
			}
		}
		
		
		$model->created = Helper\Html::date( $model->created );
		$model->logined = Helper\Html::date( $model->logined );
		$role_name = RoleModel::getRoleName( $model->role_id );
		$state_name = UserModel::getStateName( $model->state );
		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'role_name' => $role_name, 
			'state_name' => $state_name
		) ) );
	}
}

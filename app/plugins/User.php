<?php

class UserPlugin extends Yaf\Plugin_Abstract{

	public $id;
	public $adminid;
	public $role_id;
	public $name; 
	public $username; 
	public $message;

	/**
	 * @override
	 */
	public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		$this->update();
	}

	function update(){
		ini_set( 'session.gc_maxlifetime', 14400 );
		$session = Yaf\Session::getInstance();

		$this->id = $session->get( 'id' );
		$this->name = $session->get( 'name' );
		$this->username = $session->get( 'username' );
		$this->role_id = $session->get( 'role_id' );
		$this->message = $session->get( 'message' );
		$this->adminid = $session->get( 'adminid' );
	}

	function login( $user ){

		$session = Yaf\Session::getInstance();

		$session->set( 'id', $user->id );
		$session->set( 'name', $user->name );
		$session->set( 'username', $user->username );
		$session->set( 'role_id', $user->role_id );
		$session->set( 'message', $user->ownMessage );

		$this->update();
	}

	function logout(){
		$session = Yaf\Session::getInstance();

		session_destroy();
		$this->update();
	}

	function loginAdmin(){

		$session = Yaf\Session::getInstance();

		$session->set( 'adminid', 1 );
		$this->update();
	}

	function logoutAdmin(){

		$session = Yaf\Session::getInstance();

		$session->set( 'adminid', null );
		$this->update();
	}

	function checkPermission( $action ){
		$pass = true;
		switch( $action ){
			case 'mission':
				if( !$this->id ){
					$pass = false;
				}
				break;
			default: 
				break;
		}

		if( !$pass ){
			if( yaf\Application::app()->controller->isAjax() ){
				throw new Exception( '登录时间超时，请重新登录！' );
			}
			Yaf\Application::app ()->controller->redirect( '/login/index' );
			exit();
		}
	}
}
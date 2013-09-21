<?php

class UserPlugin extends Yaf\Plugin_Abstract{

	public $id;
	public $adminid;
	public $adminname;
	public $role_id;
	public $name; 
	public $username; 

	private $v = array();

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
		$this->adminid = $session->get( 'adminid' );
		$this->adminname = $session->get( 'adminname' );
	}

	function login( $user ){

		$session = Yaf\Session::getInstance();

		$session->set( 'id', $user->id );
		$session->set( 'name', $user->name );
		$session->set( 'username', $user->username );
		$session->set( 'role_id', $user->role_id );

		$this->update();
	}

	function logout(){
		$session = Yaf\Session::getInstance();

		session_destroy();
		$this->update();
	}

	function loginAdmin( $user = null ){

		$session = Yaf\Session::getInstance();

		$uid = $user ? $user->id : 999999;
		$name = $user ? $user->name : 'admin';
		$session->set( 'adminid', $uid );
		$session->set( 'adminname', $name );
		$this->update();
	}

	function logoutAdmin(){

		$session = Yaf\Session::getInstance();

		$session->set( 'adminid', null );
		$session->set( 'adminname', null );
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
			case 'comeback':

				if( !in_array( $this->role_id, array(
						UserModel::ROLE_DZ, 
						UserModel::ROLE_CG, 
						UserModel::ROLE_FCG, 
					) ) ){
					$pass = false;
				}

				break;
			case 'announce':

				if( !in_array( $this->role_id, array(
						UserModel::ROLE_DZ, 
						UserModel::ROLE_CG, 
						UserModel::ROLE_FCG, 
					) ) ){
					$pass = false;
				}

				break;
			default: 
				$pass = fasle;
				break;
		}
		return $pass;
	}

	function __get( $key ){
		if( !isset( $this->v[$key] ) ){
			throw new Exception( 'not defined key: '. $key );
		}
		return $this->v[$key];
	}

	function __set( $key, $value ){
		$this->v[$key] = $value;
	}

}
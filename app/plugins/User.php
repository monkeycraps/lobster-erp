<?php

class UserPlugin extends Yaf\Plugin_Abstract{

	public $id;
	public $role_id;
	public $username; 
	public $message;

	public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		
		$session = Yaf\Session::getInstance();

		$this->id = $session->get( 'id' );
		$this->username = $session->get( 'username' );
		$this->role_id = $session->get( 'role_id' );
		$this->message = $session->get( 'message' );
	}

	public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function preResponse(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}
}
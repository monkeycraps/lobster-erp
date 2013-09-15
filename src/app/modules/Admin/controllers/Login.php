<?php

class LoginController extends AdminBaseController {
	protected $layout = 'login';

	public function indexAction() {

		if( $this->get('username') ){
			$name = $this->get('username');
			$pwd = $this->get('password');
			if( $name == 'admin' && $pwd == 'admin' ){
				$this->user->loginAdmin();
				$this->redirect( '/admin' );
				$this->getView ()->setScriptPath ( $this->getConfig ()->application->directory . "/views" );
				return false;
			}else{
				$this->addError( '密码错误' );
			}
		}

		$this->show( 'index' );
	}

	function logoutAction(){

		$this->user->logoutAdmin();
		$this->redirect( '/' );
		$this->getView ()->setScriptPath ( $this->getConfig ()->application->directory . "/views" );
		return false;
	}
}

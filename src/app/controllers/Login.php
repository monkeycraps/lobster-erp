<?php
use eYaf\Logger;
class LoginController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {

		if( $this->post('username') ){
			$name = $this->post('username');
			$pwd = $this->post('password');
			if( !$user = R::findOne('user', 'username = ?', array( $name ) ) ){
				$this->addError( '找不到用户' );
			}

			if( $user && !user\Auth::check( $pwd, $user->pwd )){
				$this->addError( '密码错误' );
			}

			if( $user ){
				$user->logined = Helper\Html::now();
				$user->ip = $this->getRequest()->getIp();
				R::store( $user );
			}

			if( !$this->getErrors() ){
				$this->user->login( $user );
				$this->redirect( '/mission' );
				return false;
			}
		}

		$this->show( 'index' );
	}

	public function loginAction() {

		$pwd = $this->get( 'pwd', '' );
		$name = $this->get( 'name', '' );
		if( !$user = R::findOne('user', 'username = ?', array( $name ) ) ){
			throw new ErrorException( 'no user for: '. $name , 401 );
		}

		if( !user\Auth::check( $pwd, $user->pwd )){
			throw new ErrorException( 'pwd not match', 401 );
		}

		$this->user->login( $user );

		$this->redirect( history\History::prev() );
		$this->getView ()->setScriptPath ( $this->getConfig ()->application->directory . "/views" );
		return false;
	}

	function logoutAction(){
		$this->user->logout();
		$this->redirect( history\History::prev() );
		$this->getView ()->setScriptPath ( $this->getConfig ()->application->directory . "/views" );
		return false;
	}

}
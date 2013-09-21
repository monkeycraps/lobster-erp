<?php
class AdminBaseController extends ApplicationController {

	protected $layout = 'admin';

	public function init() {

		parent::init ();
		
		$this->getView ()->setLayoutPath ( $this->getConfig ()->application->directory . "/modules" . "/Admin" . "/views" . "/layouts" );

		if( !$this->user->adminid ){

			if( yaf\Application::app()->controller->isAjax() ){
				throw new Exception( '登录时间超时，请重新登录！' );
			}

			if( '/admin/login/index' != $this->getRequest()->getRequestUri()){
				$this->redirect( '/admin/login/index' );
				return false;
			}
		}
	}
}

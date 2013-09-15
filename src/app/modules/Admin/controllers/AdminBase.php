<?php
class AdminBaseController extends ApplicationController {

	protected $layout = 'admin';

	public function init() {

		parent::init ();
		
		$this->getView ()->setLayoutPath ( $this->getConfig ()->application->directory . "/modules" . "/Admin" . "/views" . "/layouts" );

		if( !$this->user->adminid ){
			if( '/admin/login/index' != $this->getRequest()->getRequestUri()){
				$this->redirect( '/admin/login/index' );
				return false;
			}
		}
	}
}

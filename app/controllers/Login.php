<?php
use eYaf\Logger;
class LoginController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {
		$this->title = '登录';
		$this->display( 'index' );
	}
	
}
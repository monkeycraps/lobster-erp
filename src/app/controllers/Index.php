<?php
class IndexController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {
		
		$this->ishome = 1;
		$this->show( 'index' );
	}
}
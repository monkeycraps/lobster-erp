<?php
class IndexController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {
		
		$this->show( 'index' );
	}
}
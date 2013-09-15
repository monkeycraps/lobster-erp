<?php

class IndexController extends AdminBaseController {
	protected $layout = 'admin';

	public function indexAction() {

		$this->show( 'index' );
	}
}

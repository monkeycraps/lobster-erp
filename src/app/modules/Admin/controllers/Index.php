<?php

class IndexController extends AdminBaseController {
	protected $layout = 'admin';

	public function indexAction() {

		$this->redirect( '/admin/mission/analyse' );
	}
}

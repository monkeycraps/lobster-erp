<?php
class AnnounceController extends AdminBaseController {
	protected $layout = 'admin';

	public function indexAction() {

		$this->show( 'index' );
	}
}

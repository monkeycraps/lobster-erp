<?php
class MissionController extends AdminBaseController {
	protected $layout = 'admin';
	
	function init(){
		$this->nav = 'mission';
		parent::init();
	}

	public function indexAction() {
		$this->redirect( 'list/id/1' );
	}
	
	public function listAction() {
		$this->subnav = $this->get('id', 1);
		$this->show( 'list' );
	}
	
	public function analyseAction() {
		$this->nav = 'analyse';
		$this->show( 'analyse' );
	}
	
}

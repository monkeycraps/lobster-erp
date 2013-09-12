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

		$params = array();
		$condition = '';

		$list = R::getAll( '
			select count(1) cnt, \'new\' type  from mission where closed is null '. $condition .' union 
			select count(1) cnt, \'second\' type from mission where is_second = 1 '. $condition .' union 
			select count(1) cnt, \'has_pid\' type from mission where pid > 0 '. $condition .' union 
			select count(1) cnt, \'closed\' type from mission where closed is not null '. $condition .'
		', $params );

		foreach( $list as $one ){
			$key = 'cnt_'. $one['type'];
			$this->$key = $one['cnt'];
		}

		$this->show( 'analyse' );
	}
	
}

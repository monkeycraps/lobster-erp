<?php
class IndexController extends ApplicationController {
	protected $layout = 'frontend';

	function init(){

		parent::init();

		if( !$this->user->id ) {
			Yaf\Application::app ()->controller->redirect( '/login/index' );
			exit();
		}
	}

	public function indexAction() {
		
		$this->ishome = 1;
		$this->show( 'index' );
	}

	public function pageAction() {
			
		echo $this->renderPartial( 'index/_announce_page' );
	}

	function showAction(){
		if( !( $type = $this->get( 'type' ) ) || !( $id = $this->get( 'id' ) ) ){
			throw new Exception( 'nothing found', 404 );
		}

		switch( $type ){
			case 'announce': 

				if( !$model = R::findOne( 'announce', 'id = ? and deleted is null', array($id) ) ){
					throw new Exception( 'nothing found', 404 );	
				}

				$this->model = $model;

				echo $this->renderPartial( 'index/announce' );
				break;
			default: 
				throw new Exception( 'nothing found', 404 );
		}


	}
}
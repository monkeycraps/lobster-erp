<?php
class IndexController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {
		
		$this->ishome = 1;
		$this->show( 'index' );
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
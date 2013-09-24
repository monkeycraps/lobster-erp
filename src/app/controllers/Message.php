<?php
class MessageController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {
		
		echo $this->renderPartial( 'message/index' );
	}

	function pageAction(){

		echo $this->renderPartial( 'message/_page' );
	}

	function deleteAction(){

		if( !($id = $this->post( 'id' )) or !( $model = R::findOne( 'message', 'id=?', array( $id ) ) ) ){
			throw new Exception( 'no message', '412' );
		}

		$model->deleted = Helper\Html::now();
		R::store( $model );

		$this->renderJson( array(
			'new_message'=> $this->user->message->updateNew( $this->user->id )
		) );
	}

	function readedAction(){

		if( !($id = $this->post( 'id' )) or !( $model = R::findOne( 'message', 'id=?', array( $id ) ) ) ){
			throw new Exception( 'no message', '412' );
		}

		$model->readed = Helper\Html::now();
		R::store( $model );

		$this->renderJson( array(
			'new_message'=> $this->user->message->updateNew( $this->user->id )
		) );
	}

	function checkAction(){

		$message = $this->user->message;
		$this->renderJson( array(
			'cnt' => $message->new_count,
			'list' => $message->new_list, 
		) );
	}
}
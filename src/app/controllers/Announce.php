<?php
class AnnounceController extends ApplicationController {
	protected $layout = 'frontend';

	function init(){

		throw new Exception( '没有权限', 403 );

		$this->nav= 'announce';

		parent::init();
		$pass = $this->user->checkPermission( 'announce' );

		if( !$pass ){
			if( yaf\Application::app()->controller->isAjax() ){
				throw new Exception( '登录时间超时，请重新登录！' );
			}
			throw new Exception( '没有权限', 403 );
			exit();
		}

	}

	function indexAction(){

		list ( $announce_list, $pager_product ) = AnnounceModel::getList ( $this->get ( 'pp' ) );
		$this->announce_list = $announce_list;


		$this->show( 'index' );
	}
	
	function announceAction() {

		$request = $this->getRequest ();
		
		if( $request->isPost() && ($action = $this->post( 'action' ) ) ){
			
			$id = $this->post ( 'id' );
			if (! $model = R::findOne ( 'announce', 'id = ? and deleted is null', array (
				$id 
			) )) {
				throw new Exception ( 'model not found' );
			}
			switch( $action ){
				case 'publish':
					$model->state = AnnounceModel::STATE_PUBLISHED;
					R::store( $model );
					break;
				default: 
					throw new Exception( 'no action' );
			}
		}else{
			
			if ($request->isPut ()) {
				$id = $this->put ( 'id' );
				if (! $model = R::findOne ( 'announce', 'id = ? and deleted is null', array (
					$id 
				) )) {
					throw new Exception ( 'model not found' );
				}
				$model->subject = $this->put ( 'subject' );
				$model->content = $this->put ( 'content' );
				$model->create_uid = $this->user->id;
				$model->updated = Helper\Html::now();

				$id = R::store ( $model );
			} elseif ($request->isPost ()) {
				$model = R::dispense ( 'announce' );
				$model->subject = $this->post ( 'subject' );
				$model->content = $this->post ( 'content' );
				$model->created = Helper\Html::now();
				$model->updated = Helper\Html::now();
				$model->create_uid = $this->user->id;
				$model->state = AnnounceModel::STATE_DRAFT;

				$id = R::store ( $model );
			} elseif ($request->isDelete ()) {
				$id = $this->get ( 'id' );
				$model = R::load ( 'announce', $id );
				$model->deleted = Helper\Html::now();
				R::store( $model );
				return $this->renderJson ( array (
					'error' => 0 
				) );
			} else {
				$id = $this->get ( 'id' );
				$model = R::load ( 'announce', $id );
			}
		}
		
		$model->created = Helper\Html::date( $model->created );

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'create_uname' => $model->create_uid ? UserModel::getName( $model->create_uid ) : 'admin', 
			'content' => $this->parse( $model->content ), 
		) ) );
	}

	private function parse( $content ){
		return html_entity_decode( $content );
	}

}

<?php
class ToolsController extends ApplicationController {
	protected $layout = 'frontend';

	public function indexAction() {
		
		$this->show( 'index' );
	}

	public function addAction() {
		
		$cate_id = $this->get( 'cate' );
		$sub_cate_id = $this->get( 'subcate' );

		$this->renderPartial( 'form-'. $cate_id. '-'. $sub_cate_id );
	}
}
<?php

class MessagePlugin extends Yaf\Plugin_Abstract {

	public $new_count = 0;

	public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		// 获取当前最新的消息状态

		$uid = Yaf\Application::app()->user->id;
		if( !$uid )return;

		$sql = 'select * from message where uid = ? and readed is null and deleted is null ';

		$this->new_count = $this->updateNew( $uid );
		$this->new_list = R::getAll( $sql, array( $uid ) );
	}

	public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {

	}

	public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		// 如果需要发送邮件则发送
	}

	public function preResponse(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
	}

	function getList( $page = 1, $with_deleted = false ){

		$uid = Yaf\Application::app()->user->id;
		if( !$uid )return array( null, null );

		$limit = 12;
		$offset = ($page-1) * $limit;
		$sql_deleted = $with_deleted ? '' : ' and deleted is null ';
		$sql = 'select * from message where uid = ? '. $sql_deleted. ' limit '. $offset. ', '. ($limit + 1);
		$pager = new pager\Pager();
		if( $list = R::getAll( $sql, array( $uid ) ) ){
			if( count( $list ) > $limit ){
				$pages->has_next = true;
			}
			if( $page > 1 ){
				$pages->has_prev = true;
			}
			unset( $list[count( $list )] );
		}

		return array( $list, $pager );
	}

	function updateNew( $uid ){
		$sql = 'select count(1) from message where uid = ? and readed is null and deleted is null ';
		return R::getCell( $sql, array( $uid ) );
	}
}

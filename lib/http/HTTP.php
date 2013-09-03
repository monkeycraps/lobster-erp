<?php

namespace http;
class HTTP{
	static function isAjax(){
		return (isset($_SERVER['HTTP_X_REQUESTED']) && $_SERVER['HTTP_X_REQUESTED']==='JSON')
			|| (isset($_SERVER['HTTP_X_REQUESTED']) && $_SERVER['HTTP_X_REQUESTED'] == 'HTML')
			|| (isset($_GET['_X_REQUESTED_']) && $_GET['_X_REQUESTED_'] == 'HTML')
			|| (isset($_GET['_X_REQUESTED_']) && $_GET['_X_REQUESTED_'] == 'JSON');
	}
}
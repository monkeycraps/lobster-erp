<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
namespace eYaf;

/**
 * Filter user input data.
 * 
 * Filter quotes and double quotes from  user input data by applying 
 * htmlspecioalchars function with parametes ENT_QUOTES and UTF-8.
 * 
 * @return array the user data filtered,
 */
class Request extends \Yaf\Request\Http {
	private $_posts;
	private $_params;
	private $_query;
	private $_puts;
	
	public function isDelete(){
		if( !isset($_SERVER['REQUEST_METHOD']) ){
			return false;
		}else if( strtolower( $_SERVER['REQUEST_METHOD']) == 'delete' ){
			return true;
		}
		return false;
	}

	public function getPost() {
		global $HTTP_RAW_POST_DATA;
		
		if( !$this->isPost() ){
			return array();
		}

		if ($this->_posts) {
			return $this->_posts;
		}
		
		if( isset( $_SERVER['HTTP_CONTENT_TYPE'] ) && $_SERVER['HTTP_CONTENT_TYPE'] == 'application/json' ){
			$_POST = $this->parseParams($HTTP_RAW_POST_DATA);
		}
		
		$this->_posts = $this->filter_params ( $_POST );
		return $this->_posts;
	}

	public function parseParams( $data ){
		$data = json_decode( $data, true );
		if( !$data ){
			return;
		}
		$func = function( &$val, $key, &$arr ){
			if( false !== strpos( $key, '[' ) ){
				preg_match( '/([^\[]*)(\[[^\]]+\])?(\[[^\]]+\])?(\[[^\]]+\])?/', $key, $matches );
				$len = count( $matches ) - 2;
				$brr = $val;
				while( $len > 0 ){
					$brr = array( $matches[$len+1]=>$brr );
					$len--;
				}
				if( isset( $arr[$matches[1]] ) ){
					$arr[$matches[1]] = array_merge_recursive( $arr[$matches[1]], $brr );
				}else{
					$arr[$matches[1]] = $brr;
				}
				unset( $arr[$key] );
			}
		};
		array_walk($data, $func, $data);

		return $this->resetKey($data);
	}


	function resetKey( $arr ){
		foreach( $arr as $key=>$one ){
			if( false !== strpos( $key, '[' ) ){
				unset( $arr[$key] );
				$key = substr($key, 1, -1);
			}
			if( is_array( $one ) ){
				$arr[$key] = $this->resetKey( $one );
			}else{
				$arr[$key] = $one;
			}
		}
		return $arr;
	}

	public function getParams() {

		if ($this->_params) {
			return $this->_params;
		}
		
		$this->_params = $this->filter_params ( parent::getParams () );
		return $this->_params;
	}

	public function getQuery() {

		if ($this->_query) {
			return $this->_query;
		}
		
		$this->_query = $this->filter_params ( parent::getQuery () );
		return $this->_query;
	}
	
	public function getPut(){
		
		if( !$this->isPut() ){
			return array();
		}
		
		if ($this->_puts) {
			return $this->_puts;
		}
		
		$_PUT = file_get_contents( 'php://input' );
		$_PUT = $this->parseParams( $_PUT );

		$this->_puts = $this->filter_params ( $_PUT );
		return $this->_puts;
	}
	
	public function post( $key, $default_value ){
		$post = $this->getPost();
		if( isset( $post[$key] ) ){
			return $post[$key];
		}else{
			return $default_value ? $default_value: null;
		}
	}
	
	public function put( $key, $default_value ){
		$put = $this->getPut();
		if( isset( $put[$key] ) ){
			return $put[$key];
		}else{
			return $default_value ? $default_value: null;
		}
	}

	private function filter_params($params) {

		if (! empty ( $params )) {
			array_walk_recursive ( $params, function (&$value, $key) {
				// if( is_object($value) ){
				// }
				$value = htmlspecialchars ( $value, ENT_QUOTES, 'UTF-8' );
			} );
		}
		
		return $params;
	}
}

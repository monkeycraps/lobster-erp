<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The main application controller.
 *
 * All application controllers may inherit from this controller.
 * This controller uses Layout class (@see lib/Layout.php)
 */
class ApplicationController extends Yaf\Controller_Abstract {
	
	public $nav;
	public $subnav;
	
	/**
     * The name of layout file.
     *
     * The name of layout file to be used for this controller ommiting extension.
     * Layout class will use extension from application config ini. 
     *
     * @var string
     */
	protected $layout;
	
	/**
     * The session instance.
     *
     * Yaf\Session instance to be used for this application.
     *
     */
	protected $session;
	
	/**
     * A Yaf\Config\Ini object that contains application configuration data.
     * 
     * @var Yaf\Config\Ini
     */
	private $config;
	
	/**
     * Initialize layout and session.
     *
     * In this method can be initialized anything that could be usefull for 
     * the controller.
     *
     * @return void
     */
	public $user;
	public $m = array ();

	public function init() {
		// Set the layout.
		$this->getView ()->setLayout ( $this->layout );
		
		//Set session.
		$this->session = Yaf\Session::getInstance ();
		
		// Assign session to views too.
		$this->getView ()->session = $this->session;
		
		// Assign application config file to this controller
		$this->config = Yaf\Application::app ()->getConfig ();
		
		// Assign config file to views
		$this->getView ()->config = $this->config;
		
		$this->user = Yaf\Application::app ()->user;
		Yaf\Application::app ()->controller = $this;
		
	}

	/**
     * When assign a public property to controller, this property will be 
     * available to action view template too.
     *
     * @param string $name  the name of the property
     * @param mixed  $value the value of the property
     *
     * @return void 
     */
	public function __set($name, $value) {

		$this->m [$name] = $value;
		$this->getView ()->assignRef ( $name, $value );
	}

	public function __get($name) {

		if (strtolower ( substr ( $name, 0, 3 ) ) == 'yaf')
			return;
		if (! isset ( $this->m [$name] )) {
			throw new Exception ( "{$name} not defined", 500 );
		}
		return $this->m [$name];
	}

	public function getConfig() {

		return $this->config;
	}

	/**
     * Cancel current action proccess and forward to {@link notFound()} method.
     *
     * @return false
     */
	public function forwardTo404() {

		$this->forward ( 'Index', 'application', 'notFound' );
		$this->getView ()->setScriptPath ( $this->getConfig ()->application->directory . "/views" );
		header ( 'HTTP/1.0 404 Not Found' );
		return false;
	}

	/**
     * Renders a 404 Not Found template view
     *
     * @return void
     */
	public function notFoundAction() {

	}

	function show($action_name, $tpl_vars = NULL) {

		! $tpl_vars && $tpl_vars = array ();
		foreach( $tpl_vars as $key=>$val ){
			$this->$key = $val;
		}
		$tpl_vars = array_merge ( $tpl_vars, array (
			'controller' => $this 
		) );
		$this->display ( $action_name, $tpl_vars );
	}

	function renderPartial($path, $tpl_vars = NULL) {

		// $layout_o = $this->getView()->getLayout();
		// $this->getView()->setLayout(null);

		! $tpl_vars && $tpl_vars = array ();

		$tpl_vars = array_merge ( $tpl_vars, array (
			'controller' => $this 
		) );
		// return $this->render( $path, $tpl_vars );
		return $this->getView()->renderPartial( $path, $tpl_vars );
	}

	function renderJson( $array ) {
		
		$json = json_encode( $array ); 
		$this->getView ()->setScriptPath ( $this->getConfig ()->application->directory . "/views" );
		header( 'Content-Type: application/json;' );
		echo $json;
		return false;
	}

	function get($key, $default_val = null) {

		$request = $this->getRequest ();
		return $request->get ( $key, $default_val );
	}
	
	function post($key, $default_val = null) {

		$request = $this->getRequest ();
		return $request->post ( $key, $default_val );
	}
	
	function put($key, $default_val = null) {

		$request = $this->getRequest ();
		return $request->put ( $key, $default_val );
	}

	function log($obj) {

		eYaf\Logger::getLogger ()->log ( $obj );
	}

	function addError($value, $key = null) {

		! isset ( $this->m ['errors']  ) && $this->errors = array ();
		if (! $key) {
			$this->errors = array_merge ( $this->errors, array (
				$value 
			) );
		} else {
			$this->errors = array_merge ( $this->errors, array (
				$key => $value 
			) );
		}
	}

	function getErrors(){
		return isset( $this->m ['errors'] ) ? $this->errors : array();
	}
	
	function isAjax(){
		return (isset ( $_SERVER ['HTTP_X_REQUESTED'] ) && $_SERVER ['HTTP_X_REQUESTED'] === 'JSON') || (isset ( $_GET ['_X_REQUESTED_'] ) && $_GET ['_X_REQUESTED_'] == 'JSON') || strtolower($_SERVER['CONTENT_TYPE']) == 'application/json';
	}
}

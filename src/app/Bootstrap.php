<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
use eYaf\Request;
use eYaf\Layout;
class Bootstrap extends \Yaf\Bootstrap_Abstract {

	public function _initErrorHandler(Yaf\Dispatcher $dispatcher) {

		error_reporting ( E_ALL & ~E_NOTICE & ~E_DEPRECATED );
		ini_set( 'allow_call_time_pass_reference', 1 );

		$dispatcher->setErrorHandler ( array (
			get_class ( $this ),
			'error_handler' 
		) );
	}

	public function _initConfig(Yaf\Dispatcher $dispatcher) {

		$this->config = Yaf\Application::app ()->getConfig ();
	}

	function _initComposer(Yaf\Dispatcher $dispatcher) {

		require( APP_PATH. '/../vendor/autoload.php' );
	}

	public function _initRequest(Yaf\Dispatcher $dispatcher) {

		if( isset( $_POST['session_id'] ) ){
			session_id( $_POST['session_id'] );
		}

		$dispatcher->setRequest ( new Request () );
	}

	public function _initDatabase(Yaf\Dispatcher $dispatcher) {

	}

	public function _initPlugins(Yaf\Dispatcher $dispatcher) {

		$dispatcher->registerPlugin ( new LogPlugin () );
		$user = new UserPlugin ();
		$dispatcher->registerPlugin ( $user );
		Yaf\Application::app()->user = $user;

		$dispatcher->registerPlugin ( new ViewPlugin () );
		$this->config->application->protect_from_csrf && $dispatcher->registerPlugin ( new AuthTokenPlugin () );

		$message = new MessagePlugin ();
		$dispatcher->registerPlugin ( $message );
		$user->message = $message;
	}

	public function _initLoader(Yaf\Dispatcher $dispatcher) {

	}

	public function _initRoute(Yaf\Dispatcher $dispatcher) {

		$config = new Yaf\Config\Ini ( APP_PATH . '/config/routing.ini' );
		$dispatcher->getRouter ()->addConfig ( $config );
	}

	/**
     * Custom init file for modules.
     *
     * Allows to load extra settings per module, like routes etc.
     */
	public function _initModules(Yaf\Dispatcher $dispatcher) {

		$app = $dispatcher->getApplication ();
		
		$modules = $app->getModules ();
		foreach ( $modules as $module ) {
			if ('index' == strtolower ( $module ))
				continue;
			
			require_once $app->getAppDirectory () . "/modules" . "/$module" . "/_init.php";
		}
	}

	public function _initLayout(Yaf\Dispatcher $dispatcher) {

		$layout = new Layout ( $this->config->application->layout->directory );
		$dispatcher->setView ( $layout );
		// $dispatcher->autoRender( true );
		// \Yaf\Dispatcher::getInstance ()->disableView ();
	}

	/**
     * Custom error handler.
     *
     * Catches all errors (not exceptions) and creates an ErrorException.
     * ErrorException then can caught by Yaf\ErrorController.
     *
     * @param integer $errno   the error number.
     * @param string  $errstr  the error message.
     * @param string  $errfile the file where error occured.
     * @param integer $errline the line of the file where error occured.
     *
     * @throws ErrorException
     */
	public static function error_handler($errno, $errstr, $errfile, $errline) {

		// Do not throw exception if error was prepended by @
		//
		// See {@link http://www.php.net/set_error_handler}
		//
		// error_reporting() settings will have no effect and your error handler 
		// will be called regardless - however you are still able to read 
		// the current value of error_reporting and act appropriately. 
		// Of particular note is that this value will be 0 
		// if the statement that caused the error was prepended 
		// by the @ error-control operator.
		//
		if (error_reporting () === 0)
			return;
		
		// \Yaf\Dispatcher::getInstance ()->enableView ();
		throw new \ErrorException ( $errstr, 0, $errno, $errfile, $errline );
	}

	public function _initDefaultDbAdapter() {

		require_once ($this->config->application->library->directory . '/RedBean/rb.php');
		require_once ($this->config->application->library->directory . '/RedBean/MyModelFormatter.php');
		require_once ($this->config->application->library->directory . '/RedBean/RedBean_FileLogger.php');
		
		$params = Yaf\Application::app ()->getConfig ()->database->params->toArray ();
		
		$formatter = new MyModelFormatter ();
		RedBean_ModelHelper::setModelFormatter ( $formatter );
		R::setup ( $params ['link'], $params ['user'], $params ['pwd'] );
		R::setStrictTyping( false );
		R::debug(true, new RedBean_FileLogger() );
		R::freeze(True);
	}

	function _initRbac() {

		$config = new Yaf\Config\Ini ( APP_PATH . '/config/rbac.ini' );
		$logger = eYaf\Logger::getLogger();
		$logger->log( $config->toArray() );
	}

	function _initHistory(){
		history\History::set();
	}
}

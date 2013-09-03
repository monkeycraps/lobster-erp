<?php 

class RedBean_FileLogger implements RedBean_Logger{

	public function log(){
		$logger = eYaf\Logger::getLogger();
		if (func_num_args() > 0) {
	      foreach (func_get_args() as $argument) {
	        if (is_array($argument)){
	        	$logger->log( var_export($argument, 1) ); 
	        }else{
	        	$logger->log( $argument );
	        }
	      }
	    }
	}
}
<?php 
namespace FormCustomer;

class Form{

	private $label;
	private $label_ext;

	function __construct( $type ){

		$yaml = yaml_parse_file( APP_PATH. '/config/form.ini' );

		if( is_array( $yaml['form'. $type] ) ){

			if( !is_array( $yaml['form'. $type]['data'] ) ){
				$yaml['form'. $type]['data'] = array();
			}
			$this->label = array_merge(
				$yaml['form'], 
				$yaml['form'. $type]['data']
			);
		}

		if( is_array( $yaml['form'. $type] ) ){

			if( !is_array( $yaml['form'. $type]['ext'] ) ){
				$yaml['form'. $type]['ext'] = array();
			}
			$this->label_ext = $yaml['form'. $type]['ext'];
		}

	}

	function getLabel( $key ){
		if( isset( $this->label[$key] )){
			return $this->label[$key];
		}
		return false;
	}

	function getLabelExt( $key ){
		if( isset( $this->label_ext[$key] )){
			return $this->label_ext[$key];
		}
		return false;
	}

}
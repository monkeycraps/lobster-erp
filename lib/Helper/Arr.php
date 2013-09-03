<?php 
namespace Helper;
class Arr implements \ArrayAccess {

	public $container;

	function __construct( $container = array() ){
		$this->container = $container;
	}

	function get( $key, $default = null ){
		return isset( $this->container[$key] )? $this->container[$key] : $default;
	}

	function set( $key, $val ){
		$this->container[$key] = $val;
	}

	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    function __toString(){
        return var_export( $this->container, 1 );
    }

}


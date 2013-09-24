<?php

namespace pager;

class Pager {
	
	public $has_prev = false;
	public $has_next = false;

	private $_page;
	private $_size;
	private $_itemCount;

	function setPage( $page ){
		$this->_page = $page;
	}

	function setSize( $size ){
		$this->_size = $size;
	}

	function setItemCount( $itemCount ){
		$this->_itemCount = $itemCount;
	}

	function getPage(){
		return $this->_page;
	}

	function getSize(){
		return $this->_size;
	}

	function getItemCount(){
		return $this->_itemCount;
	}

	function render(){

		if( $this->_itemCount <= $this->_size ){
			return;
		}

		$items = array();

		$current = $this->_page == 1 ? true : false;
		$items[] = "<a class='". ( $current ? 'current' : '' ) ." page-prev' href='#' data-page='". ( ($this->_page-1) > 1 ? ($this->_page-1) : 1 ).  "'><i class='glyphicon glyphicon-chevron-left'></i></a>" ;

		$offNow = ( $this->_page - 1 ) * $this->_size;
		$off = 0;
		$page = 1;
		while( $off < $this->_itemCount ){

			$current = $off == $offNow ? true : false;
			$items[] = "<a class='". ( $current ? 'current' : '' ) ."' href='#' data-page='". $page . "' >". $page ."</a>" ;
			$off += $this->_size;
			$page ++;
		}

		$current = ($this->_page == ceil( $this->_itemCount / $this->_size ) ) ? true : false;
		$items[] = "<a class='". ( $current ? 'current' : '' ) ." page-next' href='#' data-page='". ( ($this->_page+1) < ceil( $this->_itemCount / $this->_size ) ? $this->_page+1 : ceil( $this->_itemCount / $this->_size ) ) . "' ><i class='glyphicon glyphicon-chevron-right'></i></a>" ;

		$rs = '<div class="pager" style="text-align: center; width: 100%; clear: both; ">'. 
			implode( '', $items ). 
			'</div>';
		return $rs;
	}

}

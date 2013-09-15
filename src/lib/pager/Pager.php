<?php

namespace pager;

class Pager {
	
	public $has_prev = false;
	public $has_next = false;

	function render(){
		return '<div class="pager" style="text-align: center; width: 100%; clear: both; ">'. 
			($this->has_prev ? "<a href=''><i class='glyphicon glyphicon-chevron-left'></i></a>&nbsp;&nbsp;" : '' ). 
				($this->has_next ? "<a href=''><i class='glyphicon glyphicon-chevron-right'></i></a>" : '' ). '</div>';
	}
}


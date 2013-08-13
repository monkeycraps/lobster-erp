define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	
	var ModalManager = {
		init: function(){
			
		}, 
		modal: function( id ){
			if( $( '#modal-'+ id ).length < 1 ){
				return
			}
			$( '#modal-'+ id ).modal();
		}
	}

	module.exports = ModalManager

});
define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var ModalLogin = Backbone.View.extend({
		el: $('#modal-login'), 
		events: {
		},
		show_modal: function(){
			this.$el.modal()
		}
	})

	var modal_login = new ModalLogin();
	
	var ModalManager = {
		init: function(){
			
		}, 
		modal: function( id ){
			if( $( '#modal-'+ id ).length < 1 ){
				return
			}
			eval( 'modal_'+ id.replace( new RegExp('-',"gm"), '_' ) + '.show_modal()' )
			return;
		}
	}

	module.exports = ModalManager

});
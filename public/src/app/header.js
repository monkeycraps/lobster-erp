define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )

	var Header = Backbone.View.extend({
		el: $('header'), 
		events: {
			'click #login': 'login', 
			'click .mission-add': 'mission_add'
		},
		initialize: function(){

		}, 
		login: function(){
			ModalManager.modal( 'login' )
		}, 
		mission_add: function(){

		}, 
		showFormSuccess: function( msg ){
			if( msg == undefined || msg == '' ){
				msg = '保存成功'
			}
			this.$( '.form-success' ).html( msg ).show().fadeOut( 10000 );
		}
	});

	var header = new Header();
	module.exports = header
});
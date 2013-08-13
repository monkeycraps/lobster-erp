define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )

	var Header = Backbone.View.extend({
		el: $('header'), 
		events: {
			'click #login': 'login'
		},
		initialize: function(){

		}, 
		login: function(){
			ModalManager.modal( 'login' )
		}
	});

	module.exports = Header
});
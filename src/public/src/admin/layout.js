define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/admin/modal' )
	var Mission = require( '/src/admin/mission' );

	function resize(){
		var wh = $(window).height()
		var board_height = (wh - $("header").height() ) + "px";
		$( '#admin-menu' ).css( 'height', board_height );
		$( '#admin-body' ).css( 'height', board_height );
		$( '#admin-list' ).css( 'height', board_height );
		$( '#admin-form' ).css( 'height', board_height );
	}

	var Layout = Backbone.View.extend({

		el: $( '#layout' ),
		menu: null,  
		board: null,  
		events: {

		}, 
		initialize: function(){
			window.onload = window.onresize = function(){
				resize();
			}
			resize();
		}
	});

	

	var Menu = Backbone.View.extend({
		el: $( '#board-menu' ), 
		events: {
		}, 
		initialize: function(){
		}
	});

	module.exports = {
		Layout: Layout, 
		Menu: Menu
	}
});
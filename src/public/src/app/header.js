define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )

	var Header = Backbone.View.extend({
		el: $('header'), 
		events: {
			'click #login': 'login', 
			'click .mission-add': 'mission_add', 
			'click .check_all': 'messageCheckAll', 
			'click .open_message': 'messageOpen', 
			'click .message-list .message': 'messageOpen', 
			'keydown input[name="search"]': 'check_search'
		},
		initialize: function(){
		}, 
		login: function(){
			ModalManager.modal( 'login' )
		}, 
		mission_add: function(){
		}, 
		messageOpen: function( e ){
			if( app.Message == null )  {
				return;
			}
			var id = $( e.currentTarget ).attr( 'data' )
			var view = this;
			var target = $( e.currentTarget );
			app.Message.view.show( id );
		}, 
		messageCheckAll: function(){
			if( app.Message == null )  {
				return;
			}
			var view = this;
			app.Message.view.checkAll( function(){
				view.$( '.message_cnt' ).text( 0 )
				$( '.message-list', view ).empty();
			} );
		}, 
		updateMessage: function( list, cnt ){
			this.$( '.message_cnt' ).text( '('+ cnt + ')' )
			var view = this
			$( 'ul.message-list li', view ).empty();

			_.each(list, function( val, key ){
				view.$( 'ul.message-list' ).append( '<li><a href="#" class="message" data="'+ val.id +'">'+ val.title.substr( 0, 8 ) +'</a></li>' );
			})
		}, 
		initMessage: function( list, cnt ){

			this.$( '.message_cnt' ).text( '('+ cnt + ')' )
			var view = this
			_.each(list, function( val, key ){
				view.$( 'ul.message-list' ).append( '<li><a href="#" class="message" data="'+ val.id +'">'+ val.title.substr( 0, 8 ) +'</a></li>' );
			})
		}
	});

	var header = new Header();
	module.exports = header
});
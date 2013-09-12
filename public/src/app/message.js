define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )

	var View = Backbone.View.extend({
		el: $('#message-wrapper'), 
		new_message: 0, 
		list: [], 
		events: {
			'click tbody tr': 'itemToggle', 
			'click tbody tr op-remove': 'itemRemove'
		}, 
		initialize: function(){

			this.list = message_init.list
			this.new_message = message_init.cnt

			if( app.header != null ){
				app.header.initMessage( this.list, this.new_message )
			}
		}, 
		show: function( id, callback ){
			this.$('#message-inner').modal();
			if( id ){
				this.go( id, callback )
			}
		}, 
		itemToggle: function( e ){
			this.open( $( e.currentTarget ).attr( 'data-id' ) )
		}, 
		open: function( id, callback ){
			this.$( 'tbody tr' ).removeClass( 'active' )
			this.$( 'tbody tr .content' ).hide();
			this.$( 'tbody tr[data-id="'+ id +'"]' ).addClass( 'active' ).show();
			this.$( 'tbody tr[data-id="'+ id +'"] .content' ).fadeIn();
			var view = this;
			if( !this.$( 'tbody tr[data-id="'+ id +'"]' ).hasClass( 'readed' ) ){
				this.itemReaded( id, function( cnt ){
					if( typeof( callback ) == 'function' ){
						callback( cnt )
					}
				} );
			}else{
				if( typeof( callback ) == 'function' ){
					callback( this.new_message )
				}
			}
		}, 
		go: function( id, callback ){
			got = false
			this.$( 'tbody tr' ).each(function(){
				if( $(this).attr('data-id') ==  id){
					got = true;
				}
			})
			if( !got ){
				var view = this;
				this.$( 'tbody' ).load('/message/page?id='+ id, function(){
					view.open( id, callback );
				})
			}else{
				this.open( id, callback );
			}
		}, 
		itemRemove: function( e ){
			var id = $( e.currentTarget ).attr( 'data-id' )
			var view = this;
			var target = e.currentTarget;
			$.post( '/message/delete', {id: id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误: '+ data )
					return
				}
				view.new_message = data.new_message
				target.remove();

				view.updateHeader();

			} )
		}, 
		itemReaded: function( id, callback ){

			var view = this;
			$.post( '/message/readed', {id: id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误: '+ data )
					return
				}
				view.new_message = data.new_message

				if( typeof( callback ) == 'function' ){
					callback( view.new_message )
				}

				view.$( 'tbody tr[data-id="'+ id +'"]' ).addClass( 'readed' )
				_.each( view.list, function( val, key ){
					if( val.id == id ){
						delete( view.list[key] )
					}
				} )

				view.updateHeader();

			} )
		}, 
		updateHeader: function(){

			if( app.Layout == null ){
				return;
			}

			console.log( this.list )
			app.header.updateMessage( this.list, this.new_message )
		}
	})

	var Message = {
		view: new View
	};

	module.exports = Message
});